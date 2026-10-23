<?php

namespace App\Services;

use App\Models\Attachment;
use App\Models\Expense;
use App\Models\SeasonStage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ExpenseService
{
    public function __construct(private SeasonService $seasons) {}

    public function save(array $data, ?Expense $expense = null, ?UploadedFile $receipt = null): Expense
    {
        $stage = SeasonStage::with('season')->findOrFail($data['season_stage_id']);
        $this->seasons->ensureOpen($stage->season);

        if ($expense && $expense->season_id !== $stage->season_id) {
            $this->seasons->ensureOpen($expense->season);
        }

        $data['amount'] = self::resolveAmount($data);

        if ($expense && (float) $data['amount'] < (float) $expense->allocations()->sum('amount')) {
            throw ValidationException::withMessages(['amount' => __('The amount cannot be lower than what has already been paid on this bill.')]);
        }

        return DB::transaction(function () use ($data, $expense, $stage, $receipt) {
            $expense ??= new Expense;
            $expense->fill([
                'season_id' => $stage->season_id,
                'season_stage_id' => $stage->id,
                'category_id' => $data['category_id'],
                'expense_date' => $data['expense_date'],
                'description' => $data['description'],
                'quantity' => $data['quantity'] ?? null,
                'unit_code' => $data['unit_code'] ?? null,
                'unit_price' => $data['unit_price'] ?? null,
                'amount' => $data['amount'],
                'vendor_id' => $data['vendor_id'] ?? null,
                'resource_id' => $data['resource_id'] ?? null,
                'field_id' => $data['field_id'] ?? null,
                'is_notional' => $data['is_notional'] ?? false,
            ]);
            $expense->save();

            $this->markProgress($stage, $expense);

            if ($receipt) {
                $this->attachReceipt($expense, $receipt);
            }

            return $expense;
        });
    }

    public function delete(Expense $expense): void
    {
        $this->seasons->ensureOpen($expense->season);

        if ($expense->allocations()->exists()) {
            throw ValidationException::withMessages(['expense' => __('This expense has payments recorded against it. Remove the payment allocation first.')]);
        }

        $expense->delete();
    }

    public static function resolveAmount(array $data): string
    {
        $quantity = $data['quantity'] ?? null;
        $price = $data['unit_price'] ?? null;
        $amount = $data['amount'] ?? null;

        if (($amount === null || $amount === '') && is_numeric($quantity) && is_numeric($price)) {
            $amount = round((float) $quantity * (float) $price, 2);
        }

        if ($amount === null || $amount === '' || ! is_numeric($amount) || (float) $amount <= 0) {
            throw ValidationException::withMessages(['amount' => __('Enter an amount, or a quantity and unit price.')]);
        }

        return number_format((float) $amount, 2, '.', '');
    }

    private function markProgress(SeasonStage $stage, Expense $expense): void
    {
        if ($stage->status === 'not_started') {
            $stage->update(['status' => 'in_progress', 'start_date' => $stage->start_date ?? $expense->expense_date]);
        }

        if ($stage->season->status === 'planned') {
            $stage->season->update(['status' => 'active']);
        }
    }

    private function attachReceipt(Expense $expense, UploadedFile $file): void
    {
        $path = $file->store('receipts/'.$expense->farm_id, 'public');

        Attachment::create([
            'owner_table' => 'expenses',
            'owner_id' => $expense->id,
            'file_url' => $path,
            'mime_type' => $file->getMimeType() ?? 'application/octet-stream',
        ]);
    }
}
