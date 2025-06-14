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
                'title' => 'Admin',
                'slug' => 'admin',
            ],
            [
                'title' => 'User',
                'slug' => 'user',
            ],
            [
                'title' => 'Trainer',
                'slug' => 'trainer',
            ],
            [
                'title' => 'Member',
                'slug' => 'member',
            ],
        ];

        foreach ($roles as $role) {
            Role::create($role);
        }
    }
}
