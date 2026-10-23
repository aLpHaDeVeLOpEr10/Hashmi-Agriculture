<?php

namespace App\Services;

use App\Models\CropSeason;
use App\Models\Expense;
use App\Models\MachineUsage;
use App\Models\SeasonStage;
use App\Models\SharedCost;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SharedCostService
{
    public function __construct(private SeasonService $seasons) {}

    /**
     * Stages this cost could be split across, with the weight each would get under the cost's basis.
     *
     * @param  array<int, float|string>  $manual  stage id => percentage, for the manual basis
     * @return Collection<int, object{stage: SeasonStage, weight: float, share: float, amount: float}>
     */
    public function preview(SharedCost $cost, array $stageIds, array $manual = []): Collection
    {
        $stages = SeasonStage::query()->with(['season', 'template'])->whereIn('id', $stageIds)
            ->whereIn('season_id', CropSeason::query()->select('id'))->get()->keyBy('id');

        $weights = match ($cost->basis) {
            'hours', 'acres' => $this->usageWeights($cost, $stages->keys()->all()),
            'equal' => $stages->map(fn () => 1.0),
            'manual' => $stages->map(fn ($stage) => (float) ($manual[$stage->id] ?? 0)),
        };

        $total = $weights->sum();
        if ($stages->isEmpty() || $total <= 0) {
            return collect();
        }

        $rows = $stages->map(fn ($stage) => (object) [
            'stage' => $stage,
            'weight' => (float) ($weights[$stage->id] ?? 0),
            'share' => (float) ($weights[$stage->id] ?? 0) / $total,
            'amount' => 0.0,
        ])->filter(fn ($row) => $row->weight > 0)->values();

        $remaining = round((float) $cost->total_amount, 2);
        foreach ($rows as $i => $row) {
            $row->amount = $i === $rows->count() - 1 ? $remaining : round((float) $cost->total_amount * $row->share, 2);
            $remaining = round($remaining - $row->amount, 2);
        }

        return $rows;
    }

    public function allocate(SharedCost $cost, array $stageIds, array $manual = []): void
    {
        if ($cost->status === 'allocated') {
            throw ValidationException::withMessages(['shared_cost' => __('This cost has already been split.')]);
        }

        if ($cost->basis === 'manual' && abs(array_sum(array_map('floatval', array_intersect_key($manual, array_flip($stageIds)))) - 100) > 0.01) {
            throw ValidationException::withMessages(['manual' => __('Manual shares must add up to 100%.')]);
        }

        $rows = $this->preview($cost, $stageIds, $manual);
        if ($rows->isEmpty()) {
            throw ValidationException::withMessages(['stages' => $cost->basis === 'hours' || $cost->basis === 'acres'
                ? __('None of the selected stages has machine :basis logged for this machine.', ['basis' => __($cost->basis)])
                : __('Select at least one stage.')]);
        }

        DB::transaction(function () use ($cost, $rows) {
            foreach ($rows as $row) {
                $this->seasons->ensureOpen($row->stage->season);
                Expense::create([
                    'season_id' => $row->stage->season_id,
                    'season_stage_id' => $row->stage->id,
                    'category_id' => $cost->category_id,
                    'expense_date' => $cost->cost_date,
                    'description' => $cost->description.' · '.__(':pct% share', ['pct' => round($row->share * 100, 1)]),
                    'amount' => $row->amount,
                    'vendor_id' => $cost->vendor_id,
                    'resource_id' => $cost->resource_id,
                    'shared_cost_id' => $cost->id,
                ]);
                if ($row->stage->status === 'not_started') {
                    $row->stage->update(['status' => 'in_progress', 'start_date' => $row->stage->start_date ?? $cost->cost_date]);
                }
            }
            $cost->update(['status' => 'allocated']);
        });
    }

    public function unallocate(SharedCost $cost): void
    {
        DB::transaction(function () use ($cost) {
            $lines = $cost->expenses()->with('season')->get();
            foreach ($lines as $line) {
                $this->seasons->ensureOpen($line->season);
                if ($line->allocations()->exists()) {
                    throw ValidationException::withMessages(['shared_cost' => __('Part of this cost has been paid. Remove the payment before undoing the split.')]);
                }
            }
            $lines->each->forceDelete();
            $cost->update(['status' => 'draft']);
        });
    }

    private function usageWeights(SharedCost $cost, array $stageIds): Collection
    {
        if (! $cost->resource_id) {
            throw ValidationException::withMessages(['basis' => __('Splitting by hours or acres needs a machine on the shared cost.')]);
        }

        $column = $cost->basis === 'hours' ? 'hours' : 'acres';

        return MachineUsage::query()
            ->where('resource_id', $cost->resource_id)
            ->whereIn('season_stage_id', $stageIds)
            ->groupBy('season_stage_id')
            ->selectRaw("season_stage_id, SUM({$column}) as weight")
            ->pluck('weight', 'season_stage_id')
            ->map(fn ($w) => (float) $w);
    }
}
