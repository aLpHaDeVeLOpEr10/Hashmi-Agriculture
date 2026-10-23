<?php

namespace App\Models;

use App\Models\Concerns\Audited;
use App\Models\Concerns\BelongsToFarm;
use Illuminate\Database\Eloquent\Model;

class Field extends Model
{
    use Audited, BelongsToFarm;

    protected $guarded = ['id', 'farm_id', 'created_by'];

    protected function casts(): array
    {
        return ['area_acres' => 'decimal:2', 'is_active' => 'boolean'];
    }
}
