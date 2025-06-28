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
        // Get admin role
        $adminRole = Role::where('title', 'Admin')->first();

        // Create admin user if it doesn't exist
        $admin = User::firstOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'verified' => 1,
                'verified_at' => now(),
            ]
        );

        // Assign admin role if not already assigned
        if ($adminRole && !$admin->roles()->where('roles.id', $adminRole->id)->exists()) {
            $admin->roles()->attach($adminRole);
        }
    }
}
