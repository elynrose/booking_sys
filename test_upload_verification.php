<?php

/**
 * Upload Verification Test
 * This script helps verify if the upload is actually working
 */

require_once 'vendor/autoload.php';

use App\Models\Schedule;
use App\Models\Category;
use App\Models\Trainer;

echo "=== Upload Verification Test ===\n\n";

try {
    // Check current schedules count
    $currentSchedules = Schedule::count();
    echo "📊 Current schedules in database: $currentSchedules\n\n";
    
    // Check recent schedules
    $recentSchedules = Schedule::orderBy('created_at', 'desc')->take(5)->get();
    echo "📋 Recent schedules:\n";
    foreach ($recentSchedules as $schedule) {
        echo "   - ID: {$schedule->id}, Title: {$schedule->title}, Created: {$schedule->created_at}\n";
    }
    
    echo "\n📊 Categories available: " . Category::count() . "\n";
    echo "📊 Trainers available: " . Trainer::count() . "\n\n";
    
    echo "🔍 **To test the upload**:\n\n";
    echo "1. Note the current schedule count: $currentSchedules\n";
    echo "2. Go to: http://localhost:8008/admin/schedules/import\n";
    echo "3. Upload the file: test_schedules_laravel_excel.csv\n";
    echo "4. Check if the count increases\n";
    echo "5. Look for new schedules in the list\n\n";
    
    echo "📋 **Test file contents**:\n";
    $csvFile = 'test_schedules_laravel_excel.csv';
    if (file_exists($csvFile)) {
        $lines = file($csvFile, FILE_IGNORE_NEW_LINES);
        echo "   Header: " . $lines[0] . "\n";
        echo "   First row: " . $lines[1] . "\n";
        echo "   Total rows: " . count($lines) . "\n\n";
    }
    
    echo "🚨 **If upload still doesn't work**:\n";
    echo "- Check the Laravel logs (tail -f storage/logs/laravel.log)\n";
    echo "- Look for the log messages we added to the controller\n";
    echo "- Check if there are any validation errors\n";
    echo "- Verify the file format matches the expected headers\n\n";
    
    echo "✅ **Expected behavior**:\n";
    echo "- After upload, you should see new schedules in the list\n";
    echo "- The schedule count should increase\n";
    echo "- You should see a success message\n";
    echo "- The schedules should have the correct data\n\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
} 