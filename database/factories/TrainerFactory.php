<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Trainer>
 */
class TrainerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'profile_picture' => $this->faker->imageUrl(300, 300, 'people'),
            'bio' => $this->faker->paragraph(),
            'payment_method' => $this->faker->randomElement(['stripe', 'paypal', 'zelle', 'cash']),
            'payment_details' => json_encode([
                'account_id' => $this->faker->uuid(),
                'email' => $this->faker->email(),
            ]),
            'is_active' => true,
            'is_available_by_default' => true,
            'default_start_time' => '08:00:00',
            'default_end_time' => '18:00:00',
            'default_available_days' => [0, 1, 2, 3, 4, 5, 6], // All days
        ];
    }

    /**
     * Indicate that the trainer is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the trainer is not available by default.
     */
    public function notAvailableByDefault(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_available_by_default' => false,
        ]);
    }

    /**
     * Set specific available days.
     */
    public function availableDays(array $days): static
    {
        return $this->state(fn (array $attributes) => [
            'default_available_days' => $days,
        ]);
    }

    /**
     * Set specific working hours.
     */
    public function workingHours(string $startTime, string $endTime): static
    {
        return $this->state(fn (array $attributes) => [
            'default_start_time' => $startTime,
            'default_end_time' => $endTime,
        ]);
    }
}
