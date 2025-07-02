<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    echo "Testing mail failover configuration...\n";
    
    // Test 1: Check mail configuration
    $mailConfig = config('mail');
    echo "✓ Default mailer: " . $mailConfig['default'] . "\n";
    echo "✓ Failover mailers: " . implode(', ', $mailConfig['mailers']['failover']['mailers']) . "\n";
    
    // Test 2: Check if mail channel exists
    $logConfig = config('logging');
    if (isset($logConfig['channels']['mail'])) {
        echo "✓ Mail log channel configured\n";
    } else {
        echo "✗ Mail log channel not found\n";
    }
    
    // Test 3: Try to send a test email
    $user = \App\Models\User::first();
    if ($user) {
        echo "✓ Testing with user: " . $user->email . "\n";
        
        try {
            // Send a test notification
            $user->notify(new \Illuminate\Auth\Notifications\ResetPassword('test-token'));
            echo "✓ Mail sent successfully (check logs for details)\n";
            
            // Check if mail log file was created
            $mailLogPath = storage_path('logs/mail.log');
            if (file_exists($mailLogPath)) {
                echo "✓ Mail log file created: " . $mailLogPath . "\n";
                $logContent = file_get_contents($mailLogPath);
                echo "✓ Mail log content length: " . strlen($logContent) . " bytes\n";
            } else {
                echo "⚠ Mail log file not found (may be using SES)\n";
            }
            
        } catch (Exception $e) {
            echo "✗ Mail failed: " . $e->getMessage() . "\n";
            echo "This is expected if SES credentials are not configured\n";
        }
    } else {
        echo "✗ No users found\n";
    }
    
    // Test 4: Check environment
    echo "✓ Environment: " . app()->environment() . "\n";
    
} catch (Exception $e) {
    echo "✗ Fatal error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} 