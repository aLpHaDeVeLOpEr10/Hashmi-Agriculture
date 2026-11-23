<?php

namespace App\Models;

use App\Models\Concerns\Audited;
use App\Services\MachineService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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

    /** Stages this category is limited to; none means every stage. */
    public function stages(): BelongsToMany
    {
        return $this->belongsToMany(StageTemplate::class, 'expense_category_stage')->orderBy('sequence');
    }

    /** Stage template ids this category is limited to, or [] for all stages. */
    public function stageIds(): array
    {
        return $this->stages->pluck('id')->all();
    }

    /** A category is usable in a stage when it and its group both allow it. */
    public function availableFor(int $stageTemplateId): bool
    {
        foreach (array_filter([$this, $this->parent]) as $category) {
            $ids = $category->stageIds();
            if ($ids && ! in_array($stageTemplateId, $ids, true)) {
                return false;
            }
        }

        return true;
    }

    /** Effective stage ids (own ∩ group's), or null when usable in every stage. */
    public function effectiveStageIds(): ?array
    {
        $own = $this->stageIds();
        $group = $this->parent?->stageIds() ?? [];

        return match (true) {
            ! $own && ! $group => null,
            ! $own => $group,
            ! $group => $own,
            default => array_values(array_intersect($own, $group)),
        };
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
            ->with(['stages:id', 'children' => fn ($q) => $q->where('is_active', true)->where('name', '!=', MachineService::NOTIONAL_CATEGORY)->with('stages:id')])
            ->orderBy('name')->get()
            ->each(fn (self $group) => $group->children->each->setRelation('parent', $group));
    }
}
