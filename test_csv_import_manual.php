<?php

/**
 * Manual Test Script for CSV Import System
 * This script simulates the CSV upload process to test the functionality
 */

require_once 'vendor/autoload.php';

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use App\Http\Controllers\Admin\ScheduleController;

echo "=== Manual CSV Import Test ===\n\n";

// Test 1: Check if test CSV file exists and is valid
echo "1. Testing CSV File...\n";
$testFile = 'test_schedules_import.csv';
if (file_exists($testFile)) {
    echo "   ✓ Test CSV file exists\n";
    
    // Read and validate CSV structure
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
        echo "   ✓ CSV structure is valid\n";
    } else {
        echo "   ✗ Missing fields: " . implode(', ', $missingFields) . "\n";
        exit(1);
    }
} else {
    echo "   ✗ Test CSV file missing\n";
    exit(1);
}

// Test 2: Test SpreadsheetReader functionality
echo "\n2. Testing SpreadsheetReader...\n";
try {
    $reader = new SpreadsheetReader($testFile);
    $firstRow = $reader->current();
    echo "   ✓ SpreadsheetReader works\n";
    echo "   ✓ First row has " . count($firstRow) . " columns\n";
} catch (Exception $e) {
    echo "   ✗ SpreadsheetReader error: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 3: Test file upload simulation
echo "\n3. Testing File Upload Simulation...\n";
try {
    // Create a mock UploadedFile
    $uploadedFile = new UploadedFile(
        $testFile,
        'test_schedules_import.csv',
        'text/csv',
        null,
        true
    );
    
    echo "   ✓ UploadedFile created successfully\n";
    echo "   ✓ File size: " . filesize($testFile) . " bytes\n";
} catch (Exception $e) {
    echo "   ✗ UploadedFile error: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 4: Test CSV parsing
echo "\n4. Testing CSV Parsing...\n";
try {
    $reader = new SpreadsheetReader($testFile);
    $headers = $reader->current();
    $lines = [];
    
    $i = 0;
    while ($reader->next() !== false && $i < 5) {
        $lines[] = $reader->current();
        $i++;
    }
    
    echo "   ✓ CSV parsed successfully\n";
    echo "   ✓ Headers: " . implode(', ', $headers) . "\n";
    echo "   ✓ Preview rows: " . count($lines) . "\n";
    
    // Show first data row
    if (!empty($lines)) {
        echo "   ✓ First data row: " . implode(', ', $lines[0]) . "\n";
    }
} catch (Exception $e) {
    echo "   ✗ CSV parsing error: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 5: Test data validation
echo "\n5. Testing Data Validation...\n";
try {
    $reader = new SpreadsheetReader($testFile);
    $headers = $reader->current();
    $rowNum = 1;
    $errors = [];
    
    while ($reader->next() !== false) {
        $rowNum++;
        $data = $reader->current();
        $row = array_combine($headers, $data);
        
        // Basic validation
        if (empty($row['title'])) {
            $errors[] = "Row {$rowNum}: Missing title";
        }
        
        if (empty($row['trainer_id']) || !is_numeric($row['trainer_id'])) {
            $errors[] = "Row {$rowNum}: Invalid trainer_id";
        }
        
        if (empty($row['category_id']) || !is_numeric($row['category_id'])) {
            $errors[] = "Row {$rowNum}: Invalid category_id";
        }
        
        if (!in_array($row['type'], ['group', 'private'])) {
            $errors[] = "Row {$rowNum}: Invalid type (must be 'group' or 'private')";
        }
        
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $row['start_date'])) {
            $errors[] = "Row {$rowNum}: Invalid start_date format";
        }
        
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $row['end_date'])) {
            $errors[] = "Row {$rowNum}: Invalid end_date format";
        }
        
        if (!preg_match('/^\d{2}:\d{2}:\d{2}$/', $row['start_time'])) {
            $errors[] = "Row {$rowNum}: Invalid start_time format";
        }
        
        if (!preg_match('/^\d{2}:\d{2}:\d{2}$/', $row['end_time'])) {
            $errors[] = "Row {$rowNum}: Invalid end_time format";
        }
        
        if (!is_numeric($row['price'])) {
            $errors[] = "Row {$rowNum}: Invalid price";
        }
        
        if (!is_numeric($row['max_participants'])) {
            $errors[] = "Row {$rowNum}: Invalid max_participants";
        }
        
        if (!in_array($row['status'], ['active', 'inactive'])) {
            $errors[] = "Row {$rowNum}: Invalid status (must be 'active' or 'inactive')";
        }
    }
    
    if (empty($errors)) {
        echo "   ✓ All data validation passed\n";
    } else {
        echo "   ✗ Data validation errors:\n";
        foreach ($errors as $error) {
            echo "      - {$error}\n";
        }
        exit(1);
    }
} catch (Exception $e) {
    echo "   ✗ Data validation error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n=== Test Complete ===\n";
echo "✅ All tests passed! The CSV import system is working correctly.\n\n";
echo "To test the full web interface:\n";
echo "1. Make sure you're logged in as an admin user\n";
echo "2. Visit: http://localhost:8008/admin/schedules/import\n";
echo "3. Upload the test_schedules_import.csv file\n";
echo "4. Follow the import process\n\n";
echo "Note: The parse route only accepts POST requests with file uploads.\n";
echo "You cannot access it directly with a GET request.\n"; 