<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Schedule;
use Illuminate\Support\Facades\DB;

echo "Testing Location Field Saving\n";
echo "============================\n\n";

// Check if location column exists
echo "1. Checking database column...\n";
$columns = DB::select("SELECT column_name FROM information_schema.columns WHERE table_name = 'schedules' AND column_name = 'location'");
if (count($columns) > 0) {
    echo "✅ Location column exists in database\n";
} else {
    echo "❌ Location column does NOT exist in database\n";
}

// Check Schedule model fillable
echo "\n2. Checking Schedule model fillable array...\n";
$schedule = new Schedule();
$fillable = $schedule->getFillable();
if (in_array('location', $fillable)) {
    echo "✅ Location is in fillable array\n";
} else {
    echo "❌ Location is NOT in fillable array\n";
    echo "Current fillable: " . implode(', ', $fillable) . "\n";
}

// Test updating a schedule with location
echo "\n3. Testing location update...\n";
$testSchedule = Schedule::first();
if ($testSchedule) {
    echo "Testing with Schedule #{$testSchedule->id}: {$testSchedule->title}\n";
    echo "Current location: " . ($testSchedule->location ?? 'NULL') . "\n";
    
    try {
        $testSchedule->update(['location' => 'Test Location - ' . now()]);
        echo "✅ Location updated successfully\n";
        echo "New location: " . ($testSchedule->fresh()->location ?? 'NULL') . "\n";
        
        // Reset to NULL
        $testSchedule->update(['location' => null]);
        echo "✅ Location reset to NULL\n";
        
    } catch (Exception $e) {
        echo "❌ Error updating location: " . $e->getMessage() . "\n";
    }
} else {
    echo "❌ No schedules found to test with\n";
}

// Check database table structure
echo "\n4. Checking schedules table structure...\n";
$tableStructure = DB::select("SELECT column_name, data_type, is_nullable FROM information_schema.columns WHERE table_name = 'schedules' AND column_name = 'location'");
if (count($tableStructure) > 0) {
    $column = $tableStructure[0];
    echo "Location column details:\n";
    echo "- Column name: {$column->column_name}\n";
    echo "- Data type: {$column->data_type}\n";
    echo "- Nullable: {$column->is_nullable}\n";
} else {
    echo "❌ Location column not found in table structure\n";
}

echo "\nTest completed!\n"; 