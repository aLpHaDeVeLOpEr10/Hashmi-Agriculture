<?php

namespace App\Http\Controllers;

use App\Models\CropSeason;
use App\Models\Expense;
use App\Support\CostReport;
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
        $data = [
            'season' => $season,
            'categories' => $this->report->categoryBreakdown($season),
            'expenses' => Expense::query()->where('season_id', $season->id)->with(['stage.template', 'category.parent', 'vendor', 'status'])->orderBy('expense_date')->get(),
        ];
        $filename = 'season-summary-'.$season->code;

        return match ($this->format($request)) {
            'pdf' => $this->exporter->pdf('reports.pdf.season', $data, $filename),
            'xlsx' => $this->exporter->xlsx($this->seasonSheets($data), $filename),
            default => response()->view('reports.season', $data),
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

    private function seasonSheets(array $data): array
    {
        $season = $data['season'];
        $summary = $season->summary;

        return [
            __('Summary') => [
                [__('Measure'), __('Value')],
                [__('Season'), "{$season->name} ({$season->code})"],
                [__('Crop'), $season->crop->name],
                [__('Status'), CropSeason::statusLabel($season->status)],
                [__('Area (acres)'), (float) $season->total_area_acres],
                [__('Season total'), (float) $summary->total_cost],
                [__('Cost per acre'), (float) $summary->cost_per_acre],
                [__('Paid'), (float) $summary->paid],
                [__('Outstanding'), (float) $summary->outstanding],
                [__('Harvested (kg)'), (float) $summary->harvested_kg],
                [__('Usable silage (kg)'), (float) $summary->silage_usable_kg],
                [__('Cost per tonne harvested'), $summary->cost_per_tonne_harvested !== null ? (float) $summary->cost_per_tonne_harvested : ''],
                [__('Silage cost per tonne'), $summary->silage_cost_per_tonne !== null ? (float) $summary->silage_cost_per_tonne : ''],
            ],
            __('Stages') => array_merge([[__('Stage'), __('Total'), __('Paid'), __('Balance'), __('Lines')]],
                $season->stages->map(fn ($s) => [$s->name(), (float) $s->totals?->total, (float) $s->totals?->paid, (float) $s->totals?->balance, (int) $s->totals?->line_count])->all()),
            __('Categories') => array_merge([[__('Category'), __('Total'), __('Share %')]],
                $data['categories']->map(fn ($c) => [__($c->name), $c->total, $c->share])->all()),
            __('Expenses') => array_merge([[__('Date'), __('Stage'), __('Category'), __('Description'), __('Vendor'), __('Quantity'), __('Unit'), __('Unit price'), __('Amount'), __('Paid'), __('Payment')]],
                $data['expenses']->map(fn ($e) => [
                    $e->expense_date->toDateString(), $e->stage->name(), $e->category->fullName(), $e->description, $e->vendor?->name ?? '',
                    $e->quantity !== null ? (float) $e->quantity : '', $e->unit_code ?? '', $e->unit_price !== null ? (float) $e->unit_price : '',
                    (float) $e->amount, (float) $e->status?->paid, $e->status?->payment_status,
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
