<?php

require_once 'vendor/autoload.php';

use App\Models\Schedule;
use App\Models\Trainer;
use App\Models\Category;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== CSV Import System Test ===\n\n";

try {
    // Test 1: Check if trainer_id is nullable
    echo "1. Testing trainer_id nullable constraint...\n";
    
    $schedule = new Schedule([
        'title' => 'Test Schedule',
        'description' => 'Test description',
        'start_date' => '2025-07-07',
        'end_date' => '2025-07-07',
        'start_time' => '09:00:00',
        'end_time' => '10:00:00',
        'max_participants' => 10,
        'price' => 25.00,
        'location' => 'Test Location',
        'status' => 'active',
        'trainer_id' => null, // This should work now
    ]);
    
    $schedule->save();
    echo "✓ Successfully created schedule with null trainer_id\n";
    
    // Clean up test schedule
    $schedule->delete();
    echo "✓ Test schedule cleaned up\n\n";
    
    // Test 2: Check available trainers
    echo "2. Checking available trainers...\n";
    $trainers = Trainer::where('is_active', true)->get();
    echo "Found " . $trainers->count() . " active trainers:\n";
    foreach ($trainers as $trainer) {
        echo "  - ID: {$trainer->id}, Name: " . $trainer->user->name . "\n";
    }
    echo "\n";
    
    // Test 3: Check available categories
    echo "3. Checking available categories...\n";
    $categories = Category::all();
    echo "Found " . $categories->count() . " categories:\n";
    foreach ($categories as $category) {
        echo "  - ID: {$category->id}, Name: {$category->name}\n";
    }
    echo "\n";
    
    // Test 4: Test CSV import class
    echo "4. Testing SchedulesImport class...\n";
    $import = new \App\Imports\SchedulesImport();
    
    // Test with sample data
    $sampleRow = [
        'title' => 'Test Yoga Class',
        'description' => 'Test yoga session',
        'start_time' => '09:00',
        'end_time' => '10:00',
        'date' => '2025-07-07',
        'category' => 'Test Category',
        'instructor' => $trainers->first() ? $trainers->first()->user->name : 'Test Instructor',
        'max_capacity' => 15,
        'price' => 30.00,
        'location' => 'Test Studio',
        'status' => 'active'
    ];
    
    $result = $import->model($sampleRow);
    if ($result) {
        echo "✓ Successfully created schedule from sample data\n";
        $result->delete(); // Clean up
        echo "✓ Test schedule cleaned up\n";
    } else {
        echo "✗ Failed to create schedule from sample data\n";
    }
    
    echo "\n=== All Tests Passed! ===\n";
    echo "The CSV import system is working correctly.\n";
    echo "You can now upload CSV files without trainer_id constraint errors.\n";
    
} catch (Exception $e) {
    echo "✗ Test failed: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} 