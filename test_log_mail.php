<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    echo "Testing log mail driver...\n";
    
    // Temporarily set mail driver to log for testing
    config(['mail.default' => 'log']);
    
    // Test 1: Check mail configuration
    $mailConfig = config('mail');
    echo "✓ Default mailer: " . $mailConfig['default'] . "\n";
    
    // Test 2: Check if mail channel exists
    $logConfig = config('logging');
    if (isset($logConfig['channels']['mail'])) {
        echo "✓ Mail log channel configured\n";
    } else {
        echo "✗ Mail log channel not found\n";
    }
    
    // Test 3: Try to send a test email using log driver
    $user = \App\Models\User::first();
    if ($user) {
        echo "✓ Testing with user: " . $user->email . "\n";
        
        // Clear any existing mail log
        $mailLogPath = storage_path('logs/mail.log');
        if (file_exists($mailLogPath)) {
            unlink($mailLogPath);
            echo "✓ Cleared existing mail log\n";
        }
        
        try {
            // Send a test notification
            $user->notify(new \Illuminate\Auth\Notifications\ResetPassword('test-token'));
            echo "✓ Mail sent successfully\n";
            
            // Check if mail log file was created
            if (file_exists($mailLogPath)) {
                echo "✓ Mail log file created: " . $mailLogPath . "\n";
                $logContent = file_get_contents($mailLogPath);
                echo "✓ Mail log content length: " . strlen($logContent) . " bytes\n";
                
                // Show a preview of the log content
                $lines = explode("\n", $logContent);
                $previewLines = array_slice($lines, 0, 5);
                echo "✓ Mail log preview:\n";
                foreach ($previewLines as $line) {
                    if (trim($line)) {
                        echo "  " . $line . "\n";
                    }
                }
            } else {
                echo "✗ Mail log file not found\n";
            }
            
        } catch (Exception $e) {
            echo "✗ Mail failed: " . $e->getMessage() . "\n";
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