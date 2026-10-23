<?php

namespace App\Models;

use App\Models\Concerns\Audited;
use App\Services\MachineService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

class ExpenseCategory extends Model
{
    use Audited;

    public const COST_TYPES = ['fuel', 'labour', 'machinery', 'maintenance', 'material', 'service', 'transport', 'utility', 'other'];

    protected $guarded = ['id', 'farm_id', 'created_by'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    protected static function booted(): void
    {
        static::addGlobalScope('farm', function (Builder $builder) {
            if ($farmId = Auth::user()?->farm_id) {
                $builder->where(fn ($q) => $q->whereNull('expense_categories.farm_id')->orWhere('expense_categories.farm_id', $farmId));
            }
        });
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('name');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'default_unit', 'code');
    }

    public function isSystem(): bool
    {
        return $this->farm_id === null;
    }

    public function fullName(): string
    {
        return $this->parent ? $this->parent->name.' → '.$this->name : $this->name;
    }

    public static function tree(): Collection
    {
        return static::query()->whereNull('parent_id')->where('is_active', true)
            ->with(['children' => fn ($q) => $q->where('is_active', true)->where('name', '!=', MachineService::NOTIONAL_CATEGORY)])
            ->orderBy('name')->get();
    }
}
