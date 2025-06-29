<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Role;

class AssignRoles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:assign-roles {--email= : Specific user email to assign role to} {--role= : Specific role to assign}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Assign roles to users';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->option('email');
        $roleName = $this->option('role');

        // Get roles
        $adminRole = Role::where('title', 'Admin')->first();
        $trainerRole = Role::where('title', 'Trainer')->first();
        $userRole = Role::where('title', 'User')->first();

        if (!$adminRole || !$trainerRole || !$userRole) {
            $this->error('Required roles not found! Please run RoleSeeder first.');
            return 1;
        }

        if ($email && $roleName) {
            // Assign specific role to specific user
            $user = User::where('email', $email)->first();
            $role = Role::where('title', $roleName)->first();

            if (!$user) {
                $this->error("User with email {$email} not found!");
                return 1;
            }

            if (!$role) {
                $this->error("Role {$roleName} not found!");
                return 1;
            }

            $user->roles()->detach();
            $user->roles()->attach($role);
            $this->info("Role {$roleName} assigned to {$user->name} ({$user->email})");
            return 0;
        }

        // Assign roles to all users
        $users = User::all();
        
        if ($users->count() === 0) {
            $this->warn('No users found in the database.');
            return 0;
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
                $this->info("Admin role assigned to: {$user->name} ({$user->email})");
            } elseif ($user->email === 'trainer@example.com' || $user->email === 'trainer@greenstreet.com') {
                $user->roles()->attach($trainerRole);
                $trainerCount++;
                $this->info("Trainer role assigned to: {$user->name} ({$user->email})");
            } else {
                $user->roles()->attach($userRole);
                $userCount++;
                $this->info("User role assigned to: {$user->name} ({$user->email})");
            }
        }

        $this->info("Role assignment completed!");
        $this->info("Admin users: {$adminCount}");
        $this->info("Trainer users: {$trainerCount}");
        $this->info("Regular users: {$userCount}");

        return 0;
    }
} 