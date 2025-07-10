<?php

namespace Database\Factories;

use App\Models\Schedule;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;
use Illuminate\Support\Str;

class ScheduleFactory extends Factory
{
    protected $model = Schedule::class;

    public function definition()
    {
        $classTypes = [
            'Beginner Gymnastics' => 'Perfect for kids new to gymnastics. Learn basic tumbling, balance beam, and vault techniques.',
            'Advanced Gymnastics' => 'For experienced gymnasts. Focus on complex routines, flips, and advanced techniques.',
            'Swimming Lessons' => 'Learn essential swimming strokes and water safety skills.',
            'Karate for Kids' => 'Build confidence and discipline through martial arts training.',
            'Ballet Basics' => 'Introduction to classical ballet techniques and dance fundamentals.',
            'Soccer Training' => 'Develop soccer skills, teamwork, and sportsmanship.',
            'Basketball Skills' => 'Improve shooting, dribbling, and court awareness.',
            'Tennis Fundamentals' => 'Learn proper grip, stance, and basic tennis strokes.',
            'Cheerleading' => 'Master cheers, chants, and basic tumbling moves.',
            'Hip-Hop Dance' => 'High-energy dance class with modern hip-hop moves.',
            'Tae Kwon Do' => 'Traditional Korean martial arts training for all ages.',
            'Swimming Advanced' => 'Advanced stroke techniques and competitive swimming skills.',
            'Gymnastics Competition' => 'Prepare for gymnastics competitions with advanced routines.',
            'Dance Performance' => 'Choreography and performance skills for dance recitals.',
            'Soccer League' => 'Team-based soccer training and league play.',
        ];

        $selectedClass = $this->faker->randomElement(array_keys($classTypes));
        $description = $classTypes[$selectedClass];

        // Generate realistic class times (typically 1-2 hour sessions)
        $startTime = $this->faker->dateTimeBetween('+1 day', '+2 weeks');
        $endTime = clone $startTime;
        $endTime->modify('+' . $this->faker->randomElement([60, 90, 120]) . ' minutes');

        // Extract date and time components
        $startDate = Carbon::parse($startTime)->format('Y-m-d');
        $endDate = Carbon::parse($endTime)->format('Y-m-d');

        return [
            'title' => $selectedClass,
            'slug' => Str::slug($selectedClass) . '-' . $this->faker->unique()->numberBetween(1000, 9999),
            'type' => $this->faker->randomElement(['group', 'private']),
            'description' => $description,
            'photo' => null, // Will be set by media library if needed
            'start_date' => $startDate,
            'end_date' => $endDate,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'max_participants' => $this->faker->randomElement([8, 10, 12, 15, 20]),
            'current_participants' => $this->faker->numberBetween(0, 8),
            'price' => $this->faker->randomElement([25, 30, 35, 40, 45, 50]),
            'trainer_id' => \App\Models\Trainer::inRandomOrder()->first()?->id ?? null,
            'status' => $this->faker->randomElement(['active', 'active', 'active', 'inactive']), // Mostly active
            'is_featured' => $this->faker->boolean(20), // 20% chance of being featured
            'category_id' => Category::inRandomOrder()->first()->id ?? 1,
        ];
    }
} 