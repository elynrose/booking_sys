<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => 'Admin',
                'title' => 'Admin',
                'guard_name' => 'web',
                'slug' => 'admin',
            ],
            [
                'name' => 'User',
                'title' => 'User',
                'guard_name' => 'web',
                'slug' => 'user',
            ],
            [
                'name' => 'Trainer',
                'title' => 'Trainer',
                'guard_name' => 'web',
                'slug' => 'trainer',
            ],
            [
                'name' => 'Member',
                'title' => 'Member',
                'guard_name' => 'web',
                'slug' => 'member',
            ],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate([
                'name' => $role['name'],
                'guard_name' => $role['guard_name'],
            ], $role);
        }
    }
}
