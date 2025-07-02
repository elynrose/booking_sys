<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    echo "Testing password reset controller process...\n";
    
    // Simulate the password reset email request
    $email = 'smith.darrell@example.com';
    
    // Test 1: Check if user exists
    $user = \App\Models\User::where('email', $email)->first();
    if (!$user) {
        echo "✗ User not found: " . $email . "\n";
        exit(1);
    }
    echo "✓ User found: " . $user->email . "\n";
    
    // Test 2: Check if user is verified (some systems require this)
    if (!$user->email_verified_at) {
        echo "⚠ User email not verified\n";
    } else {
        echo "✓ User email verified\n";
    }
    
    // Test 3: Generate reset token
    $token = \Illuminate\Support\Facades\Password::createToken($user);
    echo "✓ Reset token generated: " . substr($token, 0, 10) . "...\n";
    
    // Test 4: Check if token was saved
    $tokenRecord = \Illuminate\Support\Facades\DB::table('password_reset_tokens')
        ->where('email', $email)
        ->first();
    if ($tokenRecord) {
        echo "✓ Token saved to database\n";
    } else {
        echo "✗ Token not saved to database\n";
    }
    
    // Test 5: Try to send notification
    try {
        $user->sendPasswordResetNotification($token);
        echo "✓ Notification sent successfully\n";
    } catch (Exception $e) {
        echo "✗ Notification failed: " . $e->getMessage() . "\n";
        echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    }
    
    // Test 6: Check mail configuration
    $mailConfig = config('mail');
    echo "✓ Mail driver: " . $mailConfig['default'] . "\n";
    echo "✓ Mail from: " . $mailConfig['from']['address'] . "\n";
    
    // Test 7: Check environment
    echo "✓ Environment: " . app()->environment() . "\n";
    
    // Test 8: Check if we're in production
    if (app()->environment('production')) {
        echo "✓ Running in production mode\n";
        // Check SES configuration
        $sesConfig = config('services.ses');
        if ($sesConfig) {
            echo "✓ SES region: " . ($sesConfig['region'] ?? 'not set') . "\n";
            echo "✓ SES key: " . (isset($sesConfig['key']) ? 'set' : 'not set') . "\n";
        } else {
            echo "⚠ SES config not found\n";
        }
    }
    
} catch (Exception $e) {
    echo "✗ Fatal error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} 