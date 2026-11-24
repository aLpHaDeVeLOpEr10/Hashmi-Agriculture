<?php

namespace App\Http\Controllers;

use App\Models\CropSeason;
use App\Models\Expense;
use App\Models\SeasonStage;
use App\Support\CostReport;
use App\Support\Pagination;
use App\Support\ReportExporter;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

class ReportController extends Controller
{
    public function __construct(private CostReport $report, private ReportExporter $exporter) {}

    public function index(): Response
    {
        return response()->view('reports.index', [
            'seasons' => CropSeason::query()->with(['crop', 'summary'])->orderByDesc('start_date')->get(),
        ]);
    }

    public function season(Request $request, CropSeason $season): Response
    {
        $season->load(['crop', 'fields', 'summary', 'stages.template', 'stages.totals', 'harvests', 'batches.storageUnit']);
        $code = $request->validate(['stage' => ['nullable', Rule::in($season->stages->map(fn ($s) => $s->template->code)->all())]])['stage'] ?? null;
        $stage = $code ? $season->stages->first(fn ($s) => $s->template->code === $code) : null;

        $expenses = Expense::query()->where('season_id', $season->id)
            ->when($stage, fn ($q) => $q->where('season_stage_id', $stage->id))
            ->with(['stage.template', 'category.parent', 'vendor', 'status'])->orderBy('expense_date')->get();
        $data = [
            'season' => $season,
            'stage' => $stage,
            'kpis' => $this->seasonKpis($season, $stage, $expenses),
            'categories' => $this->report->categoryBreakdown($season, $stage?->id),
            'expenses' => $expenses,
        ];
        $filename = 'season-summary-'.$season->code.($stage ? '-'.strtolower($code) : '');

        return match ($this->format($request)) {
            'pdf' => $this->exporter->pdf('reports.pdf.season', $data, $filename),
            'xlsx' => $this->exporter->xlsx($this->seasonSheets($data), $filename),
            // On screen the expense list is paged; exports above always contain every line.
            default => response()->view('reports.season', ['expenses' => Pagination::collection($expenses, $request, fragment: 'expenses')] + $data),
        };
    }

    public function comparison(Request $request): Response
    {
        $seasons = $this->selectedSeasons($request, 2);
        $data = $this->report->comparison($seasons) + ['all' => $this->allSeasons()];

        return match ($this->format($request)) {
            'pdf' => $this->exporter->pdf('reports.pdf.comparison', $data + ['landscape' => true], 'season-comparison'),
            'xlsx' => $this->exporter->xlsx([__('Comparison') => $this->comparisonRows($data)], 'season-comparison'),
            default => response()->view('reports.comparison', $data),
        };
    }

    public function categories(Request $request): Response
    {
        $range = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);
        $seasons = $this->selectedSeasons($request, 1);
        $data = $this->report->categoryMatrix($seasons, $range['from'] ?? null, $range['to'] ?? null) + ['all' => $this->allSeasons(), 'from' => $range['from'] ?? null, 'to' => $range['to'] ?? null];

        return match ($this->format($request)) {
            'pdf' => $this->exporter->pdf('reports.pdf.categories', $data + ['landscape' => $seasons->count() > 3], 'category-breakdown'),
            'xlsx' => $this->exporter->xlsx([__('Categories') => $this->categoryRows($data)], 'category-breakdown'),
            default => response()->view('reports.categories', $data),
        };
    }

    public function ageing(Request $request): Response
    {
        $bills = $this->report->outstandingBills();
        $data = ['bills' => $bills, 'buckets' => $this->report->ageingBuckets($bills), 'vendors' => $this->report->ageingByVendor($bills)];

        return match ($this->format($request)) {
            'pdf' => $this->exporter->pdf('reports.pdf.ageing', $data, 'payables-ageing'),
            'xlsx' => $this->exporter->xlsx([
                __('By vendor') => array_merge([[__('Vendor'), ...array_map(fn ($b) => $b.' '.__('days'), CostReport::AGEING_BUCKETS), __('Total')]], $data['vendors']->map(fn ($v) => array_values($v))->all()),
                __('Bills') => array_merge([[__('Date'), __('Vendor'), __('Season'), __('Stage'), __('Description'), __('Amount'), __('Paid'), __('Balance'), __('Days')]],
                    $bills->map(fn ($b) => [$b->expense_date, $b->vendor_name ?? '', $b->season_code, __($b->stage_name), $b->description, (float) $b->amount, (float) $b->paid, (float) $b->balance, $b->age_days])->all()),
            ], 'payables-ageing'),
            default => response()->view('reports.ageing', $data),
        };
    }

    private function format(Request $request): string
    {
        return $request->validate(['format' => ['nullable', Rule::in(['html', 'pdf', 'xlsx'])]])['format'] ?? 'html';
    }

    private function allSeasons(): Collection
    {
        return CropSeason::query()->with('crop')->orderByDesc('start_date')->get();
    }

    private function selectedSeasons(Request $request, int $default): Collection
    {
        $codes = $request->validate(['seasons' => ['nullable', 'array'], 'seasons.*' => ['string', 'max:20']])['seasons'] ?? [];
        $query = CropSeason::query()->orderBy('start_date');

        return $codes
            ? $query->whereIn('code', $codes)->get()
            : $query->latest('start_date')->limit($default)->get()->sortBy('start_date')->values();
    }

    /**
     * Headline figures for the season, or for one stage of it. Cost ratios use the same
     * formulas as v_season_summary, applied to the stage's own total when a stage is chosen.
     */
    private function seasonKpis(CropSeason $season, ?SeasonStage $stage, Collection $expenses): object
    {
        $summary = $season->summary;
        $total = (float) ($stage ? $stage->totals?->total : $summary->total_cost);
        $acres = (float) $season->total_area_acres;
        $harvested = (float) $summary->harvested_kg;
        $usable = (float) $summary->silage_usable_kg;
        $incomplete = $expenses->where('completeness', 'incomplete');
        $rework = $expenses->where('is_rework', true);

        return (object) [
            'provisional' => $season->harvests->contains('method', 'estimate'),
            'lines' => $expenses->count(),
            'incomplete_lines' => $incomplete->count(),
            'incomplete_amount' => (float) $incomplete->sum('amount'),
            'incomplete_share' => $total > 0 ? $incomplete->sum('amount') / $total * 100 : 0.0,
            'rework_lines' => $rework->count(),
            'rework_amount' => (float) $rework->sum('amount'),
            'cost_per_kg' => $harvested > 0 ? $total / $harvested : null,
            'cost_per_maund' => $harvested > 0 ? $total * CostReport::MAUND_KG / $harvested : null,
            'yield_maund_per_acre' => $summary->yield_kg_per_acre ? (float) $summary->yield_kg_per_acre / CostReport::MAUND_KG : null,
            'benchmark_yield_maund' => $season->benchmark_yield_maund !== null ? (float) $season->benchmark_yield_maund : null,
            'dry_matter_pct' => (float) $season->dry_matter_pct,
            'dm_kg' => ($basis = $usable > 0 ? (float) $summary->silage_fresh_kg : $harvested) * (float) $season->dry_matter_pct / 100,
            'cost_per_kg_dm' => $basis > 0 && (float) $season->dry_matter_pct > 0 ? $total / ($basis * (float) $season->dry_matter_pct / 100) : null,
            'total_label' => $stage ? __('Stage total') : __('Season total'),
            'total' => $total,
            'paid' => (float) ($stage ? $stage->totals?->paid : $summary->paid),
            'outstanding' => (float) ($stage ? $stage->totals?->balance : $summary->outstanding),
            'cost_per_acre' => $acres > 0 ? $total / $acres : null,
            'harvested_kg' => $harvested,
            'yield_kg_per_acre' => $summary->yield_kg_per_acre,
            'cost_per_tonne_harvested' => $harvested > 0 ? $total * 1000 / $harvested : null,
            'silage_usable_kg' => $usable,
            'silage_cost_per_kg' => $usable > 0 ? $total / $usable : null,
            'silage_cost_per_tonne' => $usable > 0 ? $total * 1000 / $usable : null,
        ];
    }

    private function seasonSheets(array $data): array
    {
        $season = $data['season'];
        $kpis = $data['kpis'];

        return [
            __('Summary') => [
                [__('Measure'), __('Value')],
                [__('Season'), "{$season->name} ({$season->code})"],
                [__('Stage'), $data['stage']?->name() ?? __('All stages')],
                [__('Crop'), $season->crop->name],
                [__('Status'), CropSeason::statusLabel($season->status)],
                [__('Area (acres)'), (float) $season->total_area_acres],
                [$kpis->total_label, $kpis->total],
                [__('Cost per acre'), $kpis->cost_per_acre ?? ''],
                [__('Paid'), $kpis->paid],
                [__('Outstanding'), $kpis->outstanding],
                [__('Harvested (kg)'), $kpis->harvested_kg],
                [__('Usable silage (kg)'), $kpis->silage_usable_kg],
                [__('Cost per kg'), $kpis->cost_per_kg ?? ''],
                [__('Cost per maund'), $kpis->cost_per_maund ?? ''],
                [__('Cost per tonne harvested'), $kpis->cost_per_tonne_harvested ?? ''],
                [__('Yield (maund / acre)'), $kpis->yield_maund_per_acre ?? ''],
                [__('Benchmark yield (maund / acre)'), $kpis->benchmark_yield_maund ?? ''],
                [__('Dry matter %'), $kpis->dry_matter_pct],
                [__('Cost per kg DM'), $kpis->cost_per_kg_dm ?? ''],
                [__('Production basis'), $kpis->provisional ? __('Provisional estimate') : __('Measured')],
                [__('Transactions'), $kpis->lines],
                [__('Incomplete transactions'), $kpis->incomplete_lines],
                [__('Incomplete spend'), $kpis->incomplete_amount],
                [__('% spend incomplete'), round($kpis->incomplete_share, 2)],
                [__('Identified re-work'), $kpis->rework_amount],
                [__('Silage cost per tonne'), $kpis->silage_cost_per_tonne ?? ''],
            ],
            __('Stages') => array_merge([[__('Stage'), __('Total'), __('Paid'), __('Balance'), __('Lines')]],
                $season->stages->map(fn ($s) => [$s->name(), (float) $s->totals?->total, (float) $s->totals?->paid, (float) $s->totals?->balance, (int) $s->totals?->line_count])->all()),
            __('Categories') => array_merge([[__('Category'), __('Total'), __('Share %')]],
                $data['categories']->map(fn ($c) => [__($c->name), $c->total, $c->share])->all()),
            __('Expenses') => array_merge([[__('Date'), __('Stage'), __('Category'), __('Description'), __('Vendor'), __('Quantity'), __('Unit'), __('Unit price'), __('Amount'), __('Paid'), __('Payment'), __('Documentation'), __('Re-work')]],
                $data['expenses']->map(fn ($e) => [
                    $e->expense_date->toDateString(), $e->stage->name(), $e->category->fullName(), $e->description, $e->vendor?->name ?? '',
                    $e->quantity !== null ? (float) $e->quantity : '', $e->unit_code ?? '', $e->unit_price !== null ? (float) $e->unit_price : '',
                    (float) $e->amount, (float) $e->status?->paid, $e->status?->payment_status, $e->completeness, $e->is_rework ? __('Yes') : '',
                ])->all()),
        ];
    }

    private function comparisonRows(array $data): array
    {
        $header = [__('Measure'), ...$data['seasons']->map(fn ($s) => "{$s->name} ({$s->code})")->all()];
        foreach ($data['seasons']->skip(1) as $s) {
            $header[] = __('Change vs :code (%)', ['code' => $data['seasons']->first()->code]).' · '.$s->code;
        }

        return array_merge([$header], array_map(
            fn ($row) => [$row['label'], ...array_map(fn ($v) => $v ?? '', $row['values']), ...array_map(fn ($v) => $v ?? '', array_slice($row['change'], 1))],
            $data['rows'],
        ));
    }

    private function categoryRows(array $data): array
    {
        $header = [__('Category'), ...$data['seasons']->map(fn ($s) => $s->code)->all(), __('Total')];

        return array_merge(
            [$header],
            $data['rows']->map(fn ($row) => [__($row['category']), ...array_values($row['values']), $row['total']])->all(),
            [[__('Total'), ...array_values($data['totals']), array_sum($data['totals'])]],
        );
    }
}
