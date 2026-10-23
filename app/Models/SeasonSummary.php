<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeasonSummary extends Model
{
    protected $table = 'v_season_summary';

    protected $primaryKey = 'season_id';

    public $incrementing = false;

    public $timestamps = false;

    protected function casts(): array
    {
        return ['total_area_acres' => 'decimal:2', 'total_cost' => 'decimal:2', 'economic_cost' => 'decimal:2', 'paid' => 'decimal:2', 'outstanding' => 'decimal:2', 'cost_per_acre' => 'decimal:2', 'harvested_kg' => 'decimal:3', 'yield_kg_per_acre' => 'decimal:2', 'cost_per_tonne_harvested' => 'decimal:2', 'silage_fresh_kg' => 'decimal:3', 'silage_usable_kg' => 'decimal:3', 'silage_cost_per_kg' => 'decimal:4', 'silage_cost_per_tonne' => 'decimal:2'];
    }
}
