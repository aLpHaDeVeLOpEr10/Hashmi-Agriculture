<?php

namespace App\Models;

use App\Models\Concerns\Audited;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MachineUsage extends Model
{
    use Audited;

    protected $table = 'resource_usage';

    protected $guarded = ['id', 'created_by'];

    protected function casts(): array
    {
        return ['usage_date' => 'date', 'hours' => 'decimal:2', 'acres' => 'decimal:2', 'fuel_litres' => 'decimal:2'];
    }

    public function machine(): BelongsTo
    {
        return $this->belongsTo(Machine::class, 'resource_id');
    }

    public function stage(): BelongsTo
    {
        return $this->belongsTo(SeasonStage::class, 'season_stage_id');
    }
}
