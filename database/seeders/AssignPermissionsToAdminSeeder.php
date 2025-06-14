<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class AssignPermissionsToAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the admin, trainer, and user roles
        $adminRole = Role::where('title', 'Admin')->first();
        $trainerRole = Role::where('title', 'Trainer')->first();
        $userRole = Role::where('title', 'User')->first();

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

        // Ensure the guard_name is 'web'
        $adminRole->guard_name = 'web';
        $adminRole->save();
        $trainerRole->guard_name = 'web';
        $trainerRole->save();
        $userRole->guard_name = 'web';
        $userRole->save();

        // Get all permissions
        $permissions = Permission::all();

        // Assign all permissions to admin role
        $adminRole->syncPermissions($permissions);

        // Assign permissions to trainer role (all except user management)
        $trainerPermissions = $permissions->filter(function($perm) {
            return !str_starts_with($perm->name, 'user_');
        });
        $trainerRole->syncPermissions($trainerPermissions);

        // Assign permissions to user role (only booking, schedule, payment, profile, child, checkin, home, user_alert)
        $userPermissions = $permissions->filter(function($perm) {
            return preg_match('/^(booking|schedule|payment|profile|child|checkin|home|user_alert)_/', $perm->name);
        });
        $userRole->syncPermissions($userPermissions);

        $this->command->info('Permissions have been assigned to admin, trainer, and user roles.');
    }
}
