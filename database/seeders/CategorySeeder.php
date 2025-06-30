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
                'name' => 'Gymnastics',
                'slug' => 'gymnastics',
                'description' => 'Classical gymnastics training for all skill levels',
            ],
            [
                'name' => 'Swimming',
                'slug' => 'swimming',
                'description' => 'Swimming lessons and water safety training',
            ],
            [
                'name' => 'Martial Arts',
                'slug' => 'martial-arts',
                'description' => 'Karate, Taekwondo, and self-defense training',
            ],
            [
                'name' => 'Dance',
                'slug' => 'dance',
                'description' => 'Ballet, Jazz, Hip-hop, and contemporary dance',
            ],
            [
                'name' => 'Soccer',
                'slug' => 'soccer',
                'description' => 'Youth soccer training and team development',
            ],
            [
                'name' => 'Basketball',
                'slug' => 'basketball',
                'description' => 'Basketball skills, drills, and team play',
            ],
            [
                'name' => 'Tennis',
                'slug' => 'tennis',
                'description' => 'Tennis instruction for beginners and intermediate players',
            ],
            [
                'name' => 'Cheerleading',
                'slug' => 'cheerleading',
                'description' => 'Cheerleading, tumbling, and spirit training',
            ],
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate([
                'slug' => $category['slug'],
            ], $category);
        }
    }
}
