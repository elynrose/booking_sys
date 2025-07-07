<?php

/**
 * Simple Upload Test
 * This script tests the upload functionality without database dependencies
 */

echo "=== Simple Upload Test ===\n\n";

// Test 1: Check if the import form exists
echo "1. Checking import form...\n";
$importFormPath = 'resources/views/admin/schedules/import.blade.php';
if (file_exists($importFormPath)) {
    echo "   ✅ Import form exists\n";
    
    // Check form action
    $formContent = file_get_contents($importFormPath);
    if (strpos($formContent, 'admin.schedules.process-csv') !== false) {
        echo "   ✅ Form action is correct\n";
    } else {
        echo "   ❌ Form action is incorrect\n";
    }
    
    // Check file input
    if (strpos($formContent, 'name="csv_file"') !== false) {
        echo "   ✅ File input exists\n";
    } else {
        echo "   ❌ File input missing\n";
    }
    
    // Check enctype
    if (strpos($formContent, 'enctype="multipart/form-data"') !== false) {
        echo "   ✅ Form has correct enctype\n";
    } else {
        echo "   ❌ Form missing enctype\n";
    }
} else {
    echo "   ❌ Import form not found\n";
}

// Test 2: Check if the route exists
echo "\n2. Checking routes...\n";
$routesPath = 'routes/web.php';
if (file_exists($routesPath)) {
    $routesContent = file_get_contents($routesPath);
    if (strpos($routesContent, 'schedules.process-csv') !== false) {
        echo "   ✅ Process CSV route exists\n";
    } else {
        echo "   ❌ Process CSV route missing\n";
    }
    
    if (strpos($routesContent, 'schedules.import') !== false) {
        echo "   ✅ Import form route exists\n";
    } else {
        echo "   ❌ Import form route missing\n";
    }
} else {
    echo "   ❌ Routes file not found\n";
}

// Test 3: Check if the controller method exists
echo "\n3. Checking controller...\n";
$controllerPath = 'app/Http/Controllers/Admin/ScheduleController.php';
if (file_exists($controllerPath)) {
    $controllerContent = file_get_contents($controllerPath);
    if (strpos($controllerContent, 'processCsvImport') !== false) {
        echo "   ✅ processCsvImport method exists\n";
    } else {
        echo "   ❌ processCsvImport method missing\n";
    }
    
    if (strpos($controllerContent, 'importForm') !== false) {
        echo "   ✅ importForm method exists\n";
    } else {
        echo "   ❌ importForm method missing\n";
    }
} else {
    echo "   ❌ ScheduleController not found\n";
}

// Test 4: Check if test CSV file exists
echo "\n4. Checking test files...\n";
$csvFiles = [
    'test_schedules_laravel_excel.csv',
    'test_schedules.csv'
];

foreach ($csvFiles as $file) {
    if (file_exists($file)) {
        echo "   ✅ $file exists (" . filesize($file) . " bytes)\n";
    } else {
        echo "   ❌ $file not found\n";
    }
}

// Test 5: Check storage directories
echo "\n5. Checking storage...\n";
$storageDirs = [
    'storage/app',
    'storage/app/temp_imports',
    'storage/app/csv_import'
];

foreach ($storageDirs as $dir) {
    if (is_dir($dir)) {
        echo "   ✅ $dir exists\n";
        if (is_writable($dir)) {
            echo "   ✅ $dir is writable\n";
        } else {
            echo "   ❌ $dir is not writable\n";
        }
    } else {
        echo "   ❌ $dir not found\n";
        // Try to create it
        if (mkdir($dir, 0755, true)) {
            echo "   ✅ Created $dir\n";
        } else {
            echo "   ❌ Failed to create $dir\n";
        }
    }
}

echo "\n=== Upload Issue Diagnosis ===\n";
echo "Based on the tests above, here are the most likely issues:\n\n";

echo "1. **Form Submission Issue**:\n";
echo "   - Check browser console for JavaScript errors\n";
echo "   - Check if the form is actually submitting\n";
echo "   - Verify the file is being uploaded\n\n";

echo "2. **Server Configuration**:\n";
echo "   - Check PHP upload limits in php.ini\n";
echo "   - Check if file uploads are enabled\n";
echo "   - Check if the server can handle the file size\n\n";

echo "3. **Laravel Excel Issue**:\n";
echo "   - The SchedulesImport class might have validation errors\n";
echo "   - The CSV format might not match expectations\n";
echo "   - Database connection issues during import\n\n";

echo "4. **Debugging Steps**:\n";
echo "   - Add error logging to the controller\n";
echo "   - Check Laravel logs: tail -f storage/logs/laravel.log\n";
echo "   - Test with a smaller CSV file\n";
echo "   - Check if the import is actually running\n\n";

echo "5. **Quick Fix**:\n";
echo "   - Try uploading a smaller file first\n";
echo "   - Check if the form shows any validation errors\n";
echo "   - Verify the file format matches the expected headers\n"; 