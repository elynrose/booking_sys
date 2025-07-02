<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Schedule;
use App\Models\Trainer;
use App\Models\Checkin;
use App\Models\Booking;
use App\Models\Payment;
use Carbon\Carbon;

echo "Testing Live Dashboard Data...\n\n";

$now = Carbon::now();
$today = Carbon::today();

echo "Current time: " . $now->format('Y-m-d H:i:s') . "\n";
echo "Today: " . $today->format('Y-m-d') . "\n\n";

// Test current classes
echo "=== Current Classes ===\n";
$currentClasses = Schedule::with(['trainer.user', 'category', 'bookings.user', 'bookings.checkin'])
    ->whereDate('start_time', $today)
    ->where('start_time', '<=', $now->addHours(2))
    ->where('end_time', '>=', $now->subHours(1))
    ->get();

echo "Found " . $currentClasses->count() . " classes for today\n";
foreach ($currentClasses as $schedule) {
    echo "- " . $schedule->title . " (Trainer: " . ($schedule->trainer->user->name ?? 'Unassigned') . ")\n";
}

// Test trainer assignments
echo "\n=== Trainer Assignments ===\n";
$trainerAssignments = Trainer::with(['user', 'schedules.bookings.user', 'schedules.bookings.checkin'])
    ->get();

echo "Found " . $trainerAssignments->count() . " trainers\n";
foreach ($trainerAssignments as $trainer) {
    echo "- " . $trainer->user->name . " (Status: " . ($trainer->schedules()->whereDate('start_time', $now->toDateString())->where('start_time', '<=', $now)->where('end_time', '>=', $now)->first() ? 'active' : 'available') . ")\n";
}

// Test recent checkins
echo "\n=== Recent Check-ins ===\n";
$recentCheckins = Checkin::with(['booking.user', 'booking.schedule'])
    ->whereDate('created_at', $today)
    ->orderBy('created_at', 'desc')
    ->take(5)
    ->get();

echo "Found " . $recentCheckins->count() . " recent check-ins\n";
foreach ($recentCheckins as $checkin) {
    echo "- " . ($checkin->booking->user->name ?? 'Unknown') . " checked " . ($checkin->checkout_time ? 'out' : 'in') . " for " . ($checkin->booking->schedule->title ?? 'Unknown Class') . "\n";
}

// Test statistics
echo "\n=== Statistics ===\n";
$activeClasses = $currentClasses->filter(function($schedule) use ($now) {
    return $schedule->start_time <= $now && $schedule->end_time >= $now;
})->count();

$checkedInStudents = Checkin::whereDate('created_at', $today)
    ->whereNotNull('checkin_time')
    ->whereNull('checkout_time')
    ->count();

$activeTrainers = $trainerAssignments->filter(function($trainer) use ($now) {
    return $trainer->schedules()->whereDate('start_time', $now->toDateString())->where('start_time', '<=', $now)->where('end_time', '>=', $now)->exists();
})->count();

echo "Active Classes: " . $activeClasses . "\n";
echo "Checked In Students: " . $checkedInStudents . "\n";
echo "Active Trainers: " . $activeTrainers . "\n";

echo "\nTest completed!\n"; 