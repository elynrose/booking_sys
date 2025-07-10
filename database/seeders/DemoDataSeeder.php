<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\Schedule;
use App\Models\Waitlist;
use App\Models\User;
use App\Models\Child;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Faker\Factory as Faker;

class DemoDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        // Get roles
        $trainerRole = Role::where('title', 'Trainer')->first();
        $userRole = Role::where('title', 'User')->first();
        $adminRole = Role::where('title', 'Admin')->first();

        // Use existing users if available
        $trainer = User::whereHas('roles', function($query) use ($trainerRole) {
            $query->where('roles.id', $trainerRole->id);
        })->first();

        $parents = User::whereHas('roles', function($query) use ($userRole) {
            $query->where('roles.id', $userRole->id);
        })->take(2)->get();

        $parent1 = $parents->first();
        $parent2 = $parents->skip(1)->first();

        // Ensure a trainer exists
        if (!$trainer) {
            $trainer = User::create([
                'name' => $faker->name(),
                'email' => 'demo.trainer@example.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]);
        }

        if (!$trainer->hasRole('Trainer')) {
            $trainer->assignRole('Trainer');
        }

        // Create schedules
        $schedule1 = Schedule::create([
            'title' => 'Beginner Gymnastics',
            'description' => 'Perfect for kids new to gymnastics. Learn basic tumbling, balance beam, and vault techniques.',
            'start_time' => now()->addDays(1)->setHour(9)->setMinute(0),
            'end_time' => now()->addDays(1)->setHour(10)->setMinute(0),
            'max_participants' => 12,
            'current_participants' => 0,
            'price' => 35.00,
            'trainer_id' => $trainer ? $trainer->id : null,
            'status' => 'active',
        ]);

        $schedule2 = Schedule::create([
            'title' => 'Swimming Lessons',
            'description' => 'Learn essential swimming strokes and water safety skills.',
            'start_time' => now()->addDays(2)->setHour(14)->setMinute(0),
            'end_time' => now()->addDays(2)->setHour(15)->setMinute(0),
            'max_participants' => 8,
            'current_participants' => 0,
            'price' => 40.00,
            'trainer_id' => $trainer ? $trainer->id : null,
            'status' => 'active',
        ]);

        // Create children first
        $child1 = null;
        $child2 = null;
        
        if ($parent1) {
            $child1 = Child::create([
                'user_id' => $parent1->id,
                'name' => $faker->randomElement(['Emma', 'Liam', 'Olivia', 'Noah']),
                'date_of_birth' => now()->subYears(8)->toDateString(),
                'gender' => 'male',
            ]);
        }
        
        if ($parent2) {
            $child2 = Child::create([
                'user_id' => $parent2->id,
                'name' => $faker->randomElement(['Sophia', 'William', 'Ava', 'James']),
                'date_of_birth' => now()->subYears(7)->toDateString(),
                'gender' => 'female',
            ]);
        }

        // Create bookings
        if ($parent1 && $child1) {
            $booking1 = Booking::create([
                'user_id' => $parent1->id,
                'child_id' => $child1->id,
                'schedule_id' => $schedule1->id,
                'sessions_remaining' => 4,
                'status' => 'confirmed',
                'is_paid' => true,
                'check_in_code' => 'GYM001',
                'total_cost' => 140.00,
            ]);
        }

        if ($parent2 && $child2) {
            $booking2 = Booking::create([
                'user_id' => $parent2->id,
                'child_id' => $child2->id,
                'schedule_id' => $schedule1->id,
                'sessions_remaining' => 4,
                'status' => 'pending',
                'is_paid' => false,
                'check_in_code' => 'GYM002',
                'total_cost' => 140.00,
            ]);
        }

        // Create payments
        if (isset($booking1) && $parent1) {
            Payment::create([
                'user_id' => $parent1->id,
                'booking_id' => $booking1->id,
                'amount' => $schedule1->price,
                'description' => 'Gymnastics class payment',
                'status' => 'paid',
                'payment_date' => now(),
                'paid_at' => now(),
            ]);
        }

        // Create waitlist
        if ($parent2 && $child2) {
            Waitlist::create([
                'user_id' => $parent2->id,
                'child_id' => $child2->id,
                'schedule_id' => $schedule2->id,
                'sessions_requested' => 1,
                'status' => 'waiting',
                'notes' => 'Interested in swimming lessons',
            ]);
        }

        // Seed additional children for each user
        User::all()->each(function ($user) {
            Child::factory()->count(2)->create([
                'user_id' => $user->id,
            ]);
        });
    }
}
