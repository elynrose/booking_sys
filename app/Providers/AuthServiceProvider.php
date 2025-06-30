<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Log;

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
            Log::info('Gate before check:', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'ability' => $ability
            ]);
            
            // Log user's roles
            $userRoles = $user->roles()->get();
            Log::info('User roles:', [
                'roles' => $userRoles->pluck('name')->toArray()
            ]);
            
            if ($user->hasRole('Admin')) {
                Log::info('Admin access granted');
                return true;
            }
        });

        // Register all permissions as gates
        foreach (config('permissions.permissions') as $permission) {
            Gate::define($permission, function ($user) use ($permission) {
                Log::info('Checking permission:', [
                    'permission' => $permission,
                    'user_id' => $user->id,
                    'user_email' => $user->email
                ]);

                // Get the role name from the permission (e.g., 'booking_access' -> 'Booking')
                $roleName = str_replace(['_access', '_create', '_edit', '_show', '_delete'], '', $permission);
                $roleName = ucfirst($roleName);
                
                Log::info('Looking for role:', [
                    'role_name' => $roleName,
                    'permission' => $permission
                ]);

                // Check if user has the role
                $hasRole = $user->roles()->where('name', $roleName)->exists();
                
                Log::info('Role check result:', [
                    'has_role' => $hasRole,
                    'role_name' => $roleName
                ]);

                return $hasRole;
            });
        }
    }
}
