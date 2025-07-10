<?php

namespace Database\Factories;

use App\Models\TrainerUnavailability;
use App\Models\Trainer;
use App\Models\Schedule;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class TrainerUnavailabilityFactory extends Factory
{
    protected $model = TrainerUnavailability::class;

    public function definition()
    {
        $date = $this->faker->dateTimeBetween('+1 day', '+30 days');
        $startTime = $this->faker->dateTimeBetween('08:00', '16:00');
        $endTime = clone $startTime;
        $endTime->modify('+' . $this->faker->randomElement([60, 90, 120, 240]) . ' minutes');

        return [
            'trainer_id' => Trainer::factory(),
            'schedule_id' => Schedule::factory(),
            'date' => $date->format('Y-m-d'),
            'start_time' => $startTime->format('Y-m-d H:i:s'),
            'end_time' => $endTime->format('Y-m-d H:i:s'),
            'reason' => $this->faker->randomElement(['personal', 'sick', 'vacation', 'other']),
            'notes' => $this->faker->optional()->sentence(),
        ];
    }

    /**
     * All day unavailability
     */
    public function allDay()
    {
        return $this->state(function (array $attributes) {
            return [
                'start_time' => null,
                'end_time' => null,
            ];
        });
    }

    /**
     * Personal reason
     */
    public function personal()
    {
        return $this->state(function (array $attributes) {
            return [
                'reason' => 'personal',
            ];
        });
    }

    /**
     * Sick leave
     */
    public function sick()
    {
        return $this->state(function (array $attributes) {
            return [
                'reason' => 'sick',
            ];
        });
    }

    /**
     * Vacation
     */
    public function vacation()
    {
        return $this->state(function (array $attributes) {
            return [
                'reason' => 'vacation',
            ];
        });
    }

    /**
     * Other reason
     */
    public function other()
    {
        return $this->state(function (array $attributes) {
            return [
                'reason' => 'other',
            ];
        });
    }
} 