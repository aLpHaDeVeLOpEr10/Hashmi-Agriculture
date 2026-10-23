<?php

namespace App\Models;

use App\Models\Concerns\Audited;
use App\Models\Concerns\BelongsToFarm;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Expense extends Model
{
    use Audited, BelongsToFarm, SoftDeletes;

    protected $guarded = ['id', 'farm_id', 'created_by'];

    protected function casts(): array
    {
        return [
            'expense_date' => 'date',
            'quantity' => 'decimal:3',
            'unit_price' => 'decimal:2',
            'amount' => 'decimal:2',
            'is_notional' => 'boolean',
        ];
    }

    public function season(): BelongsTo
    {
        return $this->belongsTo(CropSeason::class, 'season_id');
    }

    public function stage(): BelongsTo
    {
        return $this->belongsTo(SeasonStage::class, 'season_stage_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class, 'category_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'unit_code', 'code');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function machine(): BelongsTo
    {
        return $this->belongsTo(Machine::class, 'resource_id');
    }

    public function field(): BelongsTo
    {
        return $this->belongsTo(Field::class);
    }

    public function sharedCost(): BelongsTo
    {
        return $this->belongsTo(SharedCost::class);
    }

    public function status(): HasOne
    {
        return $this->hasOne(ExpenseStatus::class, 'expense_id');
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(PaymentAllocation::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class, 'owner_id')->where('owner_table', 'expenses');
    }
}
