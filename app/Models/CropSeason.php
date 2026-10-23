<?php

namespace App\Models;

use App\Models\Concerns\Audited;
use App\Models\Concerns\BelongsToFarm;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CropSeason extends Model
{
    use Audited, BelongsToFarm;

    public const STATUSES = ['planned', 'active', 'harvested', 'ensiled', 'closed'];

    protected $guarded = ['id', 'farm_id', 'created_by'];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'expected_end_date' => 'date',
            'closed_at' => 'date',
            'total_area_acres' => 'decimal:2',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'code';
    }

    public function crop(): BelongsTo
    {
        return $this->belongsTo(Crop::class);
    }

    public function fields(): BelongsToMany
    {
        return $this->belongsToMany(Field::class, 'season_fields', 'season_id')->withPivot('area_acres')->withTimestamps();
    }

    public function stages(): HasMany
    {
        return $this->hasMany(SeasonStage::class, 'season_id')->orderBy(
            StageTemplate::select('sequence')->whereColumn('stage_templates.id', 'season_stages.stage_template_id')
        );
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class, 'season_id');
    }

    public function harvests(): HasMany
    {
        return $this->hasMany(HarvestRecord::class, 'season_id')->orderBy('harvest_date');
    }

    public function batches(): HasMany
    {
        return $this->hasMany(SilageBatch::class, 'season_id')->orderBy('fill_start');
    }

    public function summary(): HasOne
    {
        return $this->hasOne(SeasonSummary::class, 'season_id');
    }

    public function isClosed(): bool
    {
        return $this->status === 'closed';
    }

    public static function statusLabel(string $status): string
    {
        return __(ucfirst($status));
    }
}
