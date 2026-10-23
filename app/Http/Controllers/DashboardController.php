<?php

namespace App\Http\Controllers;

use App\Models\CropSeason;
use App\Models\Expense;
use App\Support\CostReport;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request, CostReport $report): View
    {
        $open = CropSeason::query()->where('status', '!=', 'closed')->with(['crop', 'summary'])->orderByDesc('start_date')->get();

        $focus = $open->firstWhere('code', $request->query('season'))
            ?? $open->firstWhere('status', 'active')
            ?? $open->first();
        $focus?->load(['stages.template', 'stages.totals']);

        return view('dashboard', [
            'openSeasons' => $open,
            'focus' => $focus,
            'activeSpend' => (float) $open->sum(fn ($s) => $s->summary?->total_cost),
            'monthSpend' => $report->spendThisMonth(),
            'outstanding' => $report->outstanding(),
            'latestSilageCost' => $report->latestSilageCostPerTonne(),
            'recent' => Expense::query()->with(['season', 'stage.template', 'category.parent', 'status'])
                ->latest('expense_date')->latest('id')->limit(10)->get(),
        ]);
    }
}
