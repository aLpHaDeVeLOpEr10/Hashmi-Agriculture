<?php

namespace App\Models;

use App\Models\Concerns\Audited;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Audited, Notifiable;

    public const ROLES = ['owner', 'manager', 'data_entry', 'viewer'];

    protected $guarded = ['id', 'created_by', 'remember_token'];

    protected $hidden = ['password', 'remember_token'];

    protected $attributes = [
        'is_active' => true,
        'locale' => 'en',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function farm(): BelongsTo
    {
        return $this->belongsTo(Farm::class);
    }

    public function hasRole(string ...$roles): bool
    {
        return in_array($this->role, $roles, true);
    }

    public static function roleLabel(string $role): string
    {
        return __(match ($role) {
            'owner' => 'Owner',
            'manager' => 'Manager',
            'data_entry' => 'Data entry',
            default => 'Viewer',
        });
    }
}
