<?php

/**
 * Simple Test Script for CSV Import System
 * Tests basic functionality without database access
 */

echo "=== CSV Import System Test ===\n\n";

// Test 1: Check if test CSV file exists
echo "1. Testing Test CSV File...\n";
$testFile = 'test_schedules_import.csv';
if (file_exists($testFile)) {
    echo "   ✓ Test CSV file exists\n";
    $lines = count(file($testFile));
    echo "   ✓ CSV file has {$lines} lines\n";
} else {
    echo "   ✗ Test CSV file missing\n";
}

// Test 2: Check if storage directory exists
echo "\n2. Testing Storage Directory...\n";
$csvImportDir = 'storage/app/csv_import';
if (is_dir($csvImportDir)) {
    echo "   ✓ CSV import directory exists\n";
} else {
    echo "   ✗ CSV import directory missing\n";
    mkdir($csvImportDir, 0755, true);
    echo "   ✓ Created CSV import directory\n";
}

// Test 3: Validate CSV structure
echo "\n3. Testing CSV Structure...\n";
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

// Test 4: Check views
echo "\n4. Testing Views...\n";
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

// Test 5: Check controller file
echo "\n5. Testing Controller...\n";
$controllerFile = 'app/Http/Controllers/Admin/ScheduleController.php';
if (file_exists($controllerFile)) {
    echo "   ✓ ScheduleController exists\n";
    
    // Check for required methods
    $content = file_get_contents($controllerFile);
    $methods = ['importForm', 'parseCsvImport', 'processCsvImport', 'downloadTemplate'];
    
    foreach ($methods as $method) {
        if (strpos($content, "public function {$method}") !== false) {
            echo "   ✓ {$method} method exists\n";
        } else {
            echo "   ✗ {$method} method missing\n";
        }
    }
} else {
    echo "   ✗ ScheduleController missing\n";
}

// Test 6: Check routes file
echo "\n6. Testing Routes...\n";
$routesFile = 'routes/web.php';
if (file_exists($routesFile)) {
    echo "   ✓ Routes file exists\n";
    
    $content = file_get_contents($routesFile);
    $routePatterns = [
        'schedules/import' => 'Import route',
        'schedules/import/parse' => 'Parse route',
        'schedules/import/process' => 'Process route',
        'schedules/import/template' => 'Template route'
    ];
    
    foreach ($routePatterns as $pattern => $description) {
        if (strpos($content, $pattern) !== false) {
            echo "   ✓ {$description} exists\n";
        } else {
            echo "   ✗ {$description} missing\n";
        }
    }
} else {
    echo "   ✗ Routes file missing\n";
}

// Test 7: Check SpreadsheetReader package
echo "\n7. Testing SpreadsheetReader Package...\n";
$vendorFile = 'vendor/nuovo/spreadsheet-reader/SpreadsheetReader.php';
if (file_exists($vendorFile)) {
    echo "   ✓ SpreadsheetReader package installed\n";
} else {
    echo "   ✗ SpreadsheetReader package not found\n";
}

echo "\n=== Test Complete ===\n";
echo "To test the full system:\n";
echo "1. Start the server: php artisan serve --port=8008\n";
echo "2. Visit: http://localhost:8008/admin/schedules/import\n";
echo "3. Upload the test_schedules_import.csv file\n";
echo "4. Follow the import process\n"; 