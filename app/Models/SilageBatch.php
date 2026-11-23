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

    /** Cubic feet to cubic metres, as used by the workbook's bunker estimator. */
    public const FT3_TO_M3 = 0.0283168;

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
            'length_ft' => 'decimal:2',
            'width_ft' => 'decimal:2',
            'height_ft' => 'decimal:2',
            'density_kg_m3' => 'decimal:2',
        ];
    }

    /** Bunker volume in m³ from length × width × average settled height (ft). */
    public static function volumeM3(float $lengthFt, float $widthFt, float $heightFt): float
    {
        return $lengthFt * $widthFt * $heightFt * self::FT3_TO_M3;
    }

    /** Estimated fresh silage (kg) = bunker volume (m³) × packing density (kg/m³). */
    public static function estimateKg(float $lengthFt, float $widthFt, float $heightFt, float $densityKgM3): float
    {
        return round(self::volumeM3($lengthFt, $widthFt, $heightFt) * $densityKgM3, 3);
    }

    public function isMeasured(): bool
    {
        return $this->method === 'pit_volume' && $this->length_ft !== null;
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
