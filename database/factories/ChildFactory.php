<?php

namespace Database\Factories;

use App\Models\Child;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ChildFactory extends Factory
{
    protected $model = Child::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'name' => $this->faker->name,
            'age' => $this->faker->numberBetween(1, 18),
            'gender' => $this->faker->randomElement(['male', 'female', 'other']),
        ];
    }
} 