<?php

namespace App\Models;

use App\Models\Concerns\Audited;
use App\Models\Concerns\BelongsToFarm;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StorageUnit extends Model
{
    use Audited, BelongsToFarm;

    public const TYPES = ['pit', 'bunker', 'bag', 'bale_stack'];

    protected $guarded = ['id', 'farm_id', 'created_by'];

    protected function casts(): array
    {
        return ['capacity_tonnes' => 'decimal:2', 'is_active' => 'boolean'];
    }

    public function batches(): HasMany
    {
        return $this->hasMany(SilageBatch::class);
    }

    public static function typeLabel(string $type): string
    {
        return __(match ($type) {
            'pit' => 'Pit',
            'bunker' => 'Bunker',
            'bag' => 'Silage bags',
            default => 'Bale stack',
        });
    }
}
