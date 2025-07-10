<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition()
    {
        $categories = [
            'Gymnastics' => 'Gymnastics classes for all skill levels',
            'Swimming' => 'Swimming lessons and water safety',
            'Martial Arts' => 'Karate, Tae Kwon Do, and other martial arts',
            'Dance' => 'Ballet, Hip-Hop, and other dance styles',
            'Soccer' => 'Soccer training and team sports',
            'Basketball' => 'Basketball skills and team play',
            'Tennis' => 'Tennis lessons and court skills',
            'Cheerleading' => 'Cheerleading and tumbling',
            'Fitness' => 'General fitness and conditioning',
            'Yoga' => 'Yoga and flexibility training',
        ];

        $selectedCategory = $this->faker->randomElement(array_keys($categories));
        $description = $categories[$selectedCategory];

        return [
            'name' => $selectedCategory,
            'slug' => Str::slug($selectedCategory),
            'description' => $description,
            'is_active' => $this->faker->boolean(80), // 80% chance of being active
        ];
    }

    /**
     * Active category
     */
    public function active()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_active' => true,
            ];
        });
    }

    /**
     * Inactive category
     */
    public function inactive()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_active' => false,
            ];
        });
    }
} 