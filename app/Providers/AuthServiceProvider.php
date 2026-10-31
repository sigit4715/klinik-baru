<?php

namespace App\Providers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        // Menu permissions from config/permissions.php, granted per role in Manajemen Role.
        // The system admin role gets everything. Abilities not in the config fall through
        // to normal Gate/policy checks.
        $permissionKeys = array_flip(Role::allPermissionKeys());

        Gate::before(function (User $user, string $ability) use ($permissionKeys) {
            if (! isset($permissionKeys[$ability])) {
                return null;
            }

            return $user->hasPermission($ability);
        });
    }
}
