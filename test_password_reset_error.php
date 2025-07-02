<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    echo "Testing password reset process...\n";
    
    // Test 1: Find a user
    $user = \App\Models\User::first();
    if (!$user) {
        echo "✗ No users found\n";
        exit(1);
    }
    echo "✓ Found user: " . $user->email . "\n";
    
    // Test 2: Generate a password reset token
    $token = \Illuminate\Support\Facades\Password::createToken($user);
    echo "✓ Generated token: " . substr($token, 0, 10) . "...\n";
    
    // Test 3: Try to send the notification
    try {
        $user->sendPasswordResetNotification($token);
        echo "✓ Password reset notification sent successfully!\n";
    } catch (Exception $e) {
        echo "✗ Error sending notification: " . $e->getMessage() . "\n";
        echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    }
    
    // Test 4: Check mail configuration
    $mailConfig = config('mail');
    echo "✓ Mail driver: " . $mailConfig['default'] . "\n";
    echo "✓ Mail from: " . $mailConfig['from']['address'] . "\n";
    
    // Test 5: Check if SES is configured
    if ($mailConfig['default'] === 'ses') {
        $sesConfig = config('services.ses');
        echo "✓ SES region: " . ($sesConfig['region'] ?? 'not set') . "\n";
        echo "✓ SES key: " . (isset($sesConfig['key']) ? 'set' : 'not set') . "\n";
    }
    
} catch (Exception $e) {
    echo "✗ Fatal error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} 