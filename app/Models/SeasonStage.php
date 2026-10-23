<?php

namespace App\Models;

use App\Models\Concerns\Audited;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SeasonStage extends Model
{
    use Audited;

    public const STATUSES = ['not_started', 'in_progress', 'completed'];

    protected $guarded = ['id', 'created_by'];

    protected function casts(): array
    {
        return ['start_date' => 'date', 'end_date' => 'date'];
    }

    public function season(): BelongsTo
    {
        return $this->belongsTo(CropSeason::class, 'season_id');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(StageTemplate::class, 'stage_template_id');
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function totals(): HasOne
    {
        return $this->hasOne(StageTotal::class, 'season_stage_id');
    }

    public function name(): string
    {
        return __($this->template->name);
    }

    public static function statusLabel(string $status): string
    {
        return __(match ($status) {
            'not_started' => 'Not started',
            'in_progress' => 'In progress',
            default => 'Completed',
        });
    }
}
