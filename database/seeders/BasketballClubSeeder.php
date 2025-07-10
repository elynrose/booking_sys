<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Trainer;
use App\Models\User;
use App\Models\Schedule;
use App\Models\Booking;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class BasketballClubSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Categories
        $categories = [
            'Basketball Skills',
            'Youth Basketball',
            'Elite Training',
            'Shooting Clinics',
            'Summer Camps',
        ];
        $categoryIds = [];
        foreach ($categories as $cat) {
            $category = Category::firstOrCreate([
                'name' => $cat
            ], [
                'description' => $cat . ' sessions and programs',
                'is_active' => true
            ]);
            $categoryIds[$cat] = $category->id;
        }

        // 2. Create Trainers & Users
        $trainersData = [
            ['name' => 'Coach Carter', 'email' => 'carter@basketclub.com'],
            ['name' => 'Coach Lisa', 'email' => 'lisa@basketclub.com'],
            ['name' => 'Coach Mike', 'email' => 'mike@basketclub.com'],
        ];
        $trainerIds = [];
        foreach ($trainersData as $data) {
            $user = User::firstOrCreate([
                'email' => $data['email']
            ], [
                'name' => $data['name'],
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'timezone' => 'UTC',
            ]);
            $user->assignRole('Trainer');
            $trainer = Trainer::firstOrCreate([
                'user_id' => $user->id
            ], [
                'bio' => $data['name'] . ' is a certified basketball coach.',
                'is_active' => true,
                'payment_method' => 'check',
                'payment_details' => $data['email'],
            ]);
            $trainerIds[$data['name']] = $trainer->id;
        }

        // 3. Create Schedules
        $schedulesData = [
            [
                'title' => 'Youth Basketball Fundamentals',
                'category' => 'Youth Basketball',
                'trainer' => 'Coach Lisa',
                'type' => 'group',
                'start_date' => Carbon::now()->addDays(2),
                'end_date' => Carbon::now()->addDays(32),
                'start_time' => '16:00',
                'end_time' => '18:00',
                'price' => 20,
                'max_participants' => 20,
                'status' => 'active',
                'location' => 'Main Gym',
            ],
            [
                'title' => 'Elite Shooting Clinic',
                'category' => 'Shooting Clinics',
                'trainer' => 'Coach Mike',
                'type' => 'group',
                'start_date' => Carbon::now()->addDays(5),
                'end_date' => Carbon::now()->addDays(10),
                'start_time' => '18:30',
                'end_time' => '20:00',
                'price' => 35,
                'max_participants' => 15,
                'status' => 'active',
                'location' => 'Court 2',
            ],
            [
                'title' => 'Private 1-on-1 Training',
                'category' => 'Elite Training',
                'trainer' => 'Coach Carter',
                'type' => 'private',
                'start_date' => Carbon::now()->addDays(1),
                'end_date' => Carbon::now()->addDays(30),
                'start_time' => '14:00',
                'end_time' => '15:00',
                'price' => 50,
                'max_participants' => 1,
                'status' => 'active',
                'location' => 'Private Court',
            ],
            [
                'title' => 'Summer Basketball Camp',
                'category' => 'Summer Camps',
                'trainer' => 'Coach Lisa',
                'type' => 'group',
                'start_date' => Carbon::now()->addDays(10),
                'end_date' => Carbon::now()->addDays(17),
                'start_time' => '09:00',
                'end_time' => '15:00',
                'price' => 200,
                'max_participants' => 30,
                'status' => 'active',
                'location' => 'Main Gym',
            ],
        ];
        $scheduleIds = [];
        foreach ($schedulesData as $data) {
            $schedule = Schedule::create([
                'title' => $data['title'],
                'description' => $data['title'] . ' for all skill levels.',
                'trainer_id' => $trainerIds[$data['trainer']],
                'category_id' => $categoryIds[$data['category']],
                'type' => $data['type'],
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'start_time' => $data['start_time'],
                'end_time' => $data['end_time'],
                'price' => $data['price'],
                'max_participants' => $data['max_participants'],
                'status' => $data['status'],
                'location' => $data['location'],
                'is_featured' => true,
            ]);
            $scheduleIds[] = $schedule->id;
        }

        // 4. Create Demo Users and Bookings
        $demoUsers = [
            ['name' => 'Jordan Smith', 'email' => 'jordan@player.com'],
            ['name' => 'Taylor Lee', 'email' => 'taylor@player.com'],
            ['name' => 'Morgan Brown', 'email' => 'morgan@player.com'],
        ];
        foreach ($demoUsers as $userData) {
            $user = User::firstOrCreate([
                'email' => $userData['email']
            ], [
                'name' => $userData['name'],
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'timezone' => 'UTC',
            ]);
            // Book each user into a random schedule
            foreach ($scheduleIds as $sid) {
                Booking::create([
                    'user_id' => $user->id,
                    'schedule_id' => $sid,
                    'status' => 'confirmed',
                    'is_paid' => true,
                    'total_cost' => Schedule::find($sid)->price,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
} 