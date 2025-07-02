<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Cloud Mail Configuration Fix ===\n\n";

// Check current configuration
echo "Current Configuration:\n";
echo "MAIL_MAILER: " . env('MAIL_MAILER') . "\n";
echo "MAIL_FROM_ADDRESS: " . env('MAIL_FROM_ADDRESS') . "\n";
echo "MAIL_FROM_NAME: " . env('MAIL_FROM_NAME') . "\n\n";

// Force set mail configuration for cloud
echo "Setting mail configuration for cloud...\n";

// Set mail configuration to log for cloud deployment
putenv('MAIL_MAILER=log');
putenv('MAIL_FROM_ADDRESS=info@lyqid.com');
putenv('MAIL_FROM_NAME="Greenstreet"');

// Clear and recache configuration
echo "Clearing configuration cache...\n";
\Illuminate\Support\Facades\Artisan::call('config:clear');
\Illuminate\Support\Facades\Artisan::call('config:cache');

echo "Configuration updated:\n";
echo "MAIL_MAILER: " . env('MAIL_MAILER') . "\n";
echo "MAIL_FROM_ADDRESS: " . env('MAIL_FROM_ADDRESS') . "\n";
echo "MAIL_FROM_NAME: " . env('MAIL_FROM_NAME') . "\n\n";

// Test password reset
echo "Testing password reset...\n";
try {
    $user = \App\Models\User::where('email', 'admin@example.com')->first();
    
    if ($user) {
        echo "✅ Found user: " . $user->name . "\n";
        
        // Create token
        $token = \Illuminate\Support\Facades\Password::createToken($user);
        echo "✅ Token created\n";
        
        // Send email
        $user->sendPasswordResetNotification($token);
        echo "✅ Password reset email sent (logged)\n";
        
        echo "\nReset URL: " . url('/reset-password?token=' . $token . '&email=' . urlencode($user->email)) . "\n";
        
    } else {
        echo "❌ Admin user not found\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n=== Fix Complete ===\n";
echo "The mail configuration has been set to 'log' for cloud deployment.\n";
echo "Password reset emails will be logged to storage/logs/laravel.log\n";
echo "This should resolve the 500 error on password reset.\n"; 