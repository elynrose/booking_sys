<?php

require_once 'vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

// Bootstrap Laravel
$app = Application::configure(basePath: __DIR__)
    ->withRouting(
        web: __DIR__.'/routes/web.php',
        commands: __DIR__.'/routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();

$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Testing Trainer Class Details Page...\n";

try {
    // Test 1: Check if schedule ID 5 exists
    echo "\n1. Checking if Schedule ID 5 exists...\n";
    $schedule = \App\Models\Schedule::find(5);
    
    if (!$schedule) {
        echo "ERROR: Schedule ID 5 does not exist!\n";
        exit(1);
    }
    
    echo "✓ Schedule found: {$schedule->title}\n";
    echo "  - Trainer ID: {$schedule->trainer_id}\n";
    echo "  - Status: {$schedule->status}\n";
    
    // Test 2: Check if trainer exists
    echo "\n2. Checking if trainer exists...\n";
    $trainer = \App\Models\Trainer::find($schedule->trainer_id);
    
    if (!$trainer) {
        echo "ERROR: Trainer not found for schedule!\n";
        exit(1);
    }
    
    echo "✓ Trainer found: {$trainer->name}\n";
    echo "  - User ID: {$trainer->user_id}\n";
    
    // Test 3: Check if user exists
    echo "\n3. Checking if user exists...\n";
    $user = \App\Models\User::find($trainer->user_id);
    
    if (!$user) {
        echo "ERROR: User not found for trainer!\n";
        exit(1);
    }
    
    echo "✓ User found: {$user->name} ({$user->email})\n";
    
    // Test 4: Test the bookings query
    echo "\n4. Testing bookings query...\n";
    $bookings = \App\Models\Booking::where('schedule_id', $schedule->id)
        ->where('status', 'confirmed')
        ->with([
            'user', 
            'child', 
            'checkins' => function($query) {
                $query->orderBy('created_at', 'desc');
            }
        ])
        ->get();
    
    echo "✓ Found {$bookings->count()} confirmed bookings\n";
    
    // Test 5: Test the checkin stats calculation
    echo "\n5. Testing checkin stats calculation...\n";
    $bookingsWithStats = $bookings->map(function($booking) {
        $checkins = $booking->checkins;
        $totalCheckins = $checkins->count();
        $totalCheckouts = $checkins->whereNotNull('checkout_time')->count();
        $currentlyCheckedIn = $checkins->whereNull('checkout_time')->count();

        $booking->checkin_stats = [
            'total_checkins' => $totalCheckins,
            'total_checkouts' => $totalCheckouts,
            'currently_checked_in' => $currentlyCheckedIn,
            'last_checkin' => $checkins->first() ? $checkins->first()->created_at : null,
            'last_checkout' => $checkins->whereNotNull('checkout_time')->first() ? 
                $checkins->whereNotNull('checkout_time')->first()->checkout_time : null
        ];

        return $booking;
    });
    
    echo "✓ Successfully calculated checkin stats for {$bookingsWithStats->count()} bookings\n";
    
    // Test 6: Test view rendering
    echo "\n6. Testing view rendering...\n";
    $view = view('frontend.trainer.class-details', [
        'schedule' => $schedule,
        'bookingsWithStats' => $bookingsWithStats
    ]);
    
    $rendered = $view->render();
    echo "✓ View rendered successfully (" . strlen($rendered) . " characters)\n";
    
    echo "\n✅ All tests passed! The trainer class details page should work correctly.\n";
    
} catch (\Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
} 