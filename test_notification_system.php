<?php

require_once 'vendor/autoload.php';

use App\Models\Booking;
use App\Models\Payment;
use App\Models\User;
use App\Models\Schedule;
use App\Models\Child;
use Illuminate\Support\Facades\Log;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Testing Notification System...\n";

try {
    // Get a test user
    $user = User::where('email', 'parent@example.com')->first();
    if (!$user) {
        echo "Test user not found. Creating one...\n";
        $user = User::create([
            'name' => 'Test Parent',
            'email' => 'parent@example.com',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
            'member_id' => 'GYM-2024-0001'
        ]);
    }

    // Get a test schedule
    $schedule = Schedule::first();
    if (!$schedule) {
        echo "No schedules found. Please create a schedule first.\n";
        exit(1);
    }

    // Get or create a test child
    $child = Child::where('user_id', $user->id)->first();
    if (!$child) {
        echo "Test child not found. Creating one...\n";
        $child = Child::create([
            'user_id' => $user->id,
            'name' => 'Test Child',
            'date_of_birth' => '2020-01-01',
            'gender' => 'male'
        ]);
    }

    echo "Testing Booking Creation Event...\n";
    
    // Create a test booking (this should trigger BookingCreated event)
    $booking = Booking::create([
        'user_id' => $user->id,
        'schedule_id' => $schedule->id,
        'child_id' => $child->id,
        'status' => 'pending',
        'payment_status' => 'pending',
        'is_paid' => false
    ]);

    echo "Booking created with ID: " . $booking->id . "\n";
    echo "This should have triggered BookingCreated event and notification.\n";

    echo "\nTesting Payment Creation Event...\n";
    
    // Create a test payment (this should trigger PaymentReceived event)
    $payment = Payment::create([
        'user_id' => $user->id,
        'booking_id' => $booking->id,
        'schedule_id' => $schedule->id,
        'amount' => $schedule->price,
        'payment_method' => 'test',
        'status' => 'paid',
        'transaction_id' => 'test_' . time(),
        'description' => 'Test payment',
        'paid_at' => now()
    ]);

    echo "Payment created with ID: " . $payment->id . "\n";
    echo "This should have triggered PaymentReceived event and notifications.\n";

    echo "\nTesting Booking Status Update Events...\n";
    
    // Update booking status to confirmed (this should trigger BookingConfirmed event)
    $booking->update(['status' => 'confirmed']);
    echo "Booking status updated to confirmed. This should have triggered BookingConfirmed event.\n";

    // Update booking status to cancelled (this should trigger BookingCancelled event)
    $booking->update(['status' => 'cancelled']);
    echo "Booking status updated to cancelled. This should have triggered BookingCancelled event.\n";

    echo "\nNotification system test completed!\n";
    echo "Check your email and the notifications table for results.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} 