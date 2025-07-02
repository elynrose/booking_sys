<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Password Reset Test ===\n\n";

// Check current mail configuration
echo "Current Mail Configuration:\n";
echo "MAIL_MAILER: " . env('MAIL_MAILER') . "\n";
echo "MAIL_FROM_ADDRESS: " . env('MAIL_FROM_ADDRESS') . "\n";
echo "MAIL_FROM_NAME: " . env('MAIL_FROM_NAME') . "\n\n";

// Test password reset for admin user
try {
    $user = \App\Models\User::where('email', 'admin@example.com')->first();
    
    if (!$user) {
        echo "❌ Admin user not found\n";
        exit;
    }
    
    echo "✅ Found user: " . $user->name . " (" . $user->email . ")\n";
    
    // Create password reset token
    $token = \Illuminate\Support\Facades\Password::createToken($user);
    echo "✅ Password reset token created: " . substr($token, 0, 10) . "...\n";
    
    // Send password reset email
    $user->sendPasswordResetNotification($token);
    echo "✅ Password reset email sent successfully\n";
    
    // Check if email was logged
    $logFile = storage_path('logs/laravel.log');
    if (file_exists($logFile)) {
        $logContent = file_get_contents($logFile);
        if (strpos($logContent, 'Password reset email') !== false || strpos($logContent, 'admin@example.com') !== false) {
            echo "✅ Email logged to laravel.log\n";
        } else {
            echo "⚠️  Email might not be logged - check laravel.log manually\n";
        }
    }
    
    echo "\n=== Password Reset URL ===\n";
    echo "Reset URL: " . url('/reset-password?token=' . $token . '&email=' . urlencode($user->email)) . "\n\n";
    
    echo "=== Next Steps ===\n";
    echo "1. Check storage/logs/laravel.log for the email content\n";
    echo "2. Use the reset URL above to test the password reset form\n";
    echo "3. The email content will be in the log file instead of being sent\n\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "=== Test Complete ===\n"; 