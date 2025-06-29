<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;

class RoleAssignmentSeeder extends Seeder
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

        if (!$adminRole || !$trainerRole || !$userRole) {
            $this->command->error('Required roles not found! Please run RoleSeeder first.');
            return;
        }

        // Get all users
        $users = User::all();
        
        if ($users->count() === 0) {
            $this->command->warn('No users found in the database.');
            return;
        }

        $adminCount = 0;
        $trainerCount = 0;
        $userCount = 0;

        foreach ($users as $user) {
            // Remove any existing roles
            $user->roles()->detach();
            
            // Assign role based on email or user ID
            if ($user->email === 'admin@example.com' || $user->id === 1) {
                $user->roles()->attach($adminRole);
                $adminCount++;
                $this->command->info("Admin role assigned to: {$user->name} ({$user->email})");
            } elseif ($user->email === 'trainer@example.com' || $user->email === 'trainer@greenstreet.com') {
                $user->roles()->attach($trainerRole);
                $trainerCount++;
                $this->command->info("Trainer role assigned to: {$user->name} ({$user->email})");
            } else {
                $user->roles()->attach($userRole);
                $userCount++;
                $this->command->info("User role assigned to: {$user->name} ({$user->email})");
            }
        }

        $this->command->info("Role assignment completed!");
        $this->command->info("Admin users: {$adminCount}");
        $this->command->info("Trainer users: {$trainerCount}");
        $this->command->info("Regular users: {$userCount}");
    }
} 