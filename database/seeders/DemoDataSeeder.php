<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\Schedule;
use App\Models\Waitlist;
use App\Models\User;
use App\Models\Child;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Use existing users if available
        $trainer = \App\Models\User::where('role', 'trainer')->first();
        $parent1 = \App\Models\User::where('role', 'parent')->first();
        $parent2 = \App\Models\User::where('role', 'parent')->skip(1)->first();

        // Ensure a trainer exists
        if (!$trainer) {
            $trainer = User::create([
                'name' => 'Demo Trainer',
                'email' => 'trainer@example.com',
                'password' => bcrypt('password'),
                'role' => 'trainer',
            ]);
        }

        // Create schedules
        $schedule1 = Schedule::create([
            'title' => 'Morning Gymnastics',
            'description' => 'Basic gymnastics for beginners',
            'start_time' => now()->addDays(1)->setHour(9)->setMinute(0),
            'end_time' => now()->addDays(1)->setHour(10)->setMinute(0),
            'max_participants' => 5,
            'current_participants' => 0,
            'price' => 25.00,
            'trainer_id' => $trainer ? $trainer->id : null,
            'status' => 'active',
        ]);

        $schedule2 = Schedule::create([
            'title' => 'Advanced Gymnastics',
            'description' => 'Advanced gymnastics for experienced students',
            'start_time' => now()->addDays(2)->setHour(14)->setMinute(0),
            'end_time' => now()->addDays(2)->setHour(15)->setMinute(0),
            'max_participants' => 3,
            'current_participants' => 0,
            'price' => 35.00,
            'trainer_id' => $trainer ? $trainer->id : null,
            'status' => 'active',
        ]);

        // Create bookings
        if ($parent1) {
            $booking1 = Booking::create([
                'user_id' => $parent1->id,
                'schedule_id' => $schedule1->id,
                'child_name' => 'Child One',
                'child_age' => 8,
                'sessions_remaining' => 4,
                'status' => 'confirmed',
                'is_paid' => true,
            ]);
        }

        if ($parent2) {
            $booking2 = Booking::create([
                'user_id' => $parent2->id,
                'schedule_id' => $schedule1->id,
                'child_name' => 'Child Two',
                'child_age' => 7,
                'sessions_remaining' => 4,
                'status' => 'pending',
                'is_paid' => false,
            ]);
        }

        // Create payments
        if (isset($booking1) && $parent1) {
            Payment::create([
                'user_id' => $parent1->id,
                'booking_id' => $booking1->id,
                'amount' => $schedule1->price,
                'payment_method' => 'stripe',
                'status' => 'completed',
                'stripe_payment_id' => 'demo_stripe_123',
            ]);
        }

        // Create waitlist
        if ($parent2) {
            Waitlist::create([
                'user_id' => $parent2->id,
                'schedule_id' => $schedule2->id,
                'child_name' => 'Child Two',
                'child_age' => 7,
                'position' => 1,
                'status' => 'waiting',
            ]);
        }

        // Seed children for each user
        User::all()->each(function ($user) {
            Child::factory()->count(2)->create([
                'user_id' => $user->id,
            ]);
        });

        // Seed admin user
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        // Seed regular user
        User::create([
            'name' => 'Regular User',
            'email' => 'user@example.com',
            'password' => bcrypt('password'),
            'role' => 'user',
        ]);
    }
}
