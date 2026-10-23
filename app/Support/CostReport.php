<?php

namespace App\Support;

use App\Models\CropSeason;
use App\Models\ExpenseCategory;
use App\Models\SilageBatch;
use App\Models\StageTemplate;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CostReport
{
    public const AGEING_BUCKETS = ['0–30', '31–60', '61–90', '90+'];

    public function categoryBreakdown(CropSeason $season): Collection
    {
        $rows = DB::table('v_category_breakdown')->where('season_id', $season->id)->get();
        $categories = ExpenseCategory::query()->whereIn('id', $rows->pluck('category_id')->merge($rows->pluck('parent_category_id'))->filter())->get()->keyBy('id');
        $total = max((float) $rows->sum('total'), 0.01);

        return $rows
            ->groupBy(fn ($row) => $row->parent_category_id ?? $row->category_id)
            ->map(fn ($group, $parentId) => (object) [
                'name' => $categories[$parentId]->name ?? __('Other'),
                'cost_type' => $group->first()->cost_type,
                'total' => (float) $group->sum('total'),
                'lines' => (int) $group->sum('line_count'),
                'share' => round($group->sum('total') / $total * 100, 1),
            ])
            ->sortByDesc('total')
            ->values();
    }

    public function stageCategorySubtotals(int $stageId): Collection
    {
        return DB::table('v_expense_status as v')
            ->join('expense_categories as c', 'c.id', '=', 'v.category_id')
            ->leftJoin('expense_categories as p', 'p.id', '=', 'c.parent_id')
            ->where('v.season_stage_id', $stageId)
            ->groupBy('c.id', 'c.name', 'p.name')
            ->selectRaw('c.name as name, p.name as parent, SUM(v.amount) as total, COUNT(*) as lines')
            ->orderByDesc('total')
            ->get();
    }

    public function spendThisMonth(): float
    {
        return (float) DB::table('v_expense_status')
            ->where('farm_id', Auth::user()->farm_id)
            ->where('is_notional', false)
            ->whereBetween('expense_date', [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()])
            ->sum('amount');
    }

    public function outstanding(): float
    {
        return (float) DB::table('v_expense_status')->where('farm_id', Auth::user()->farm_id)->sum('balance');
    }

    public function latestSilageCostPerTonne(): ?float
    {
        $batch = SilageBatch::query()
            ->whereIn('season_id', CropSeason::query()->select('id'))
            ->with('season.summary')
            ->latest('fill_start')->latest('id')
            ->first();

        $perKg = $batch?->cost_per_kg ?? $batch?->season->summary?->silage_cost_per_kg;

        return $perKg !== null ? round((float) $perKg * 1000, 2) : null;
    }

    public function outstandingBills(): Collection
    {
        return DB::table('v_expense_status as v')
            ->join('expenses as e', 'e.id', '=', 'v.expense_id')
            ->join('crop_seasons as s', 's.id', '=', 'v.season_id')
            ->join('season_stages as ss', 'ss.id', '=', 'v.season_stage_id')
            ->join('stage_templates as t', 't.id', '=', 'ss.stage_template_id')
            ->leftJoin('vendors as vd', 'vd.id', '=', 'v.vendor_id')
            ->where('v.farm_id', Auth::user()->farm_id)
            ->where('v.balance', '>', 0)
            ->orderBy('v.expense_date')->orderBy('v.expense_id')
            ->get([
                'v.expense_id', 'v.expense_date', 'v.amount', 'v.paid', 'v.balance', 'v.payment_status', 'v.vendor_id',
                'e.description', 'vd.name as vendor_name', 'vd.phone as vendor_phone',
                's.code as season_code', 's.name as season_name', 't.name as stage_name',
            ])
            ->each(fn ($bill) => $bill->age_days = (int) Carbon::parse($bill->expense_date)->diffInDays(today()));
    }

    public function ageingBuckets(Collection $bills): array
    {
        $buckets = array_fill_keys(self::AGEING_BUCKETS, ['amount' => 0.0, 'count' => 0]);

        foreach ($bills as $bill) {
            $key = match (true) {
                $bill->age_days <= 30 => '0–30',
                $bill->age_days <= 60 => '31–60',
                $bill->age_days <= 90 => '61–90',
                default => '90+',
            };
            $buckets[$key]['amount'] += (float) $bill->balance;
            $buckets[$key]['count']++;
        }

        return $buckets;
    }

    public function ageingByVendor(Collection $bills): Collection
    {
        return $bills->groupBy(fn ($bill) => $bill->vendor_name ?? __('No vendor'))
            ->map(fn ($items, $vendor) => ['vendor' => $vendor] + array_map(fn ($b) => $b['amount'], $this->ageingBuckets($items)) + ['total' => (float) $items->sum('balance')])
            ->sortByDesc('total')
            ->values();
    }

    /**
     * @param  Collection<int, CropSeason>  $seasons  ordered oldest first; the first is the baseline
     */
    public function comparison(Collection $seasons): array
    {
        $seasons->load(['crop', 'summary', 'stages.template', 'stages.totals']);
        $templates = StageTemplate::query()
            ->whereIn('id', $seasons->flatMap(fn ($s) => $s->stages->pluck('stage_template_id'))->unique())
            ->orderBy('sequence')->get();

        $metrics = [
            'total_cost' => __('Season total'),
            'cost_per_acre' => __('Cost per acre'),
            'yield_kg_per_acre' => __('Yield (kg per acre)'),
            'cost_per_tonne_harvested' => __('Cost per tonne harvested'),
            'silage_cost_per_tonne' => __('Silage cost per tonne'),
        ];

        $rows = [];
        foreach ($metrics as $key => $label) {
            $rows[] = ['label' => $label, 'key' => $key, 'values' => $seasons->map(fn ($s) => $s->summary?->{$key} !== null ? (float) $s->summary->{$key} : null)->all()];
        }
        foreach ($templates as $template) {
            $rows[] = ['label' => __($template->name), 'key' => 'stage', 'values' => $seasons->map(fn ($s) => (float) ($s->stages->firstWhere('stage_template_id', $template->id)?->totals?->total ?? 0))->all()];
        }

        foreach ($rows as &$row) {
            $base = $row['values'][0] ?? null;
            $row['change'] = array_map(fn ($v) => $base > 0 && $v > 0 ? round(($v - $base) / $base * 100, 1) : null, $row['values']);
        }

        return ['seasons' => $seasons, 'rows' => $rows, 'templates' => $templates];
    }

    public function categoryMatrix(Collection $seasons, ?string $from = null, ?string $to = null): array
    {
        $rows = DB::table('v_expense_status as v')
            ->join('expense_categories as c', 'c.id', '=', 'v.category_id')
            ->leftJoin('expense_categories as p', 'p.id', '=', 'c.parent_id')
            ->whereIn('v.season_id', $seasons->pluck('id'))
            ->where('v.is_notional', false)
            ->when($from, fn ($q) => $q->where('v.expense_date', '>=', $from))
            ->when($to, fn ($q) => $q->where('v.expense_date', '<=', $to))
            ->groupBy('v.season_id', DB::raw('COALESCE(p.name, c.name)'), 'c.cost_type')
            ->selectRaw('v.season_id, COALESCE(p.name, c.name) as category, c.cost_type, SUM(v.amount) as total')
            ->get();

        $matrix = $rows->groupBy('category')->map(fn ($items, $category) => [
            'category' => $category,
            'cost_type' => $items->first()->cost_type,
            'values' => $seasons->mapWithKeys(fn ($s) => [$s->id => (float) $items->where('season_id', $s->id)->sum('total')])->all(),
            'total' => (float) $items->sum('total'),
        ])->sortByDesc('total')->values();

        return ['seasons' => $seasons, 'rows' => $matrix, 'totals' => $seasons->mapWithKeys(fn ($s) => [$s->id => (float) $rows->where('season_id', $s->id)->sum('total')])->all()];
    }
}
