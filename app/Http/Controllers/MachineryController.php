<?php

namespace App\Http\Controllers;

use App\Models\CropSeason;
use App\Models\ExpenseCategory;
use App\Models\Machine;
use App\Models\MachineUsage;
use App\Models\SharedCost;
use App\Models\Vendor;
use App\Services\MachineService;
use App\Services\SharedCostService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MachineryController extends Controller
{
    public function __construct(private MachineService $machines, private SharedCostService $shared) {}

    public function index(): View
    {
        $machines = Machine::query()->orderBy('name')->get();
        $totals = DB::table('resource_usage')->whereIn('resource_id', $machines->pluck('id'))
            ->groupBy('resource_id')
            ->selectRaw('resource_id, SUM(hours) as hours, SUM(acres) as acres, SUM(fuel_litres) as litres, COUNT(*) as entries')
            ->get()->keyBy('resource_id');

        return view('machinery.index', [
            'machines' => $machines,
            'totals' => $totals,
            'usage' => MachineUsage::query()->whereIn('resource_id', $machines->pluck('id'))
                ->with(['machine', 'stage.season', 'stage.template'])->latest('usage_date')->latest('id')->limit(25)->get(),
            'sharedCosts' => SharedCost::query()->with(['category.parent', 'machine', 'vendor', 'expenses.season'])->latest('cost_date')->latest('id')->get(),
            'seasons' => $this->openSeasons(),
            'categories' => ExpenseCategory::tree(),
            'vendors' => Vendor::query()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function storeMachine(Request $request): RedirectResponse
    {
        $this->authorize('manage-machinery');
        Machine::create($this->machineData($request));

        return back()->with('status', __('Machine added.'));
    }

    public function updateMachine(Request $request, Machine $machine): RedirectResponse
    {
        $this->authorize('manage-machinery');
        $machine->update($this->machineData($request) + ['is_active' => $request->boolean('is_active')]);

        return back()->with('status', __('Machine updated.'));
    }

    public function storeUsage(Request $request): RedirectResponse
    {
        $this->authorize('enter-expenses');
        $this->machines->logUsage($this->usageData($request), $request->boolean('add_notional') && $request->user()->can('manage-machinery'));

        return back()->with('status', __('Machine hours logged.'));
    }

    public function destroyUsage(MachineUsage $usage): RedirectResponse
    {
        $this->authorize('manage-machinery');
        abort_unless(Machine::whereKey($usage->resource_id)->exists(), 404);
        $this->machines->deleteUsage($usage);

        return back()->with('status', __('Hour log entry deleted.'));
    }

    public function storeShared(Request $request): RedirectResponse
    {
        $this->authorize('manage-machinery');
        $farm = $request->user()->farm_id;
        $data = $request->validate([
            'cost_date' => ['required', 'date', 'before_or_equal:today'],
            'category_id' => ['required', Rule::exists('expense_categories', 'id')->where(fn ($q) => $q->whereNull('farm_id')->orWhere('farm_id', $farm))],
            'resource_id' => ['nullable', 'required_if:basis,hours,acres', Rule::exists('resources', 'id')->where('farm_id', $farm)],
            'vendor_id' => ['nullable', Rule::exists('vendors', 'id')->where('farm_id', $farm)],
            'description' => ['required', 'string', 'max:200'],
            'total_amount' => ['required', 'numeric', 'min:0.01', 'max:999999999999'],
            'basis' => ['required', Rule::in(SharedCost::BASES)],
        ]);
        $cost = SharedCost::create($data + ['status' => 'draft']);

        return redirect()->route('machinery.shared.show', $cost)->with('status', __('Shared cost saved. Choose the stages to split it across.'));
    }

    public function showShared(Request $request, SharedCost $cost): View
    {
        $cost->load(['category.parent', 'machine', 'vendor', 'expenses.season', 'expenses.stage.template']);
        $seasons = $this->openSeasons();
        $stageIds = array_map('intval', $request->query('stages', []));

        if (! $stageIds && $cost->resource_id && in_array($cost->basis, ['hours', 'acres'], true)) {
            $stageIds = MachineUsage::query()->where('resource_id', $cost->resource_id)
                ->whereIn('season_stage_id', $seasons->flatMap->stages->pluck('id'))
                ->distinct()->pluck('season_stage_id')->all();
        }

        $manual = array_map('floatval', (array) $request->query('manual', []));

        return view('machinery.split', [
            'cost' => $cost,
            'seasons' => $seasons,
            'selected' => $stageIds,
            'manual' => $manual,
            'preview' => $cost->status === 'draft' ? $this->shared->preview($cost, $stageIds, $manual) : collect(),
        ]);
    }

    public function allocate(Request $request, SharedCost $cost): RedirectResponse
    {
        $this->authorize('manage-machinery');
        $data = $request->validate([
            'stages' => ['required', 'array', 'min:1'],
            'stages.*' => ['integer'],
            'manual' => ['nullable', 'array'],
            'manual.*' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);
        $this->shared->allocate($cost, array_map('intval', $data['stages']), $data['manual'] ?? []);

        return redirect()->route('machinery.index')->with('status', __('“:cost” split into :count stage expenses.', ['cost' => $cost->description, 'count' => $cost->expenses()->count()]));
    }

    public function unallocate(SharedCost $cost): RedirectResponse
    {
        $this->authorize('manage-machinery');
        $this->shared->unallocate($cost);

        return redirect()->route('machinery.shared.show', $cost)->with('status', __('Split undone. The cost is a draft again.'));
    }

    public function destroyShared(SharedCost $cost): RedirectResponse
    {
        $this->authorize('manage-machinery');
        abort_if($cost->status === 'allocated', 422, __('Undo the split before deleting this cost.'));
        $cost->delete();

        return redirect()->route('machinery.index')->with('status', __('Shared cost deleted.'));
    }

    private function openSeasons()
    {
        return CropSeason::query()->where('status', '!=', 'closed')->with(['crop', 'stages.template'])->orderByDesc('start_date')->get();
    }

    private function machineData(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'type' => ['required', Rule::in(Machine::TYPES)],
            'ownership' => ['required', Rule::in(['owned', 'rented'])],
            'internal_rate_per_hour' => ['nullable', 'numeric', 'min:0', 'max:99999999'],
        ]);
    }

    private function usageData(Request $request): array
    {
        return $request->validate([
            'resource_id' => ['required', Rule::exists('resources', 'id')->where('farm_id', $request->user()->farm_id)],
            'season_stage_id' => ['required', 'integer', Rule::exists('season_stages', 'id')->whereIn('season_id', CropSeason::query()->where('status', '!=', 'closed')->pluck('id'))],
            'usage_date' => ['required', 'date', 'before_or_equal:today'],
            'hours' => ['required', 'numeric', 'min:0.1', 'max:9999'],
            'acres' => ['nullable', 'numeric', 'min:0', 'max:99999'],
            'fuel_litres' => ['nullable', 'numeric', 'min:0', 'max:99999'],
            'operator' => ['nullable', 'string', 'max:80'],
        ]);
    }
}
