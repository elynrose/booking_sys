<?php

namespace Database\Factories;

use App\Models\Payment;
use App\Models\User;
use App\Models\Schedule;
use App\Models\Booking;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition()
    {
        $paymentDescriptions = [
            'Gymnastics class payment',
            'Swimming lesson fee',
            'Martial arts training payment',
            'Dance class registration',
            'Soccer training fee',
            'Basketball skills payment',
            'Tennis lesson payment',
            'Cheerleading class fee',
            'Monthly membership payment',
            'Class package payment',
            'Registration fee',
            'Equipment fee',
            'Competition entry fee',
            'Private lesson payment',
            'Summer camp payment',
        ];

        $status = $this->faker->randomElement(['pending', 'paid', 'refunded']);
        $paidAt = $status === 'paid' ? $this->faker->dateTimeBetween('-1 month', 'now') : null;

        return [
            'user_id' => User::inRandomOrder()->first()->id ?? 1,
            'schedule_id' => Schedule::inRandomOrder()->first()->id ?? 1,
            'booking_id' => Booking::inRandomOrder()->first()->id ?? 1,
            'amount' => $this->faker->randomFloat(2, 25, 150),
            'currency' => 'USD',
            'payment_method' => $this->faker->randomElement(['stripe', 'zelle', 'cash', 'paypal']),
            'description' => $this->faker->randomElement($paymentDescriptions),
            'status' => $status,
            'payment_date' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'paid_at' => $paidAt,
        ];
    }
} 