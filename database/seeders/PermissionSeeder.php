<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tables = [
            'booking',
            'payment',
            'trainer',
            'category',
            'schedule',
            'user',
            'home',
            'profile',
            'child',
            'user_alert',
            'checkin'
        ];

        $permissions = [
            'access',
            'create',
            'edit',
            'delete',
            'show'
        ];

        foreach ($tables as $table) {
            foreach ($permissions as $permission) {
                Permission::firstOrCreate([
                    'title' => $table . '_' . $permission
                ]);
            }
        }
    }
}
