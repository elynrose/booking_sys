<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

class AssignPermissionsToAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the admin, trainer, and user roles
        $adminRole = Role::where('name', 'Admin')->first();
        $trainerRole = Role::where('name', 'Trainer')->first();
        $userRole = Role::where('name', 'User')->first();

        if (!$adminRole) {
            $this->command->error('Admin role not found!');
            return;
        }
        if (!$trainerRole) {
            $this->command->error('Trainer role not found!');
            return;
        }
        if (!$userRole) {
            $this->command->error('User role not found!');
            return;
        }

        // Get all permissions
        $permissions = Permission::all();

        // Assign all permissions to admin role
        $adminRole->permissions()->sync($permissions->pluck('id'));

        // Assign permissions to trainer role (all except user management and site settings)
        $trainerPermissions = $permissions->filter(function($perm) {
            return !str_starts_with($perm->name, 'user_') && !str_starts_with($perm->name, 'site_settings_');
        });
        $trainerRole->permissions()->sync($trainerPermissions->pluck('id'));

        // Assign permissions to user role (only booking, schedule, payment, profile, child, checkin, home, user_alert)
        $userPermissions = $permissions->filter(function($perm) {
            return preg_match('/^(booking|schedule|payment|profile|child|checkin|home|user_alert)_/', $perm->name);
        });
        $userRole->permissions()->sync($userPermissions->pluck('id'));

        $this->command->info('Permissions have been assigned to admin, trainer, and user roles.');
    }
}
