<?php

namespace App\Console\Commands;

use App\Models\CropSeason;
use App\Models\Expense;
use App\Models\ExpenseStatus;
use App\Models\Payment;
use App\Models\User;
use App\Services\PaymentService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MarkSeasonPaid extends Command
{
    protected $signature = 'season:mark-paid
        {season : Season code, e.g. CS-2026-06}
        {--except= : Comma-separated stage template codes to leave unpaid}
        {--method=cash : Payment method recorded on each payment}
        {--reference=Settled per workbook : Reference recorded on each payment}
        {--dry-run : Show what would be paid without writing anything}';

    protected $description = 'Settle the outstanding balance of every expense in a season, except the excluded stages';

    public function handle(PaymentService $payments): int
    {
        $season = CropSeason::withoutGlobalScopes()->where('code', $this->argument('season'))->first();
        if (! $season) {
            $this->error('No season with code '.$this->argument('season'));

            return self::FAILURE;
        }

        $method = $this->option('method');
        if (! in_array($method, Payment::METHODS, true)) {
            $this->error('Method must be one of: '.implode(', ', Payment::METHODS));

            return self::FAILURE;
        }

        $owner = User::query()->where('farm_id', $season->farm_id)->where('role', 'owner')->first();
        if (! $owner) {
            $this->error("No owner user found for farm {$season->farm_id}");

            return self::FAILURE;
        }
        Auth::setUser($owner);

        $except = array_filter(array_map(fn ($c) => strtoupper(trim($c)), explode(',', (string) $this->option('except'))));

        $expenses = Expense::query()
            ->whereHas('stage', fn ($q) => $q->where('season_id', $season->id))
            ->whereDoesntHave('stage.template', fn ($q) => $q->whereIn('code', $except))
            ->where('is_notional', false)
            ->orderBy('expense_date')->orderBy('id')
            ->get();

        $balances = ExpenseStatus::query()->whereIn('expense_id', $expenses->modelKeys())->pluck('balance', 'expense_id');
        $due = $expenses->filter(fn (Expense $e) => (float) $balances->get($e->id, 0) > 0.009);

        $this->info(sprintf(
            '%s (%s): %d lines outside [%s], %d with a balance, Rs %s to settle.',
            $season->name, $season->code, $expenses->count(), implode(', ', $except), $due->count(),
            number_format($due->sum(fn ($e) => (float) $balances[$e->id]), 2),
        ));

        if ($this->option('dry-run') || $due->isEmpty()) {
            Auth::forgetUser();

            return self::SUCCESS;
        }

        DB::transaction(function () use ($due, $balances, $payments, $method) {
            foreach ($due as $expense) {
                $payments->payExpense($expense, $balances[$expense->id], $expense->expense_date->toDateString(), $method, $this->option('reference'));
            }
        });

        $this->info("Recorded {$due->count()} payments.");
        Auth::forgetUser();

        return self::SUCCESS;
    }
}
