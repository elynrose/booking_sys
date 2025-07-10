<?php

namespace Database\Factories;

use App\Models\TrainerAvailability;
use App\Models\Trainer;
use App\Models\Schedule;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class TrainerAvailabilityFactory extends Factory
{
    protected $model = TrainerAvailability::class;

    public function definition()
    {
        $date = $this->faker->dateTimeBetween('+1 day', '+30 days');
        $startTime = $this->faker->dateTimeBetween('08:00', '16:00');
        $endTime = clone $startTime;
        $endTime->modify('+' . $this->faker->randomElement([60, 90, 120]) . ' minutes');

        return [
            'trainer_id' => Trainer::factory(),
            'schedule_id' => Schedule::factory(),
            'date' => $date->format('Y-m-d'),
            'start_time' => $startTime->format('H:i:s'),
            'end_time' => $endTime->format('H:i:s'),
            'status' => $this->faker->randomElement(['available', 'unavailable', 'booked', 'cancelled']),
            'notes' => $this->faker->optional()->sentence(),
        ];
    }

    /**
     * Available availability
     */
    public function available()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'available',
            ];
        });
    }

    /**
     * Unavailable availability
     */
    public function unavailable()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'unavailable',
            ];
        });
    }

    /**
     * Booked availability
     */
    public function booked()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'booked',
            ];
        });
    }

    /**
     * Cancelled availability
     */
    public function cancelled()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'cancelled',
            ];
        });
    }
} 