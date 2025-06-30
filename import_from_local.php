<?php

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$jsonFile = 'database_export.json';
if (!file_exists($jsonFile)) {
    echo "File {$jsonFile} not found!\n";
    exit(1);
}

$exportData = json_decode(file_get_contents($jsonFile), true);
if (!$exportData) {
    echo "Failed to decode {$jsonFile}\n";
    exit(1);
}

// Check database connection type
$connection = config('database.default');
$isPostgres = $connection === 'pgsql';

if ($isPostgres) {
    echo "Using PostgreSQL - will handle foreign keys differently\n";
    // For PostgreSQL, we'll disable foreign key checks differently
    try {
        DB::statement('SET session_replication_role = replica;');
    } catch (Exception $e) {
        echo "Warning: Could not set session_replication_role (requires superuser): " . $e->getMessage() . "\n";
        echo "Continuing without disabling foreign key checks...\n";
    }
} else {
    // For MySQL, disable foreign key checks
    DB::statement('SET FOREIGN_KEY_CHECKS = 0;');
}

// Define import order based on foreign key dependencies
// Tables with no dependencies come first
$importOrder = [
    'users',
    'roles', 
    'permissions',
    'categories',
    'trainers',
    'schedules',
    'children',
    'bookings',
    'payments',
    'checkins',
    'waitlists',
    'user_alerts',
    'qa_topics',
    'qa_messages',
    'testimonials',
    'sessions'
];

$totalInserted = 0;

// Import tables in dependency order
foreach ($importOrder as $tableName) {
    if (!isset($exportData[$tableName])) {
        echo "Table {$tableName} not found in export data, skipping...\n";
        continue;
    }
    
    $tableData = $exportData[$tableName];
    echo "Truncating and importing table: {$tableName}\n";
    
    try {
        DB::table($tableName)->truncate();
    } catch (Exception $e) {
        echo "Warning: Could not truncate {$tableName}: " . $e->getMessage() . "\n";
        // Try delete fallback
        try {
            DB::table($tableName)->delete();
        } catch (Exception $e2) {
            echo "Warning: Could not delete from {$tableName}: " . $e2->getMessage() . "\n";
            echo "Continuing with insert only...\n";
        }
    }
    
    $rows = $tableData['data'];
    $count = 0;
    $errors = 0;
    
    foreach ($rows as $row) {
        // Convert stdClass to array if needed
        $row = (array) $row;
        // Remove any keys not in the current table (for schema drift)
        $columns = Schema::getColumnListing($tableName);
        $row = array_intersect_key($row, array_flip($columns));
        
        try {
            DB::table($tableName)->insert($row);
            $count++;
        } catch (Exception $e) {
            $errors++;
            if ($errors <= 5) { // Only show first 5 errors to avoid spam
                echo "Warning: Could not insert row into {$tableName}: " . $e->getMessage() . "\n";
            }
        }
    }
    
    echo "Inserted {$count} rows into {$tableName}";
    if ($errors > 0) {
        echo " ({$errors} errors)";
    }
    echo "\n";
    $totalInserted += $count;
}

// Also import any remaining tables not in the predefined order
foreach ($exportData as $tableName => $tableData) {
    if (in_array($tableName, $importOrder) || $tableName === 'migrations') {
        continue; // Already processed or skip migrations
    }
    
    echo "Importing additional table: {$tableName}\n";
    
    try {
        DB::table($tableName)->truncate();
    } catch (Exception $e) {
        echo "Warning: Could not truncate {$tableName}: " . $e->getMessage() . "\n";
        try {
            DB::table($tableName)->delete();
        } catch (Exception $e2) {
            echo "Warning: Could not delete from {$tableName}: " . $e2->getMessage() . "\n";
        }
    }
    
    $rows = $tableData['data'];
    $count = 0;
    $errors = 0;
    
    foreach ($rows as $row) {
        $row = (array) $row;
        $columns = Schema::getColumnListing($tableName);
        $row = array_intersect_key($row, array_flip($columns));
        
        try {
            DB::table($tableName)->insert($row);
            $count++;
        } catch (Exception $e) {
            $errors++;
            if ($errors <= 5) {
                echo "Warning: Could not insert row into {$tableName}: " . $e->getMessage() . "\n";
            }
        }
    }
    
    echo "Inserted {$count} rows into {$tableName}";
    if ($errors > 0) {
        echo " ({$errors} errors)";
    }
    echo "\n";
    $totalInserted += $count;
}

// Re-enable foreign key checks
if ($isPostgres) {
    try {
        DB::statement('SET session_replication_role = DEFAULT;');
    } catch (Exception $e) {
        echo "Warning: Could not reset session_replication_role: " . $e->getMessage() . "\n";
    }
} else {
    DB::statement('SET FOREIGN_KEY_CHECKS = 1;');
}

echo "Import completed! Total rows inserted: {$totalInserted}\n"; 