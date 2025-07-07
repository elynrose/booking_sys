<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Trainer;
use App\Models\TrainerAvailability;
use Carbon\Carbon;

echo "=== Debug Calendar Data ===\n";

// Get site timezone
$siteTimezone = \App\Models\SiteSettings::getTimezone();
echo "Site Timezone: " . $siteTimezone . "\n";

// Get current month (July 2025)
$currentDate = Carbon::createFromDate(2025, 7, 1, $siteTimezone);
$startOfMonth = $currentDate->copy()->startOfMonth();
$endOfMonth = $currentDate->copy()->endOfMonth();

echo "Current Date: " . $currentDate->format('Y-m-d H:i:s') . "\n";
echo "Start of Month: " . $startOfMonth->format('Y-m-d H:i:s') . "\n";
echo "End of Month: " . $endOfMonth->format('Y-m-d H:i:s') . "\n\n";

// Get all trainers with their availability for the month
$trainers = Trainer::with(['user', 'availabilities' => function($query) use ($startOfMonth, $endOfMonth) {
    $query->whereBetween('date', [$startOfMonth->format('Y-m-d'), $endOfMonth->format('Y-m-d')])
          ->where('status', 'available')
          ->orderBy('date')
          ->orderBy('start_time');
}])->get();

echo "Total Trainers: " . $trainers->count() . "\n";
echo "Total Availabilities: " . $trainers->sum(function($trainer) { 
    return $trainer->availabilities ? $trainer->availabilities->count() : 0; 
}) . "\n\n";

// Check each trainer's availabilities
foreach ($trainers as $trainer) {
    echo "Trainer: " . $trainer->user->name . " (ID: " . $trainer->id . ")\n";
    if ($trainer->availabilities) {
        echo "  Availabilities: " . $trainer->availabilities->count() . "\n";
        foreach ($trainer->availabilities as $availability) {
            echo "    - Date: " . $availability->date . " (Type: " . gettype($availability->date) . ")\n";
            echo "      Start: " . $availability->start_time . "\n";
            echo "      End: " . $availability->end_time . "\n";
        }
    } else {
        echo "  No availabilities\n";
    }
    echo "\n";
}

// Test calendar generation
echo "=== Testing Calendar Generation ===\n";

$firstDayOfMonth = $startOfMonth->copy()->startOfWeek();
$lastDayOfMonth = $endOfMonth->copy()->endOfWeek();

echo "First Day of Calendar: " . $firstDayOfMonth->format('Y-m-d') . "\n";
echo "Last Day of Calendar: " . $lastDayOfMonth->format('Y-m-d') . "\n\n";

// Test a specific date that should have availability
$testDate = Carbon::createFromDate(2025, 7, 5, $siteTimezone);
echo "Testing date: " . $testDate->format('Y-m-d') . "\n";

foreach ($trainers as $trainer) {
    if ($trainer->availabilities) {
        $dayAvailabilities = $trainer->availabilities->where('date', $testDate->format('Y-m-d'));
        echo "Trainer " . $trainer->user->name . " on " . $testDate->format('Y-m-d') . ": " . $dayAvailabilities->count() . " availabilities\n";
        
        // Check all availabilities for this trainer
        foreach ($trainer->availabilities as $availability) {
            echo "  Availability date: '" . $availability->date . "' vs test date: '" . $testDate->format('Y-m-d') . "'\n";
            echo "  Match: " . ($availability->date == $testDate->format('Y-m-d') ? 'YES' : 'NO') . "\n";
        }
    }
}

echo "\n=== End Debug ===\n"; 