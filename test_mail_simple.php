<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    echo "Testing mail configuration...\n";
    
    // Test 1: Check mail config
    $mailConfig = config('mail');
    echo "✓ Mail driver: " . $mailConfig['default'] . "\n";
    echo "✓ Mail from address: " . $mailConfig['from']['address'] . "\n";
    
    // Test 2: Try to send a simple mail
    $user = \App\Models\User::first();
    if ($user) {
        echo "✓ Testing with user: " . $user->email . "\n";
        
        // Test the notification directly
        $notification = new \App\Notifications\ForgotPasswordNotification('test-token-123');
        
        try {
            $user->notify($notification);
            echo "✓ Notification sent successfully!\n";
        } catch (Exception $e) {
            echo "✗ Notification failed: " . $e->getMessage() . "\n";
            echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
        }
    } else {
        echo "✗ No users found in database\n";
    }
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} 