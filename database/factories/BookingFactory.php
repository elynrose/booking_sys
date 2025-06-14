<?php

namespace Database\Factories;

use App\Models\Booking;
use Illuminate\Database\Eloquent\Factories\Factory;

class BookingFactory extends Factory
{
    protected $model = Booking::class;

    public function definition()
    {
        return [
            'user_id' => $this->faker->numberBetween(1, 10),
            'schedule_id' => $this->faker->numberBetween(1, 5),
            'child_id' => $this->faker->numberBetween(1, 15),
            'sessions_remaining' => $this->faker->numberBetween(1, 3),
            'status' => $this->faker->randomElement(['pending', 'confirmed', 'cancelled']),
            'check_in_code' => $this->faker->unique()->randomNumber(6),
            'is_paid' => $this->faker->boolean,
            'payment_method' => $this->faker->randomElement(['zelle', 'stripe']),
            'payment_status' => $this->faker->randomElement(['pending', 'paid', 'refunded']),
        ];
    }
} 