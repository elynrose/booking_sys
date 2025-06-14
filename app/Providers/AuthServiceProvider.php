<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Register permission gates
        Gate::before(function ($user, $ability) {
            if ($user->hasRole('Admin')) {
                return true;
            }
        });

        // Register all permissions as gates
        foreach (config('permissions.permissions') as $permission) {
            Gate::define($permission, function ($user) use ($permission) {
                // Check if user has the role that corresponds to this permission
                $role = str_replace('_access', '', $permission);
                $role = str_replace('_create', '', $role);
                $role = str_replace('_edit', '', $role);
                $role = str_replace('_show', '', $role);
                $role = str_replace('_delete', '', $role);
                $role = ucfirst($role);
                
                return $user->hasRole($role);
            });
        }
    }
}
