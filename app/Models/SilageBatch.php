<?php

namespace App\Models;

use App\Models\Concerns\Audited;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SilageBatch extends Model
{
    use Audited;

    public const METHODS = ['weighbridge', 'trolley_count', 'pit_volume'];

    public const STATUSES = ['filling', 'sealed', 'feeding', 'finished'];

    protected $guarded = ['id', 'created_by'];

    protected function casts(): array
    {
        return [
            'fill_start' => 'date',
            'sealed_date' => 'date',
            'opened_date' => 'date',
            'fresh_weight_kg' => 'decimal:3',
            'expected_loss_pct' => 'decimal:2',
            'cost_per_kg' => 'decimal:4',
        ];
    }

    public function season(): BelongsTo
    {
        return $this->belongsTo(CropSeason::class, 'season_id');
    }

    public function storageUnit(): BelongsTo
    {
        return $this->belongsTo(StorageUnit::class);
    }

    public function usableKg(): float
    {
        return round((float) $this->fresh_weight_kg * (100 - (float) $this->expected_loss_pct) / 100, 3);
    }

    public function code(): string
    {
        return 'SB-'.str_pad((string) $this->id, 4, '0', STR_PAD_LEFT);
    }
}
