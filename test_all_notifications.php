<?php

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\Mail;
use App\Models\User;
use App\Models\Booking;
use App\Models\Payment;
use App\Notifications\{
    BookingConfirmedNotification,
    DataChangeEmailNotification,
    EmergencyNotification,
    ForgotPasswordNotification,
    NewSignupNotification,
    PaymentConfirmedNotification,
    SystemMaintenanceNotification,
    TwoFactorCodeNotification,
    VerifyUserNotification,
    WelcomeBackNotification
};

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Testing basic notifications with Mailpit...\n";

// Get test data
$user = User::first();
$booking = Booking::first();
$payment = Payment::first();

echo "Using user: {$user->email}\n";
echo "Using booking ID: " . ($booking ? $booking->id : 'N/A') . "\n";
echo "Using payment ID: " . ($payment ? $payment->id : 'N/A') . "\n";

$notifications = [
    'DataChangeEmailNotification' => new DataChangeEmailNotification(['action' => 'created', 'model_name' => 'Booking']),
    'EmergencyNotification' => new EmergencyNotification('Test emergency message'),
    'ForgotPasswordNotification' => new ForgotPasswordNotification('test-token-123'),
    'NewSignupNotification' => new NewSignupNotification(),
    'SystemMaintenanceNotification' => new SystemMaintenanceNotification('Test maintenance', now()->addHour()),
    'TwoFactorCodeNotification' => new TwoFactorCodeNotification(),
    'VerifyUserNotification' => new VerifyUserNotification($user),
    'WelcomeBackNotification' => new WelcomeBackNotification($user, 7)
];

// Add notifications that require models (only if they exist)
if ($booking) {
    $notifications['BookingConfirmedNotification'] = new BookingConfirmedNotification($booking);
}

if ($payment) {
    $notifications['PaymentConfirmedNotification'] = new PaymentConfirmedNotification($payment);
}

$successCount = 0;
$errorCount = 0;

echo "\nTesting " . count($notifications) . " notifications...\n\n";

foreach ($notifications as $name => $notification) {
    try {
        echo "Testing {$name}... ";
        $user->notify($notification);
        echo "✅ SUCCESS\n";
        $successCount++;
    } catch (Exception $e) {
        echo "❌ ERROR: " . $e->getMessage() . "\n";
        $errorCount++;
    }
}

echo "\n=== TEST RESULTS ===\n";
echo "✅ Successful: {$successCount}\n";
echo "❌ Failed: {$errorCount}\n";
echo "📧 Total emails sent: {$successCount}\n";
echo "\nCheck Mailpit at http://localhost:8025 to see all emails!\n"; 