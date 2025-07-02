<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Trainer;
use App\Models\Schedule;
use App\Models\Booking;
use App\Models\Checkin;
use App\Models\Category;
use Carbon\Carbon;

echo "Creating Live Dashboard Demo Data...\n\n";

$today = Carbon::tomorrow();
$now = Carbon::now();

// Create categories
$category = Category::first();
if (!$category) {
    $category = Category::create([
        'name' => 'Sports',
        'description' => 'Sports activities'
    ]);
    echo "Created category\n";
}

// Create trainers and users
$trainerUsers = [];
$trainers = [];
for ($i = 1; $i <= 3; $i++) {
    $email = "trainer{$i}@gymapp.com";
    $trainerUser = User::where('email', $email)->first();
    if (!$trainerUser) {
        $trainerUser = User::create([
            'name' => "Trainer {$i}",
            'email' => $email,
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
            'member_id' => "GYM-2025-T00{$i}"
        ]);
        $trainerUser->assignRole('Trainer');
        echo "Created trainer user: {$trainerUser->name}\n";
    }
    $trainer = Trainer::where('user_id', $trainerUser->id)->first();
    if (!$trainer) {
        $trainer = Trainer::create([
            'user_id' => $trainerUser->id,
            'specialization' => 'Sport ' . $i,
            'experience_years' => 2 + $i,
            'bio' => 'Trainer bio'
        ]);
        echo "Created trainer: {$trainerUser->name}\n";
    }
    $trainerUsers[] = $trainerUser;
    $trainers[] = $trainer;
}

// Create multiple classes for today
$schedules = [];
$classTimes = [
    ['14:00', '15:00'],
    ['15:30', '16:30'],
    ['17:00', '18:00']
];
for ($i = 0; $i < 3; $i++) {
    $schedule = Schedule::create([
        'title' => 'Class ' . ($i + 1),
        'slug' => 'class-' . ($i + 1),
        'type' => 'class',
        'description' => 'Class description ' . ($i + 1),
        'trainer_id' => $trainers[$i]->id,
        'category_id' => $category->id,
        'start_date' => $today,
        'end_date' => $today,
        'start_time' => $today->copy()->setTime(...explode(':', $classTimes[$i][0])),
        'end_time' => $today->copy()->setTime(...explode(':', $classTimes[$i][1])),
        'price' => 20.00 + $i * 5,
        'max_participants' => 10 + $i * 5,
        'status' => 'active'
    ]);
    $schedules[] = $schedule;
    echo "Created schedule for today: {$schedule->title}\n";
}

// Create users
$users = [];
for ($i = 1; $i <= 12; $i++) {
    $user = User::where('email', "student{$i}@gymapp.com")->first();
    if (!$user) {
        $user = User::create([
            'name' => "Student {$i}",
            'email' => "student{$i}@gymapp.com",
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
            'member_id' => "GYM-2025-S00{$i}"
        ]);
        $user->assignRole('User');
        echo "Created user: {$user->name}\n";
    }
    $users[] = $user;
}

// Create bookings and check-ins/check-outs
foreach ($schedules as $idx => $schedule) {
    // Assign 4 users to each class
    for ($j = 0; $j < 4; $j++) {
        $user = $users[$idx * 4 + $j];
        $booking = Booking::create([
            'user_id' => $user->id,
            'schedule_id' => $schedule->id,
            'status' => 'confirmed',
            'booking_date' => $today,
            'total_amount' => $schedule->price
        ]);
        echo "Created booking for: {$user->name} in {$schedule->title}\n";
        // Randomly check in and out
        $checkinTime = $schedule->start_time->copy()->addMinutes(rand(0, 10));
        $checkout = rand(0, 1);
        $checkin = Checkin::create([
            'booking_id' => $booking->id,
            'checkin_time' => $checkinTime,
            'is_late_checkin' => $checkinTime->gt($schedule->start_time),
            'late_minutes' => $checkinTime->diffInMinutes($schedule->start_time, false) > 0 ? $checkinTime->diffInMinutes($schedule->start_time) : 0,
            'checkout_time' => $checkout ? $schedule->end_time->copy()->addMinutes(rand(0, 10)) : null
        ]);
        echo "Created check-in for: {$user->name} in {$schedule->title}" . ($checkout ? " (checked out)" : " (still in)") . "\n";
    }
}

echo "\nDemo data created successfully!\n";
echo "You can now test the live dashboard at: /admin/live-dashboard\n"; 