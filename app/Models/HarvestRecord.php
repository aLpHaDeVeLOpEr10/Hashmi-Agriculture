<?php

namespace App\Models;

use App\Models\Concerns\Audited;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HarvestRecord extends Model
{
    use Audited;

    public const METHODS = ['weighbridge', 'trolley_count', 'estimate'];

    protected $guarded = ['id', 'created_by'];

    protected function casts(): array
    {
        return [
            'harvest_date' => 'date',
            'quantity_kg' => 'decimal:3',
            'avg_trolley_kg' => 'decimal:2',
            'moisture_pct' => 'decimal:2',
        ];
    }

    public function season(): BelongsTo
    {
        return $this->belongsTo(CropSeason::class, 'season_id');
    }

    public function field(): BelongsTo
    {
        return $this->belongsTo(Field::class);
    }

    public static function methodLabel(string $method): string
    {
        return __(match ($method) {
            'weighbridge' => 'Weighbridge slip',
            'trolley_count' => 'Trolley count',
            'pit_volume' => 'Pit volume',
            default => 'Estimate',
        });
    }
}
