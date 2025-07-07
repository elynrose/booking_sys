<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🔍 Admin Home Debug Test\n";
echo "=======================\n\n";

// Test 1: Basic database connection
echo "1. Testing database connection...\n";
try {
    $pdo = DB::connection()->getPdo();
    echo "   ✅ Database connection working\n";
} catch (Exception $e) {
    echo "   ❌ Database connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 2: Check if models exist and are accessible
echo "\n2. Testing model accessibility...\n";
$models = ['Booking', 'Payment', 'User', 'Trainer', 'Schedule', 'Category', 'Recommendation'];
foreach ($models as $model) {
    try {
        $count = "App\\Models\\$model"::count();
        echo "   ✅ $model model working (count: $count)\n";
    } catch (Exception $e) {
        echo "   ❌ $model model failed: " . $e->getMessage() . "\n";
    }
}

// Test 3: Test basic queries from HomeController
echo "\n3. Testing HomeController queries...\n";

// Test total bookings
try {
    $totalBookings = App\Models\Booking::count();
    echo "   ✅ Total bookings query: $totalBookings\n";
} catch (Exception $e) {
    echo "   ❌ Total bookings query failed: " . $e->getMessage() . "\n";
}

// Test payment sum
try {
    $totalRevenue = App\Models\Payment::where('payments.status', 'paid')->sum('amount') ?: 0;
    echo "   ✅ Total revenue query: $totalRevenue\n";
} catch (Exception $e) {
    echo "   ❌ Total revenue query failed: " . $e->getMessage() . "\n";
}

// Test complex join query
try {
    $potentialRevenue = App\Models\Payment::where('payments.status', 'paid')
        ->join('bookings', 'payments.booking_id', '=', 'bookings.id')
        ->join('schedules', 'bookings.schedule_id', '=', 'schedules.id')
        ->sum(DB::raw('schedules.price'));
    echo "   ✅ Complex join query: $potentialRevenue\n";
} catch (Exception $e) {
    echo "   ❌ Complex join query failed: " . $e->getMessage() . "\n";
}

// Test 4: Check permissions
echo "\n4. Testing permissions...\n";
try {
    $adminUser = App\Models\User::where('email', 'admin@admin.com')->first();
    if ($adminUser) {
        echo "   ✅ Admin user found\n";
        $hasPermission = $adminUser->can('dashboard_access');
        echo "   ✅ Dashboard access permission: " . ($hasPermission ? 'YES' : 'NO') . "\n";
    } else {
        echo "   ❌ Admin user not found\n";
    }
} catch (Exception $e) {
    echo "   ❌ Permission check failed: " . $e->getMessage() . "\n";
}

// Test 5: Test view rendering
echo "\n5. Testing view rendering...\n";
try {
    $view = view('admin.dashboard');
    echo "   ✅ Admin dashboard view exists\n";
} catch (Exception $e) {
    echo "   ❌ Admin dashboard view failed: " . $e->getMessage() . "\n";
}

// Test 6: Simulate HomeController logic
echo "\n6. Testing HomeController logic...\n";
try {
    $startDate = Carbon\Carbon::now()->subDays(30)->format('Y-m-d');
    $endDate = Carbon\Carbon::now()->format('Y-m-d');
    
    $startDate = Carbon\Carbon::parse($startDate)->startOfDay();
    $endDate = Carbon\Carbon::parse($endDate)->endOfDay();
    
    echo "   ✅ Date range: {$startDate->format('Y-m-d')} to {$endDate->format('Y-m-d')}\n";
    
    // Test recent recommendations query
    $recentRecommendations = App\Models\Recommendation::with(['child', 'trainer', 'responses'])
        ->latest()
        ->take(5)
        ->get();
    echo "   ✅ Recent recommendations query: " . $recentRecommendations->count() . " results\n";
    
} catch (Exception $e) {
    echo "   ❌ HomeController logic failed: " . $e->getMessage() . "\n";
}

echo "\n✅ Debug test completed!\n";
echo "\n📋 Summary:\n";
echo "- If all tests pass, the issue might be environment-specific\n";
echo "- Check cloud logs for specific error messages\n";
echo "- Run this script on the cloud server to compare results\n"; 