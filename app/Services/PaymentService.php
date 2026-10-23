<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\ExpenseStatus;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PaymentService
{
    /**
     * @param  array{vendor_id?: ?int, payment_date: string, amount: float|string, method: string, reference?: ?string}  $data
     * @param  array<int, float|string>  $allocations  expense id => amount
     */
    public function record(array $data, array $allocations): Payment
    {
        $allocations = collect($allocations)->map(fn ($amount) => round((float) $amount, 2))->filter(fn ($amount) => $amount > 0);

        if ($allocations->isEmpty()) {
            throw ValidationException::withMessages(['allocations' => __('Tick at least one bill this payment settles.')]);
        }

        $amount = round((float) $data['amount'], 2);
        if (abs($allocations->sum() - $amount) > 0.009) {
            throw ValidationException::withMessages(['amount' => __('The bills ticked add up to :allocated, but the payment is :amount.', [
                'allocated' => number_format($allocations->sum(), 2), 'amount' => number_format($amount, 2),
            ])]);
        }

        return DB::transaction(function () use ($data, $amount, $allocations) {
            $expenses = Expense::query()->whereIn('id', $allocations->keys())->get()->keyBy('id');
            $balances = ExpenseStatus::query()->whereIn('expense_id', $allocations->keys())->pluck('balance', 'expense_id');

            foreach ($allocations as $expenseId => $allocated) {
                $expense = $expenses->get($expenseId);
                if (! $expense || $expense->is_notional) {
                    throw ValidationException::withMessages(['allocations' => __('One of the selected bills cannot be paid.')]);
                }
                if (! empty($data['vendor_id']) && $expense->vendor_id !== null && $expense->vendor_id !== (int) $data['vendor_id']) {
                    throw ValidationException::withMessages(['allocations' => __('“:bill” belongs to a different vendor.', ['bill' => $expense->description])]);
                }
                if ($allocated - (float) $balances->get($expenseId, 0) > 0.009) {
                    throw ValidationException::withMessages(['allocations' => __('“:bill” only has :balance left to pay.', ['bill' => $expense->description, 'balance' => number_format((float) $balances->get($expenseId, 0), 2)])]);
                }
            }

            $payment = Payment::create([
                'vendor_id' => $data['vendor_id'] ?? null,
                'payment_date' => $data['payment_date'],
                'amount' => $amount,
                'method' => $data['method'],
                'reference' => $data['reference'] ?? null,
            ]);

            foreach ($allocations as $expenseId => $allocated) {
                $payment->allocations()->create(['expense_id' => $expenseId, 'amount' => $allocated]);
            }

            return $payment;
        });
    }

    public function payExpense(Expense $expense, float|string $amount, string $date, string $method, ?string $reference = null): Payment
    {
        return $this->record([
            'vendor_id' => $expense->vendor_id,
            'payment_date' => $date,
            'amount' => $amount,
            'method' => $method,
            'reference' => $reference,
        ], [$expense->id => $amount]);
    }

    public function delete(Payment $payment): void
    {
        DB::transaction(function () use ($payment) {
            $payment->allocations()->get()->each->delete();
            $payment->delete();
        });
    }
}
