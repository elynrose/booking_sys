<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Force Log Mailer Fix ===\n\n";

// Check current configuration
echo "Current Configuration:\n";
echo "MAIL_MAILER: " . env('MAIL_MAILER') . "\n";
echo "MAIL_FROM_ADDRESS: " . env('MAIL_FROM_ADDRESS') . "\n";
echo "MAIL_FROM_NAME: " . env('MAIL_FROM_NAME') . "\n\n";

// Force set mail configuration to log
echo "Forcing mail configuration to log...\n";

// Set environment variables
$_ENV['MAIL_MAILER'] = 'log';
$_ENV['MAIL_FROM_ADDRESS'] = 'info@lyqid.com';
$_ENV['MAIL_FROM_NAME'] = 'Greenstreet';

// Also set in putenv for immediate effect
putenv('MAIL_MAILER=log');
putenv('MAIL_FROM_ADDRESS=info@lyqid.com');
putenv('MAIL_FROM_NAME="Greenstreet"');

// Clear all caches
echo "Clearing all caches...\n";
\Illuminate\Support\Facades\Artisan::call('config:clear');
\Illuminate\Support\Facades\Artisan::call('cache:clear');
\Illuminate\Support\Facades\Artisan::call('route:clear');
\Illuminate\Support\Facades\Artisan::call('view:clear');

// Rebuild config cache
echo "Rebuilding config cache...\n";
\Illuminate\Support\Facades\Artisan::call('config:cache');

// Verify the change
echo "\nUpdated Configuration:\n";
echo "MAIL_MAILER: " . env('MAIL_MAILER') . "\n";
echo "MAIL_FROM_ADDRESS: " . env('MAIL_FROM_ADDRESS') . "\n";
echo "MAIL_FROM_NAME: " . env('MAIL_FROM_NAME') . "\n\n";

// Test mailer configuration
echo "Testing mailer configuration...\n";
try {
    $mailConfig = config('mail');
    echo "Config mailer: " . $mailConfig['default'] . "\n";
    echo "Config from: " . $mailConfig['from']['address'] . "\n";
    
    $mailer = app('mailer');
    echo "✅ Mailer created successfully\n";
    
} catch (Exception $e) {
    echo "❌ Mailer error: " . $e->getMessage() . "\n";
}

// Test password reset
echo "\nTesting password reset...\n";
try {
    $user = \App\Models\User::where('email', 'admin@example.com')->first();
    
    if ($user) {
        echo "✅ Found user: " . $user->name . "\n";
        
        // Create token
        $token = \Illuminate\Support\Facades\Password::createToken($user);
        echo "✅ Token created: " . substr($token, 0, 10) . "...\n";
        
        // Send email
        $user->sendPasswordResetNotification($token);
        echo "✅ Password reset email sent (logged)\n";
        
        // Check if email was logged
        $logFile = storage_path('logs/laravel.log');
        if (file_exists($logFile)) {
            $logContent = file_get_contents($logFile);
            if (strpos($logContent, 'admin@example.com') !== false) {
                echo "✅ Email found in laravel.log\n";
            } else {
                echo "⚠️  Email might not be logged - check manually\n";
            }
        }
        
        echo "\nReset URL: " . url('/password/reset/' . $token . '?email=' . urlencode($user->email)) . "\n";
        
    } else {
        echo "❌ Admin user not found\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== Force Log Mailer Complete ===\n";
echo "The mail configuration has been forcefully set to 'log'.\n";
echo "This should resolve the 500 error on password reset.\n";
echo "All emails will be logged to storage/logs/laravel.log\n"; 