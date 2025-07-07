<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Permission;

class RecommendationPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            'recommendation_access',
            'recommendation_show',
            'recommendation_delete',
        ];

        foreach ($permissions as $permission) {
            $perm = Permission::where('name', $permission)->first();
            if (!$perm) {
                $perm = new Permission();
                $perm->setRawAttributes([
                    'name' => $permission,
                    'title' => $permission,
                    'guard_name' => 'web',
                ]);
                $perm->save();
            }
        }

        $this->command->info('Recommendation permissions created successfully!');
    }
}
