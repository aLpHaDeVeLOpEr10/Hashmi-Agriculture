<?php

namespace App\Http\Controllers;

use App\Models\CropSeason;
use App\Models\HarvestRecord;
use App\Models\SilageBatch;
use App\Models\StorageUnit;
use App\Services\ProductionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProductionController extends Controller
{
    public function __construct(private ProductionService $production) {}

    public function index(Request $request): View
    {
        $seasons = CropSeason::query()->with('crop')->orderByDesc('start_date')->get();
        $season = $seasons->firstWhere('code', $request->query('season'))
            ?? $seasons->first(fn ($s) => in_array($s->status, ['active', 'harvested', 'ensiled'], true))
            ?? $seasons->first();
        $season?->load(['summary', 'fields', 'harvests.field', 'batches.storageUnit']);

        $units = StorageUnit::query()->orderBy('name')->get();
        $units->load(['batches' => fn ($q) => $q->whereIn('status', ['filling', 'sealed', 'feeding'])->with('season')]);

        return view('production.index', compact('seasons', 'season', 'units'));
    }

    public function storeHarvest(Request $request, CropSeason $season): RedirectResponse
    {
        $this->authorize('enter-production');
        $this->production->saveHarvest($season, $this->harvestData($request, $season));

        return $this->back($season, __('Harvest recorded.'));
    }

    public function updateHarvest(Request $request, CropSeason $season, HarvestRecord $harvest): RedirectResponse
    {
        $this->authorize('manage-production');
        $this->production->saveHarvest($season, $this->harvestData($request, $season), $harvest);

        return $this->back($season, __('Harvest updated.'));
    }

    public function destroyHarvest(CropSeason $season, HarvestRecord $harvest): RedirectResponse
    {
        $this->authorize('manage-production');
        $this->production->deleteHarvest($harvest);

        return $this->back($season, __('Harvest record deleted.'));
    }

    public function storeBatch(Request $request, CropSeason $season): RedirectResponse
    {
        $this->authorize('enter-production');
        $this->production->saveBatch($season, $this->batchData($request));

        return $this->back($season, __('Silage batch recorded.'));
    }

    public function updateBatch(Request $request, CropSeason $season, SilageBatch $batch): RedirectResponse
    {
        $this->authorize('manage-production');
        $this->production->saveBatch($season, $this->batchData($request), $batch);

        return $this->back($season, __('Silage batch updated.'));
    }

    public function destroyBatch(CropSeason $season, SilageBatch $batch): RedirectResponse
    {
        $this->authorize('manage-production');
        $this->production->deleteBatch($batch);

        return $this->back($season, __('Silage batch deleted.'));
    }

    public function advanceBatch(Request $request, CropSeason $season, SilageBatch $batch): RedirectResponse
    {
        $this->authorize('manage-production');
        $data = $request->validate([
            'status' => ['required', Rule::in(['sealed', 'feeding', 'finished'])],
            'date' => ['required', 'date', 'before_or_equal:today'],
        ]);
        $this->production->advanceBatch($batch, $data['status'], $data['date']);

        return $this->back($season, __('Batch :code is now :status.', ['code' => $batch->code(), 'status' => __(ucfirst($data['status']))]));
    }

    public function storeUnit(Request $request): RedirectResponse
    {
        $this->authorize('manage-production');
        StorageUnit::create($this->unitData($request));

        return back()->with('status', __('Storage unit added.'));
    }

    public function updateUnit(Request $request, StorageUnit $unit): RedirectResponse
    {
        $this->authorize('manage-production');
        $unit->update($this->unitData($request) + ['is_active' => $request->boolean('is_active')]);

        return back()->with('status', __('Storage unit updated.'));
    }

    private function harvestData(Request $request, CropSeason $season): array
    {
        return $request->validate([
            'harvest_date' => ['required', 'date', 'before_or_equal:today'],
            'field_id' => ['nullable', Rule::in($season->fields()->pluck('fields.id'))],
            'method' => ['required', Rule::in(HarvestRecord::METHODS)],
            'quantity_kg' => ['required_unless:method,trolley_count', 'nullable', 'numeric', 'min:1', 'max:999999999'],
            'trolley_count' => ['required_if:method,trolley_count', 'nullable', 'integer', 'min:1', 'max:100000'],
            'avg_trolley_kg' => ['required_if:method,trolley_count', 'nullable', 'numeric', 'min:1', 'max:100000'],
            'moisture_pct' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);
    }

    private function batchData(Request $request): array
    {
        return $request->validate([
            'storage_unit_id' => ['required', Rule::exists('storage_units', 'id')->where('farm_id', $request->user()->farm_id)],
            'fill_start' => ['required', 'date', 'before_or_equal:today'],
            'sealed_date' => ['nullable', 'date', 'after_or_equal:fill_start', 'before_or_equal:today'],
            'fresh_weight_kg' => ['required', 'numeric', 'min:1', 'max:999999999'],
            'method' => ['required', Rule::in(SilageBatch::METHODS)],
            'expected_loss_pct' => ['required', 'numeric', 'min:0', 'max:60'],
        ]);
    }

    private function unitData(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:60'],
            'type' => ['required', Rule::in(StorageUnit::TYPES)],
            'capacity_tonnes' => ['required', 'numeric', 'min:0.1', 'max:100000'],
        ]);
    }

    private function back(CropSeason $season, string $message): RedirectResponse
    {
        return redirect()->route('production.index', ['season' => $season->code])->with('status', $message);
    }
}
