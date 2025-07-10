<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class DemoUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get roles
        $adminRole = Role::where('title', 'Admin')->first();
        $userRole = Role::where('title', 'User')->first();
        $trainerRole = Role::where('title', 'Trainer')->first();

        // Create Admin User
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'verified' => 1,
            'verified_at' => now(),
        ]);

        if ($adminRole) {
            $admin->assignRole('Admin');
        }

        // Create Demo Parent User
        $parent = User::create([
            'name' => 'Demo Parent',
            'email' => 'oking@example.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'verified' => 1,
            'verified_at' => now(),
        ]);

        if ($userRole) {
            $parent->assignRole('User');
        }

        // Create Trainer User
        $trainer = User::create([
            'name' => 'Demo Trainer',
            'email' => 'trainer@example.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'verified' => 1,
            'verified_at' => now(),
        ]);

        if ($trainerRole) {
            $trainer->assignRole('Trainer');
        }

        $this->command->info('Demo users created successfully!');
        $this->command->info('Admin: admin@example.com / password');
        $this->command->info('Parent: oking@example.com / password');
        $this->command->info('Trainer: trainer@example.com / password');
    }
} 