<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StageTotal extends Model
{
    protected $table = 'v_stage_totals';

    protected $primaryKey = 'season_stage_id';

    public $incrementing = false;

    public $timestamps = false;

    protected function casts(): array
    {
        return ['total' => 'decimal:2', 'notional_total' => 'decimal:2', 'paid' => 'decimal:2', 'balance' => 'decimal:2', 'line_count' => 'integer'];
    }
}
