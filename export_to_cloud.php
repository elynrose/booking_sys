<?php

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Starting database export...\n";

// Get all table names
$tables = DB::select('SHOW TABLES');
$tableNames = [];
foreach ($tables as $table) {
    $tableNames[] = array_values((array) $table)[0];
}

$exportData = [];

foreach ($tableNames as $tableName) {
    echo "Exporting table: {$tableName}\n";
    
    // Get table structure
    $columns = Schema::getColumnListing($tableName);
    
    // Get all data
    $data = DB::table($tableName)->get();
    
    $exportData[$tableName] = [
        'columns' => $columns,
        'data' => $data->toArray()
    ];
}

// Save to JSON file
$jsonFile = 'database_export.json';
file_put_contents($jsonFile, json_encode($exportData, JSON_PRETTY_PRINT));

echo "Database exported to {$jsonFile}\n";
echo "Total tables exported: " . count($tableNames) . "\n";
echo "Total records: " . array_sum(array_map(function($table) {
    return count($table['data']);
}, $exportData)) . "\n";

// Also create a SQL dump for reference
$sqlFile = 'database_dump.sql';
$sql = "-- Database Export\n";
$sql .= "-- Generated: " . date('Y-m-d H:i:s') . "\n\n";

foreach ($exportData as $tableName => $tableData) {
    $sql .= "-- Table: {$tableName}\n";
    $sql .= "CREATE TABLE IF NOT EXISTS `{$tableName}` (\n";
    
    // This is a simplified structure - in practice you'd want the actual CREATE TABLE statements
    $sql .= "  -- Table structure would go here\n";
    $sql .= ");\n\n";
    
    if (!empty($tableData['data'])) {
        $sql .= "-- Data for {$tableName}\n";
        foreach ($tableData['data'] as $row) {
            $values = array_map(function($value) {
                if (is_null($value)) return 'NULL';
                if (is_string($value)) return "'" . addslashes($value) . "'";
                return $value;
            }, (array) $row);
            
            $sql .= "INSERT INTO `{$tableName}` (" . implode(', ', array_keys($values)) . ") VALUES (" . implode(', ', $values) . ");\n";
        }
        $sql .= "\n";
    }
}

file_put_contents($sqlFile, $sql);
echo "SQL dump created: {$sqlFile}\n";
echo "Export completed successfully!\n"; 