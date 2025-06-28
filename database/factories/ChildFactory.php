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
        // Generate realistic child names (first names only)
        $childNames = [
            'male' => ['Liam', 'Noah', 'Oliver', 'Elijah', 'William', 'James', 'Benjamin', 'Lucas', 'Henry', 'Alexander', 'Mason', 'Michael', 'Ethan', 'Daniel', 'Jacob', 'Logan', 'Jackson', 'Sebastian', 'Jack', 'Owen'],
            'female' => ['Emma', 'Olivia', 'Ava', 'Isabella', 'Sophia', 'Charlotte', 'Mia', 'Amelia', 'Harper', 'Evelyn', 'Abigail', 'Emily', 'Elizabeth', 'Mila', 'Ella', 'Avery', 'Sofia', 'Camila', 'Aria', 'Scarlett'],
        ];

        $gender = $this->faker->randomElement(['male', 'female']);
        $name = $this->faker->randomElement($childNames[$gender]);
        
        // Generate birth date for children aged 3-17
        $age = $this->faker->numberBetween(3, 17);
        $dateOfBirth = now()->subYears($age)->subDays($this->faker->numberBetween(0, 365));

        return [
            'user_id' => User::factory(),
            'name' => $name,
            'date_of_birth' => $dateOfBirth->toDateString(),
            'gender' => $gender,
            'notes' => $this->faker->optional(0.3)->sentence(), // 30% chance of having notes
        ];
    }
} 