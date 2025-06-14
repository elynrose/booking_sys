<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Individual',
                'slug' => 'individual',
                'description' => 'One-on-one training sessions'
            ],
            [
                'name' => 'Group',
                'slug' => 'group',
                'description' => 'Group training sessions'
            ]
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
