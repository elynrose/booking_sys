<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\User;
use App\Models\Schedule;
use App\Models\Child;
use Illuminate\Database\Eloquent\Factories\Factory;

class BookingFactory extends Factory
{
    protected $model = Booking::class;

    public function definition()
    {
        $statuses = ['pending', 'confirmed', 'cancelled', 'completed'];
        $status = $this->faker->randomElement($statuses);
        
        // Generate realistic check-in codes (alphanumeric)
        $checkInCode = strtoupper($this->faker->bothify('??##??'));
        
        return [
            'user_id' => User::inRandomOrder()->first()->id ?? 1,
            'schedule_id' => Schedule::inRandomOrder()->first()->id ?? 1,
            'child_id' => Child::inRandomOrder()->first()->id ?? 1,
            'sessions_remaining' => $this->faker->numberBetween(1, 8),
            'status' => $status,
            'check_in_code' => $checkInCode,
            'is_paid' => $this->faker->boolean(80), // 80% chance of being paid
            'total_cost' => $this->faker->randomFloat(2, 25, 150),
            'notes' => $this->faker->optional(0.4)->sentence(), // 40% chance of having notes
        ];
    }
} 