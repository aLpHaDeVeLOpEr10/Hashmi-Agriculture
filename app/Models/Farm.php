<?php

namespace App\Models;

use App\Models\Concerns\Audited;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Farm extends Model
{
    use Audited;

    protected $guarded = ['id', 'created_by'];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function seasons(): HasMany
    {
        return $this->hasMany(CropSeason::class);
    }
}
