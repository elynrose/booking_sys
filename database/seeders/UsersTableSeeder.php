<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    public function run()
    {
        // Get roles
        $adminRole = Role::where('title', 'Admin')->first();
        $userRole = Role::where('title', 'User')->first();
        $trainerRole = Role::where('title', 'Trainer')->first();

        // Create admin user
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Demo Admin',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'verified' => 1,
                'verified_at' => now(),
            ]
        );

        // Create parent user
        $parent = User::firstOrCreate(
            ['email' => 'parent@example.com'],
            [
                'name' => 'Demo Parent',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'verified' => 1,
                'verified_at' => now(),
            ]
        );

        // Create trainer user
        $trainer = User::firstOrCreate(
            ['email' => 'trainer@example.com'],
            [
                'name' => 'Demo Trainer',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'verified' => 1,
                'verified_at' => now(),
            ]
        );

        // Assign roles
        if ($adminRole && !$admin->hasRole('Admin')) {
            $admin->assignRole('Admin');
        }

        if ($userRole && !$parent->hasRole('User')) {
            $parent->assignRole('User');
        }

        if ($trainerRole && !$trainer->hasRole('Trainer')) {
            $trainer->assignRole('Trainer');
        }

        // Also create the old admin account for backward compatibility
        $oldAdmin = User::firstOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'verified' => 1,
                'verified_at' => now(),
            ]
        );

        if ($adminRole && !$oldAdmin->hasRole('Admin')) {
            $oldAdmin->assignRole('Admin');
        }
    }
}
