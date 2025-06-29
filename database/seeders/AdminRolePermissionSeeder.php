<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;

class AdminRolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get roles
        $adminRole = Role::where('title', 'Admin')->first();
        $trainerRole = Role::where('title', 'Trainer')->first();
        $userRole = Role::where('title', 'User')->first();

        if (!$adminRole) {
            $this->command->error('Admin role not found!');
            return;
        }

        // Find admin user by email or create one
        $adminUser = User::where('email', 'admin@example.com')->first();
        
        if ($adminUser) {
            // Remove any existing roles and assign admin role
            $adminUser->roles()->detach();
            $adminUser->roles()->attach($adminRole);
            $this->command->info('Admin role assigned to admin@example.com');
        } else {
            $this->command->warn('Admin user (admin@example.com) not found. Please create admin user first.');
        }

        // Assign roles to other users if they exist
        $trainerUser = User::where('email', 'trainer@example.com')->first();
        if ($trainerUser && $trainerRole) {
            $trainerUser->roles()->detach();
            $trainerUser->roles()->attach($trainerRole);
            $this->command->info('Trainer role assigned to trainer@example.com');
        }

        // Assign user role to all other users who don't have any role
        $usersWithoutRoles = User::whereDoesntHave('roles')->get();
        if ($userRole && $usersWithoutRoles->count() > 0) {
            foreach ($usersWithoutRoles as $user) {
                $user->roles()->attach($userRole);
            }
            $this->command->info("User role assigned to {$usersWithoutRoles->count()} users without roles");
        }

        $this->command->info('Role assignment completed successfully!');
    }
}
