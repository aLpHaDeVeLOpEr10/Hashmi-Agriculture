<?php

namespace App\Models\Concerns;

use App\Models\Farm;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

trait BelongsToFarm
{
    public static function bootBelongsToFarm(): void
    {
        static::addGlobalScope('farm', function (Builder $builder) {
            if ($farmId = Auth::user()?->farm_id) {
                $builder->where($builder->getModel()->qualifyColumn('farm_id'), $farmId);
            }
        });

        static::creating(function ($model) {
            $model->farm_id ??= Auth::user()?->farm_id;
        });
    }

    public function farm(): BelongsTo
    {
        return $this->belongsTo(Farm::class);
    }
}
