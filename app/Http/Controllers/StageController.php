<?php

namespace App\Http\Controllers;

use App\Models\CropSeason;
use App\Models\Expense;
use App\Models\SeasonStage;
use App\Services\SeasonService;
use App\Support\CostReport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StageController extends Controller
{
    public function show(CropSeason $season, SeasonStage $stage, CostReport $report): View
    {
        $stage->load(['template', 'totals']);
        $season->load(['summary', 'stages.template']);

        return view('stages.show', [
            'season' => $season,
            'stage' => $stage,
            'expenses' => Expense::query()->where('season_stage_id', $stage->id)
                ->with(['category.parent', 'unit', 'vendor', 'machine', 'field', 'status', 'attachments'])
                ->orderByDesc('expense_date')->orderByDesc('id')->get(),
            'subtotals' => $report->stageCategorySubtotals($stage->id),
        ]);
    }

    public function status(Request $request, CropSeason $season, SeasonStage $stage, SeasonService $seasons): RedirectResponse
    {
        $this->authorize('manage-seasons');
        $data = $request->validate(['status' => ['required', Rule::in(SeasonStage::STATUSES)]]);

        $stage->setRelation('season', $season);
        $seasons->setStageStatus($stage, $data['status']);

        return back()->with('status', __(':stage marked :status.', ['stage' => $stage->name(), 'status' => SeasonStage::statusLabel($data['status'])]));
    }
}
