<?php

/**
 * Upload Debug Test
 * This script helps debug the CSV upload issue
 */

require_once 'vendor/autoload.php';

use App\Models\User;
use App\Models\Trainer;
use App\Models\Category;
use Illuminate\Support\Facades\DB;

echo "=== Upload Debug Test ===\n\n";

try {
    // Test 1: Check if Laravel Excel is working
    echo "1. Testing Laravel Excel...\n";
    if (class_exists('Maatwebsite\Excel\Facades\Excel')) {
        echo "   ✅ Laravel Excel is available\n";
    } else {
        echo "   ❌ Laravel Excel not found\n";
        exit(1);
    }

    // Test 2: Check if SchedulesImport class exists
    echo "2. Testing SchedulesImport class...\n";
    if (class_exists('App\Imports\SchedulesImport')) {
        echo "   ✅ SchedulesImport class exists\n";
    } else {
        echo "   ❌ SchedulesImport class not found\n";
        exit(1);
    }

    // Test 3: Check if test CSV file exists
    echo "3. Testing CSV file...\n";
    $csvFile = 'test_schedules_laravel_excel.csv';
    if (file_exists($csvFile)) {
        echo "   ✅ Test CSV file exists: $csvFile\n";
        echo "   📄 File size: " . filesize($csvFile) . " bytes\n";
        
        // Read first few lines
        $lines = file($csvFile, FILE_IGNORE_NEW_LINES);
        echo "   📋 First line: " . $lines[0] . "\n";
        echo "   📋 Second line: " . $lines[1] . "\n";
    } else {
        echo "   ❌ Test CSV file not found: $csvFile\n";
        exit(1);
    }

    // Test 4: Check database connections
    echo "4. Testing database...\n";
    try {
        $userCount = User::count();
        echo "   ✅ Database connected. Users: $userCount\n";
    } catch (Exception $e) {
        echo "   ❌ Database error: " . $e->getMessage() . "\n";
        exit(1);
    }

    // Test 5: Check if trainers exist
    echo "5. Testing trainers...\n";
    $trainers = Trainer::with('user')->get();
    echo "   📊 Found " . $trainers->count() . " trainers:\n";
    foreach ($trainers as $trainer) {
        echo "      - ID: {$trainer->id}, Name: {$trainer->user->name}, Email: {$trainer->user->email}\n";
    }

    // Test 6: Check if categories exist
    echo "6. Testing categories...\n";
    $categories = Category::all();
    echo "   📊 Found " . $categories->count() . " categories:\n";
    foreach ($categories as $category) {
        echo "      - ID: {$category->id}, Name: {$category->name}\n";
    }

    // Test 7: Check storage permissions
    echo "7. Testing storage...\n";
    $storagePath = storage_path('app');
    if (is_writable($storagePath)) {
        echo "   ✅ Storage is writable: $storagePath\n";
    } else {
        echo "   ❌ Storage is not writable: $storagePath\n";
    }

    // Test 8: Check temp directory
    echo "8. Testing temp directory...\n";
    $tempPath = storage_path('app/temp_imports');
    if (!is_dir($tempPath)) {
        mkdir($tempPath, 0755, true);
        echo "   ✅ Created temp directory: $tempPath\n";
    } else {
        echo "   ✅ Temp directory exists: $tempPath\n";
    }

    echo "\n=== Manual Upload Test ===\n";
    echo "To test the upload manually:\n";
    echo "1. Go to: http://localhost:8008/admin/schedules/import\n";
    echo "2. Upload the file: $csvFile\n";
    echo "3. Check the browser console for any JavaScript errors\n";
    echo "4. Check the Laravel logs: tail -f storage/logs/laravel.log\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} 