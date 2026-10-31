<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    /**
     * role and is_active are intentionally not mass assignable;
     * set them explicitly after an authorization check.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'google_id',
        'avatar',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * users.role stores the role name (e.g. "admin", "cashier").
     */
    public function roleModel()
    {
        return $this->belongsTo(Role::class, 'role', 'name');
    }

    public function isSuperAdmin(): bool
    {
        return (bool) $this->roleModel?->is_system;
    }

    public function hasPermission(string $permission): bool
    {
        return (bool) $this->roleModel?->hasPermission($permission);
    }

    public function permissions(): array
    {
        return $this->roleModel?->effectivePermissions() ?? [];
    }

    public function roleLabel(): string
    {
        return $this->roleModel?->label ?? ucfirst((string) $this->role);
    }
}
