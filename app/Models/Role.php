<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use Auditable;

    public const ADMIN = 'admin';

    protected $fillable = ['name', 'label', 'description', 'permissions'];

    protected $casts = [
        'permissions' => 'array',
        'is_system' => 'boolean',
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'role', 'name');
    }

    /**
     * All permission keys defined in config/permissions.php.
     */
    public static function allPermissionKeys(): array
    {
        return collect(config('permissions'))->flatMap(fn ($group) => array_keys($group))->values()->all();
    }

    /**
     * Effective permissions: the system role always gets everything,
     * other roles only keep keys that still exist in the config.
     */
    public function effectivePermissions(): array
    {
        if ($this->is_system) {
            return self::allPermissionKeys();
        }

        return array_values(array_intersect($this->permissions ?? [], self::allPermissionKeys()));
    }

    public function hasPermission(string $permission): bool
    {
        return $this->is_system || in_array($permission, $this->permissions ?? [], true);
    }
}
