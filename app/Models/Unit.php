<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Unit extends Model
{
    public const KINDS = ['weight', 'volume', 'time', 'area', 'count', 'trip'];

    protected $primaryKey = 'code';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $guarded = ['created_by'];

    protected static function booted(): void
    {
        static::creating(fn (self $unit) => $unit->created_by ??= Auth::id());
    }
}
