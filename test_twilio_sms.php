<?php

require_once 'vendor/autoload.php';

use Twilio\Rest\Client;

// Load Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== TWILIO SMS CONFIGURATION TEST ===\n\n";

// Check configuration
$sid = config('services.twilio.sid');
$authToken = config('services.twilio.auth_token');
$phoneNumber = config('services.twilio.phone_number');

echo "Current Twilio Configuration:\n";
echo "- SID: " . ($sid ? 'SET' : 'NOT SET') . "\n";
echo "- Auth Token: " . ($authToken ? 'SET' : 'NOT SET') . "\n";
echo "- Phone Number: " . ($phoneNumber ?: 'NOT SET') . "\n\n";

if (!$sid || !$authToken || !$phoneNumber) {
    echo "❌ Twilio configuration incomplete!\n";
    echo "Please set the following in your .env file:\n";
    echo "TWILIO_SID=your_twilio_sid\n";
    echo "TWILIO_AUTH_TOKEN=your_twilio_auth_token\n";
    echo "TWILIO_PHONE_NUMBER=your_twilio_phone_number\n";
    exit(1);
}

echo "✅ Twilio configuration looks good!\n\n";

// Test Twilio client
try {
    $client = new Client($sid, $authToken);
    echo "✅ Twilio client created successfully!\n\n";
} catch (Exception $e) {
    echo "❌ Failed to create Twilio client: " . $e->getMessage() . "\n";
    exit(1);
}

// Test SMS sending (optional)
if (isset($argv[1])) {
    $testPhone = $argv[1];
    echo "=== TESTING SMS SENDING ===\n";
    echo "Sending test SMS to: $testPhone\n";
    
    try {
        $message = $client->messages->create(
            $testPhone,
            [
                'from' => $phoneNumber,
                'body' => 'Test SMS from Greenstreet Gym App! This is a test message to verify Twilio SMS integration.'
            ]
        );
        
        echo "✅ Test SMS sent successfully!\n";
        echo "Message SID: " . $message->sid . "\n";
    } catch (Exception $e) {
        echo "❌ Failed to send test SMS: " . $e->getMessage() . "\n";
    }
} else {
    echo "=== SMS TESTING ===\n";
    echo "To test SMS sending, run:\n";
    echo "php test_twilio_sms.php +1234567890\n\n";
}

echo "=== NEXT STEPS ===\n";
echo "1. If tests pass, your Twilio SMS is ready!\n";
echo "2. Users can enable SMS notifications in their profile settings\n";
echo "3. SMS notifications will be sent for bookings, payments, and reminders\n";
echo "4. Monitor Twilio dashboard for delivery rates and costs\n"; 