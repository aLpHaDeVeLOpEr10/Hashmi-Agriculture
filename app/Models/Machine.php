<?php

namespace App\Models;

use App\Models\Concerns\Audited;
use App\Models\Concerns\BelongsToFarm;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Machine extends Model
{
    use Audited, BelongsToFarm;

    public const TYPES = ['tractor', 'harvester', 'chopper', 'pump', 'trolley', 'implement', 'vehicle'];

    protected $table = 'resources';

    protected $guarded = ['id', 'farm_id', 'created_by'];

    protected function casts(): array
    {
        return ['internal_rate_per_hour' => 'decimal:2', 'is_active' => 'boolean'];
    }

    public function usage(): HasMany
    {
        return $this->hasMany(MachineUsage::class, 'resource_id');
    }
}
