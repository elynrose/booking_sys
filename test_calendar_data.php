<?php

require_once 'vendor/autoload.php';

use App\Models\TrainerAvailability;
use App\Models\Trainer;
use App\Models\User;
use Carbon\Carbon;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Trainer Availability Calendar Data Test ===\n\n";

// Get the trainer user
$user = User::where('email', 'trainer@example.com')->first();
if (!$user) {
    echo "Trainer user not found!\n";
    exit;
}

echo "User: {$user->email}\n";

// Get the trainer record
$trainer = Trainer::where('user_id', $user->id)->first();
if (!$trainer) {
    echo "Trainer profile not found!\n";
    exit;
}

echo "Trainer ID: {$trainer->id}\n\n";

// Get current month
$month = Carbon::now()->format('Y-m');
$date = Carbon::parse($month . '-01');

echo "Checking for month: {$month}\n";
echo "Date range: {$date->format('Y-m-d')} to {$date->copy()->endOfMonth()->format('Y-m-d')}\n\n";

// Get all availabilities for the trainer
$allAvailabilities = TrainerAvailability::where('trainer_id', $trainer->id)->get();
echo "Total availabilities for trainer: " . $allAvailabilities->count() . "\n\n";

// Get availabilities for current month
$availabilities = TrainerAvailability::where('trainer_id', $trainer->id)
    ->whereYear('date', $date->year)
    ->whereMonth('date', $date->month)
    ->get();

echo "Availabilities for current month: " . $availabilities->count() . "\n\n";

if ($availabilities->count() > 0) {
    echo "Available dates:\n";
    foreach ($availabilities as $availability) {
        echo "- {$availability->date} ({$availability->start_time} - {$availability->end_time}) - {$availability->status}\n";
    }
} else {
    echo "No availabilities found for current month.\n";
}

// Check if there are availabilities in July 2025
$julyDate = Carbon::parse('2025-07-01');
$julyAvailabilities = TrainerAvailability::where('trainer_id', $trainer->id)
    ->whereYear('date', $julyDate->year)
    ->whereMonth('date', $julyDate->month)
    ->get();

echo "\nAvailabilities for July 2025: " . $julyAvailabilities->count() . "\n";

if ($julyAvailabilities->count() > 0) {
    echo "July 2025 available dates:\n";
    foreach ($julyAvailabilities as $availability) {
        echo "- {$availability->date} ({$availability->start_time} - {$availability->end_time}) - {$availability->status}\n";
    }
}

echo "\n=== Test Complete ===\n"; 