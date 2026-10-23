<?php

namespace App\Services;

use App\Models\CropSeason;
use App\Models\Expense;
use App\Models\RecurringRule;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class RecurringService
{
    public function __construct(private ExpenseService $expenses) {}

    /**
     * Rules whose next period has arrived, for seasons that are still open. Each is shown as a draft to confirm or skip.
     */
    public function due(): Collection
    {
        return RecurringRule::query()
            ->where('is_active', true)
            ->whereDate('next_run_date', '<=', today())
            ->whereHas('stage.season', fn ($q) => $q->where('status', '!=', 'closed')->whereIn('id', CropSeason::query()->select('id')))
            ->with(['stage.season', 'stage.template', 'category.parent', 'vendor'])
            ->orderBy('next_run_date')
            ->get();
    }

    public function confirm(RecurringRule $rule, float|string $amount, string $date, ?string $description = null): Expense
    {
        return DB::transaction(function () use ($rule, $amount, $date, $description) {
            $expense = $this->expenses->save([
                'season_stage_id' => $rule->season_stage_id,
                'category_id' => $rule->category_id,
                'expense_date' => $date,
                'description' => $description ?: $this->periodDescription($rule),
                'amount' => $amount,
                'vendor_id' => $rule->vendor_id,
            ]);
            $expense->update(['recurring_rule_id' => $rule->id]);
            $this->advance($rule);

            return $expense;
        });
    }

    public function skip(RecurringRule $rule): void
    {
        $this->advance($rule);
    }

    public function periodDescription(RecurringRule $rule): string
    {
        $date = $rule->next_run_date;
        $period = $rule->frequency === 'monthly'
            ? $date->translatedFormat('F Y')
            : __('week of :date', ['date' => $date->translatedFormat('d M')]);

        return "{$rule->description} · {$period}";
    }

    public static function nextDate(Carbon $from, string $frequency): Carbon
    {
        return $frequency === 'weekly' ? $from->copy()->addWeek() : $from->copy()->addMonthNoOverflow();
    }

    private function advance(RecurringRule $rule): void
    {
        $next = self::nextDate($rule->next_run_date, $rule->frequency);
        $rule->next_run_date = $next;
        if ($rule->end_date && $next->gt($rule->end_date)) {
            $rule->is_active = false;
        }
        $rule->save();
    }
}
