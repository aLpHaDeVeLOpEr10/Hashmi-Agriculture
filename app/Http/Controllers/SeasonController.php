<?php

namespace App\Http\Controllers;

use App\Http\Requests\SeasonRequest;
use App\Models\Crop;
use App\Models\CropSeason;
use App\Models\Expense;
use App\Models\Field;
use App\Services\SeasonService;
use App\Support\CostReport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SeasonController extends Controller
{
    public function __construct(private SeasonService $seasons) {}

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'status' => ['nullable', Rule::in(CropSeason::STATUSES)],
            'year' => ['nullable', 'integer', 'min:2000', 'max:2100'],
        ]);

        $seasons = CropSeason::query()
            ->with(['crop', 'summary'])
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->when($filters['year'] ?? null, fn ($q, $year) => $q->whereYear('start_date', $year))
            ->orderByDesc('start_date')
            ->get();

        $years = CropSeason::query()->pluck('start_date')->map->year->unique()->sortDesc()->values();

        return view('seasons.index', compact('seasons', 'years', 'filters'));
    }

    public function create(): View
    {
        $this->authorize('manage-seasons');

        return view('seasons.form', [
            'season' => new CropSeason(['start_date' => today()]),
            'crops' => Crop::query()->where('is_active', true)->orderBy('name')->get(),
            'fields' => Field::query()->where('is_active', true)->orderBy('name')->get(),
            'selected' => [],
        ]);
    }

    public function store(SeasonRequest $request): RedirectResponse
    {
        $season = $this->seasons->create($request->seasonData());

        return redirect()->route('seasons.show', $season)
            ->with('status', __('Season :code created with :count stages.', ['code' => $season->code, 'count' => $season->stages()->count()]));
    }

    public function show(CropSeason $season, CostReport $report): View
    {
        $season->load(['crop', 'fields', 'summary', 'stages.template', 'stages.totals', 'batches']);

        return view('seasons.show', [
            'season' => $season,
            'categories' => $report->categoryBreakdown($season),
            'recent' => Expense::query()->where('season_id', $season->id)
                ->with(['category.parent', 'stage.template', 'status', 'vendor'])
                ->latest('expense_date')->latest('id')->limit(8)->get(),
        ]);
    }

    public function edit(CropSeason $season): View
    {
        $this->authorize('manage-seasons');

        return view('seasons.form', [
            'season' => $season,
            'crops' => Crop::query()->where('is_active', true)->orderBy('name')->get(),
            'fields' => Field::query()->where('is_active', true)->orWhereIn('id', $season->fields->pluck('id'))->orderBy('name')->get(),
            'selected' => $season->fields->mapWithKeys(fn ($f) => [$f->id => $f->pivot->area_acres])->all(),
        ]);
    }

    public function update(SeasonRequest $request, CropSeason $season): RedirectResponse
    {
        $this->seasons->update($season, $request->seasonData());

        return redirect()->route('seasons.show', $season)->with('status', __('Season updated.'));
    }

    public function status(Request $request, CropSeason $season): RedirectResponse
    {
        $this->authorize('manage-seasons');
        $data = $request->validate(['status' => ['required', Rule::in(CropSeason::STATUSES)]]);

        $this->seasons->setStatus($season, $data['status']);

        return back()->with('status', __('Season status changed to :status.', ['status' => CropSeason::statusLabel($data['status'])]));
    }

    public function addStage(Request $request, CropSeason $season): RedirectResponse
    {
        $this->authorize('manage-seasons');
        $data = $request->validate(['name' => ['required', 'string', 'max:60']]);

        $this->seasons->addCustomStage($season, $data['name']);

        return back()->with('status', __('Custom stage “:name” added.', ['name' => $data['name']]));
    }

    public function close(CropSeason $season): RedirectResponse
    {
        $this->authorize('manage-seasons');
        $this->seasons->close($season);

        return back()->with('status', __('Season closed. Costs are frozen onto its silage batches and edits are locked.'));
    }

    public function reopen(CropSeason $season): RedirectResponse
    {
        $this->authorize('reopen-seasons');
        $this->seasons->reopen($season);

        return back()->with('status', __('Season reopened. Silage cost will be recalculated and frozen again when it is closed.'));
    }
}
