<?php

/**
 * Comprehensive Test Script for CSV Import System
 * This script tests all components of the CSV import functionality
 */

require_once 'vendor/autoload.php';

use App\Models\Schedule;
use App\Models\Trainer;
use App\Models\Category;
use App\Models\User;
use Illuminate\Support\Facades\DB;

echo "=== CSV Import System Test ===\n\n";

// Test 1: Check if required models exist
echo "1. Testing Model Availability...\n";
try {
    $trainers = Trainer::count();
    $categories = Category::count();
    $schedules = Schedule::count();
    
    echo "   ✓ Trainers: {$trainers}\n";
    echo "   ✓ Categories: {$categories}\n";
    echo "   ✓ Schedules: {$schedules}\n";
} catch (Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n";
}

// Test 2: Check if storage directory exists
echo "\n2. Testing Storage Directory...\n";
$csvImportDir = storage_path('app/csv_import');
if (is_dir($csvImportDir)) {
    echo "   ✓ CSV import directory exists\n";
} else {
    echo "   ✗ CSV import directory missing\n";
    mkdir($csvImportDir, 0755, true);
    echo "   ✓ Created CSV import directory\n";
}

// Test 3: Check if test CSV file exists
echo "\n3. Testing Test CSV File...\n";
$testFile = 'test_schedules_import.csv';
if (file_exists($testFile)) {
    echo "   ✓ Test CSV file exists\n";
    $lines = count(file($testFile));
    echo "   ✓ CSV file has {$lines} lines\n";
} else {
    echo "   ✗ Test CSV file missing\n";
}

// Test 4: Validate CSV structure
echo "\n4. Testing CSV Structure...\n";
if (file_exists($testFile)) {
    $handle = fopen($testFile, 'r');
    $headers = fgetcsv($handle);
    fclose($handle);
    
    $requiredFields = [
        'title', 'description', 'trainer_id', 'category_id', 'type',
        'start_date', 'end_date', 'start_time', 'end_time', 'price',
        'max_participants', 'is_featured', 'status', 'allow_unlimited_bookings',
        'is_discounted', 'discount_percentage', 'location'
    ];
    
    $missingFields = array_diff($requiredFields, $headers);
    if (empty($missingFields)) {
        echo "   ✓ All required fields present\n";
    } else {
        echo "   ✗ Missing fields: " . implode(', ', $missingFields) . "\n";
    }
}

// Test 5: Check SpreadsheetReader package
echo "\n5. Testing SpreadsheetReader Package...\n";
try {
    $reader = new SpreadsheetReader($testFile);
    echo "   ✓ SpreadsheetReader works\n";
} catch (Exception $e) {
    echo "   ✗ SpreadsheetReader error: " . $e->getMessage() . "\n";
}

// Test 6: Validate data integrity
echo "\n6. Testing Data Integrity...\n";
if (file_exists($testFile)) {
    $handle = fopen($testFile, 'r');
    $headers = fgetcsv($handle);
    $rowNum = 1;
    $errors = [];
    
    while (($data = fgetcsv($handle)) !== false) {
        $rowNum++;
        $row = array_combine($headers, $data);
        
        // Check required fields
        $required = ['title', 'trainer_id', 'category_id', 'type', 'start_date', 'end_date', 'start_time', 'end_time', 'price', 'max_participants', 'status'];
        foreach ($required as $field) {
            if (empty($row[$field])) {
                $errors[] = "Row {$rowNum}: Missing {$field}";
            }
        }
        
        // Check trainer exists
        if (!empty($row['trainer_id'])) {
            $trainer = Trainer::find($row['trainer_id']);
            if (!$trainer) {
                $errors[] = "Row {$rowNum}: Trainer ID {$row['trainer_id']} not found";
            }
        }
        
        // Check category exists
        if (!empty($row['category_id'])) {
            $category = Category::find($row['category_id']);
            if (!$category) {
                $errors[] = "Row {$rowNum}: Category ID {$row['category_id']} not found";
            }
        }
        
        // Check date format
        if (!empty($row['start_date']) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $row['start_date'])) {
            $errors[] = "Row {$rowNum}: Invalid start_date format";
        }
        
        if (!empty($row['end_date']) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $row['end_date'])) {
            $errors[] = "Row {$rowNum}: Invalid end_date format";
        }
        
        // Check time format
        if (!empty($row['start_time']) && !preg_match('/^\d{2}:\d{2}:\d{2}$/', $row['start_time'])) {
            $errors[] = "Row {$rowNum}: Invalid start_time format";
        }
        
        if (!empty($row['end_time']) && !preg_match('/^\d{2}:\d{2}:\d{2}$/', $row['end_time'])) {
            $errors[] = "Row {$rowNum}: Invalid end_time format";
        }
    }
    fclose($handle);
    
    if (empty($errors)) {
        echo "   ✓ All data validation passed\n";
    } else {
        echo "   ✗ Data validation errors:\n";
        foreach ($errors as $error) {
            echo "      - {$error}\n";
        }
    }
}

// Test 7: Check routes
echo "\n7. Testing Routes...\n";
$routes = [
    'admin.schedules.import' => 'GET /admin/schedules/import',
    'admin.schedules.parse-csv' => 'POST /admin/schedules/import/parse',
    'admin.schedules.process-csv' => 'POST /admin/schedules/import/process',
    'admin.schedules.download-template' => 'GET /admin/schedules/import/template'
];

foreach ($routes as $name => $route) {
    try {
        $routeUrl = route($name);
        echo "   ✓ {$route} -> {$routeUrl}\n";
    } catch (Exception $e) {
        echo "   ✗ {$route}: " . $e->getMessage() . "\n";
    }
}

// Test 8: Check views
echo "\n8. Testing Views...\n";
$views = [
    'admin.schedules.import' => 'resources/views/admin/schedules/import.blade.php',
    'admin.schedules.parse-csv' => 'resources/views/admin/schedules/parse-csv.blade.php'
];

foreach ($views as $name => $path) {
    if (file_exists($path)) {
        echo "   ✓ {$name} view exists\n";
    } else {
        echo "   ✗ {$name} view missing\n";
    }
}

// Test 9: Check controller methods
echo "\n9. Testing Controller Methods...\n";
$controller = new \App\Http\Controllers\Admin\ScheduleController();
$methods = ['importForm', 'parseCsvImport', 'processCsvImport', 'downloadTemplate'];

foreach ($methods as $method) {
    if (method_exists($controller, $method)) {
        echo "   ✓ {$method} method exists\n";
    } else {
        echo "   ✗ {$method} method missing\n";
    }
}

echo "\n=== Test Complete ===\n";
echo "Server should be running on http://localhost:8008\n";
echo "Test the import system by visiting: http://localhost:8008/admin/schedules/import\n"; 