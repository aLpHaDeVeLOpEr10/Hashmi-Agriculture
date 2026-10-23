<?php

namespace App\Models;

use App\Models\Concerns\Audited;
use Illuminate\Database\Eloquent\Model;

class Crop extends Model
{
    use Audited;

    protected $guarded = ['id', 'created_by'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }
}
