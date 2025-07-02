<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Mailgun Configuration Test ===\n\n";

// Check environment variables
echo "Environment Variables:\n";
echo "MAIL_MAILER: " . env('MAIL_MAILER') . "\n";
echo "MAILGUN_DOMAIN: " . env('MAILGUN_DOMAIN') . "\n";
echo "MAILGUN_SECRET: " . (env('MAILGUN_SECRET') ? 'SET' : 'NOT SET') . "\n";
echo "MAIL_FROM_ADDRESS: " . env('MAIL_FROM_ADDRESS') . "\n";
echo "MAIL_FROM_NAME: " . env('MAIL_FROM_NAME') . "\n\n";

// Check mail configuration
echo "Mail Configuration:\n";
$mailConfig = config('mail');
echo "Default Mailer: " . $mailConfig['default'] . "\n";
echo "From Address: " . $mailConfig['from']['address'] . "\n";
echo "From Name: " . $mailConfig['from']['name'] . "\n\n";

// Check services configuration
echo "Services Configuration:\n";
$servicesConfig = config('services.mailgun');
echo "Mailgun Domain: " . $servicesConfig['domain'] . "\n";
echo "Mailgun Secret: " . ($servicesConfig['secret'] ? 'SET' : 'NOT SET') . "\n";
echo "Mailgun Endpoint: " . $servicesConfig['endpoint'] . "\n\n";

// Test mailer creation
echo "Testing Mailer Creation:\n";
try {
    $mailer = app('mailer');
    echo "✓ Mailer created successfully\n";
    
    // Test sending a simple email
    echo "\nTesting Email Send:\n";
    try {
        $result = Mail::raw('Test email from Laravel', function($message) {
            $message->to('test@example.com')
                   ->subject('Test Email');
        });
        echo "✓ Email sent successfully\n";
    } catch (Exception $e) {
        echo "✗ Email send failed: " . $e->getMessage() . "\n";
    }
    
} catch (Exception $e) {
    echo "✗ Mailer creation failed: " . $e->getMessage() . "\n";
}

// Test password reset token creation
echo "\nTesting Password Reset Token:\n";
try {
    $user = \App\Models\User::where('email', 'admin@example.com')->first();
    if ($user) {
        $token = \Illuminate\Support\Facades\Password::createToken($user);
        echo "✓ Password reset token created: " . substr($token, 0, 10) . "...\n";
        
        // Test password reset email
        try {
            $user->sendPasswordResetNotification($token);
            echo "✓ Password reset email sent successfully\n";
        } catch (Exception $e) {
            echo "✗ Password reset email failed: " . $e->getMessage() . "\n";
        }
    } else {
        echo "✗ Admin user not found\n";
    }
} catch (Exception $e) {
    echo "✗ Password reset token creation failed: " . $e->getMessage() . "\n";
}

echo "\n=== Test Complete ===\n"; 