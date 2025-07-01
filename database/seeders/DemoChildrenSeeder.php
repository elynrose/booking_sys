<?php

namespace Database\Seeders;

use App\Models\Child;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoChildrenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the demo parent user
        $parent = User::where('email', 'parent@example.com')->first();
        
        if ($parent) {
            // Create demo children for the parent
            $children = [
                [
                    'name' => 'Emma Johnson',
                    'date_of_birth' => now()->subYears(8),
                    'gender' => 'female',
                    'user_id' => $parent->id,
                ],
                [
                    'name' => 'Lucas Smith',
                    'date_of_birth' => now()->subYears(10),
                    'gender' => 'male',
                    'user_id' => $parent->id,
                ],
            ];

            foreach ($children as $childData) {
                Child::firstOrCreate(
                    ['name' => $childData['name'], 'user_id' => $childData['user_id']],
                    $childData
                );
            }
        }
    }
}
