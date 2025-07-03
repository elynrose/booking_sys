<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

echo "=== EMAIL CONFIGURATION TEST ===\n\n";

// Current configuration
echo "Current Configuration:\n";
echo "- Mail Driver: " . config('mail.default') . "\n";
echo "- From Address: " . config('mail.from.address') . "\n";
echo "- From Name: " . config('mail.from.name') . "\n\n";

// Test different mail configurations
$mailConfigs = [
    'log' => [
        'name' => 'Log Driver (Current)',
        'description' => 'Emails are logged to storage/logs/laravel.log'
    ],
    'smtp' => [
        'name' => 'SMTP',
        'description' => 'Standard SMTP server'
    ],
    'mailgun' => [
        'name' => 'Mailgun',
        'description' => 'Mailgun service'
    ],
    'ses' => [
        'name' => 'Amazon SES',
        'description' => 'Amazon Simple Email Service'
    ]
];

echo "Available Mail Drivers:\n";
foreach ($mailConfigs as $driver => $config) {
    $current = (config('mail.default') === $driver) ? ' (CURRENT)' : '';
    echo "- {$config['name']}{$current}: {$config['description']}\n";
}

echo "\n=== TESTING CURRENT CONFIGURATION ===\n";

try {
    // Test sending a simple email
    Mail::raw('This is a test email from GymApp at ' . now(), function($message) {
        $message->to('test@example.com')
                ->subject('GymApp Email Test')
                ->from(config('mail.from.address'), config('mail.from.name'));
    });
    
    echo "✓ Email test completed successfully\n";
    
    if (config('mail.default') === 'log') {
        echo "📝 Email was logged to: storage/logs/laravel.log\n";
        echo "   Check the log file to see the email content\n";
    } else {
        echo "📧 Email was sent via " . config('mail.default') . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Email test failed: " . $e->getMessage() . "\n";
}

echo "\n=== CONFIGURATION GUIDE ===\n";
echo "To configure email sending, update your .env file:\n\n";

echo "For SMTP:\n";
echo "MAIL_MAILER=smtp\n";
echo "MAIL_HOST=your-smtp-host.com\n";
echo "MAIL_PORT=587\n";
echo "MAIL_USERNAME=your-username\n";
echo "MAIL_PASSWORD=your-password\n";
echo "MAIL_ENCRYPTION=tls\n\n";

echo "For Mailgun:\n";
echo "MAIL_MAILER=mailgun\n";
echo "MAILGUN_DOMAIN=your-domain.com\n";
echo "MAILGUN_SECRET=your-api-key\n\n";

echo "For Amazon SES:\n";
echo "MAIL_MAILER=ses\n";
echo "AWS_ACCESS_KEY_ID=your-access-key\n";
echo "AWS_SECRET_ACCESS_KEY=your-secret-key\n";
echo "AWS_DEFAULT_REGION=us-east-1\n\n";

echo "=== SECURITY NOTE ===\n";
echo "⚠️  Never share API keys or passwords in chat\n";
echo "⚠️  Use environment variables (.env file) for credentials\n";
echo "⚠️  Keep your .env file secure and never commit it to git\n\n";

echo "=== NEXT STEPS ===\n";
echo "1. Update your .env file with your email provider credentials\n";
echo "2. Run: php artisan config:clear\n";
echo "3. Run this script again to test: php test_email_config.php\n";
echo "4. Check storage/logs/laravel.log for any errors\n"; 