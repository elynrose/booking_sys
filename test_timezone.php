<?php

require_once 'vendor/autoload.php';

// Load Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== TIMEZONE TEST ===\n\n";

// Check current timezone
$currentTimezone = config('app.timezone');
echo "Current Application Timezone: " . $currentTimezone . "\n";

// Show current time in different formats
$now = now();
echo "Current Time (Laravel): " . $now->format('Y-m-d H:i:s T') . "\n";
echo "Current Time (PHP): " . date('Y-m-d H:i:s T') . "\n";

// Show timezone info
$timezoneInfo = new DateTimeZone($currentTimezone);
$offset = $timezoneInfo->getOffset(new DateTime()) / 3600;
echo "Timezone Offset: " . ($offset >= 0 ? '+' : '') . $offset . " hours from UTC\n";

// Test with a specific date
$testDate = Carbon\Carbon::parse('2024-01-15 10:00:00');
echo "Test Date (Jan 15, 2024 10:00 AM): " . $testDate->format('Y-m-d H:i:s T') . "\n";

echo "\n=== TIMEZONE CHANGE VERIFICATION ===\n";
if ($currentTimezone === 'America/New_York') {
    echo "✅ Timezone successfully changed to EST (America/New_York)\n";
} else {
    echo "❌ Timezone is still: " . $currentTimezone . "\n";
    echo "Expected: America/New_York\n";
}

echo "\n=== ENVIRONMENT VARIABLE ===\n";
$envTimezone = env('APP_TIMEZONE');
echo "APP_TIMEZONE from .env: " . ($envTimezone ?: 'NOT SET') . "\n"; 