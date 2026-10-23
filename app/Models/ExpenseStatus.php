<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExpenseStatus extends Model
{
    protected $table = 'v_expense_status';

    protected $primaryKey = 'expense_id';

    public $incrementing = false;

    public $timestamps = false;

    protected function casts(): array
    {
        return ['amount' => 'decimal:2', 'paid' => 'decimal:2', 'balance' => 'decimal:2', 'is_notional' => 'boolean', 'expense_date' => 'date'];
    }
}
