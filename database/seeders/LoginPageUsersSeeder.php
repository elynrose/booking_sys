<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class LoginPageUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // First, ensure we have the basic roles and permissions
        $this->call([
            PermissionSeeder::class,
            RoleSeeder::class,
            AssignPermissionsToAdminSeeder::class,
        ]);

        // Get or create roles
        $adminRole = Role::firstOrCreate(['title' => 'Admin'], [
            'title' => 'Admin',
            'name' => 'admin',
        ]);

        $userRole = Role::firstOrCreate(['title' => 'User'], [
            'title' => 'User',
            'name' => 'user',
        ]);

        $trainerRole = Role::firstOrCreate(['title' => 'Trainer'], [
            'title' => 'Trainer',
            'name' => 'trainer',
        ]);

        // Create Admin User (admin@demo.com)
        $admin = User::firstOrCreate([
            'email' => 'admin@demo.com',
        ], [
            'name' => 'Admin User',
            'email' => 'admin@demo.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'verified' => 1,
            'verified_at' => now(),
            'two_factor_code' => null,
            'two_factor_expires_at' => null,
        ]);

        // Assign admin role if not already assigned
        if (!$admin->hasRole('Admin')) {
            $admin->assignRole('Admin');
        }

        // Create Regular User (user@demo.com)
        $user = User::firstOrCreate([
            'email' => 'user@demo.com',
        ], [
            'name' => 'Regular User',
            'email' => 'user@demo.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'verified' => 1,
            'verified_at' => now(),
            'two_factor_code' => null,
            'two_factor_expires_at' => null,
        ]);

        // Assign user role if not already assigned
        if (!$user->hasRole('User')) {
            $user->assignRole('User');
        }

        // Create Trainer User (trainer@demo.com)
        $trainer = User::firstOrCreate([
            'email' => 'trainer@demo.com',
        ], [
            'name' => 'Trainer User',
            'email' => 'trainer@demo.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'verified' => 1,
            'verified_at' => now(),
            'two_factor_code' => null,
            'two_factor_expires_at' => null,
        ]);

        // Assign trainer role if not already assigned
        if (!$trainer->hasRole('Trainer')) {
            $trainer->assignRole('Trainer');
        }

        // Create Trainer model for the trainer user
        $trainerModel = \App\Models\Trainer::firstOrCreate([
            'user_id' => $trainer->id,
        ], [
            'user_id' => $trainer->id,
            'bio' => 'Experienced fitness trainer with expertise in strength training and cardio.',
            'payment_method' => 'paypal',
            'payment_details' => 'trainer@demo.com',
            'is_active' => true,
        ]);

        $this->command->info('Login page users created successfully!');
        $this->command->info('Admin: admin@demo.com / password');
        $this->command->info('Regular User: user@demo.com / password');
        $this->command->info('Trainer: trainer@demo.com / password');
        $this->command->info('All accounts have verified emails and proper roles assigned.');
    }
} 