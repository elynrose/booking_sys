<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Switch to SMTP Mailer ===\n\n";

// Check current configuration
echo "Current Configuration:\n";
echo "MAIL_MAILER: " . env('MAIL_MAILER') . "\n";
echo "MAIL_HOST: " . env('MAIL_HOST') . "\n";
echo "MAIL_PORT: " . env('MAIL_PORT') . "\n";
echo "MAIL_USERNAME: " . env('MAIL_USERNAME') . "\n";
echo "MAIL_PASSWORD: " . (env('MAIL_PASSWORD') ? 'SET' : 'NOT SET') . "\n";
echo "MAIL_ENCRYPTION: " . env('MAIL_ENCRYPTION') . "\n\n";

// Set SMTP configuration
echo "Setting SMTP configuration...\n";

// Gmail SMTP configuration (you can change these)
$smtpConfig = [
    'MAIL_MAILER' => 'smtp',
    'MAIL_HOST' => 'smtp.gmail.com',
    'MAIL_PORT' => '587',
    'MAIL_USERNAME' => 'your-email@gmail.com', // Change this
    'MAIL_PASSWORD' => 'your-app-password', // Change this
    'MAIL_ENCRYPTION' => 'tls',
    'MAIL_FROM_ADDRESS' => 'your-email@gmail.com', // Change this
    'MAIL_FROM_NAME' => 'Greenstreet'
];

// Update environment variables
foreach ($smtpConfig as $key => $value) {
    putenv("{$key}={$value}");
    $_ENV[$key] = $value;
}

// Clear and rebuild cache
echo "Clearing configuration cache...\n";
\Illuminate\Support\Facades\Artisan::call('config:clear');
\Illuminate\Support\Facades\Artisan::call('config:cache');

// Verify the change
echo "\nUpdated Configuration:\n";
echo "MAIL_MAILER: " . env('MAIL_MAILER') . "\n";
echo "MAIL_HOST: " . env('MAIL_HOST') . "\n";
echo "MAIL_PORT: " . env('MAIL_PORT') . "\n";
echo "MAIL_USERNAME: " . env('MAIL_USERNAME') . "\n";
echo "MAIL_ENCRYPTION: " . env('MAIL_ENCRYPTION') . "\n\n";

// Test SMTP configuration
echo "Testing SMTP configuration...\n";
try {
    $mailer = app('mailer');
    echo "✅ Mailer created successfully\n";
    
    // Test sending a simple email
    Mail::raw('Test email from Laravel SMTP', function($message) {
        $message->to('test@example.com')
               ->subject('Test SMTP Email');
    });
    echo "✅ SMTP email sent successfully\n";
    
} catch (Exception $e) {
    echo "❌ SMTP error: " . $e->getMessage() . "\n";
    
    echo "\n🔧 SMTP Setup Instructions:\n";
    echo "1. For Gmail:\n";
    echo "   - Enable 2-factor authentication\n";
    echo "   - Generate an App Password\n";
    echo "   - Use the App Password as MAIL_PASSWORD\n\n";
    
    echo "2. For other providers:\n";
    echo "   - Check your SMTP settings\n";
    echo "   - Verify username and password\n";
    echo "   - Check if port 587 is open\n\n";
}

echo "\n=== SMTP Setup Complete ===\n";
echo "To use Gmail SMTP:\n";
echo "1. Update the email addresses in this script\n";
echo "2. Generate a Gmail App Password\n";
echo "3. Update your .env file with the SMTP settings\n";
echo "4. Test password reset functionality\n"; 