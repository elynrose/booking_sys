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

// Disable foreign key checks for PostgreSQL
DB::statement('SET session_replication_role = replica;');

$totalInserted = 0;
foreach ($exportData as $tableName => $tableData) {
    if ($tableName === 'migrations') {
        echo "Skipping migrations table...\n";
        continue;
    }
    echo "Truncating and importing table: {$tableName}\n";
    try {
        DB::table($tableName)->truncate();
    } catch (Exception $e) {
        echo "Warning: Could not truncate {$tableName}: " . $e->getMessage() . "\n";
        // Try delete fallback
        DB::table($tableName)->delete();
    }
    $rows = $tableData['data'];
    $count = 0;
    foreach ($rows as $row) {
        // Convert stdClass to array if needed
        $row = (array) $row;
        // Remove any keys not in the current table (for schema drift)
        $columns = Schema::getColumnListing($tableName);
        $row = array_intersect_key($row, array_flip($columns));
        DB::table($tableName)->insert($row);
        $count++;
    }
    echo "Inserted {$count} rows into {$tableName}\n";
    $totalInserted += $count;
}

// Re-enable foreign key checks
DB::statement('SET session_replication_role = DEFAULT;');

echo "Import completed! Total rows inserted: {$totalInserted}\n"; 