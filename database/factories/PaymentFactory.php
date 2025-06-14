<?php

namespace Database\Factories;

use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition()
    {
        return [
            'user_id' => $this->faker->numberBetween(1, 10),
            'schedule_id' => $this->faker->numberBetween(1, 5),
            'booking_id' => $this->faker->numberBetween(1, 20),
            'amount' => $this->faker->randomFloat(2, 10, 100),
            'status' => $this->faker->randomElement(['pending', 'paid', 'refunded']),
            'paid_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'description' => $this->faker->sentence,
        ];
    }
} 