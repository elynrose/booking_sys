<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Mailgun Setup Fix ===\n\n";

// Current configuration
echo "Current Configuration:\n";
echo "MAIL_MAILER: " . env('MAIL_MAILER') . "\n";
echo "MAILGUN_DOMAIN: " . env('MAILGUN_DOMAIN') . "\n";
echo "MAIL_FROM_ADDRESS: " . env('MAIL_FROM_ADDRESS') . "\n";
echo "MAIL_FROM_NAME: " . env('MAIL_FROM_NAME') . "\n\n";

// Identify the problem
$mailgunDomain = env('MAILGUN_DOMAIN');
$fromAddress = env('MAIL_FROM_ADDRESS');

echo "=== Issues Found ===\n";

if ($mailgunDomain === 'smtp.mailgun.org') {
    echo "❌ MAILGUN_DOMAIN is set to 'smtp.mailgun.org' - this is incorrect!\n";
    echo "   This should be your actual Mailgun domain (e.g., sandbox123.mailgun.org)\n\n";
}

if ($fromAddress === 'info@lyqid.com' && $mailgunDomain !== 'lyqid.com') {
    echo "❌ MAIL_FROM_ADDRESS doesn't match your Mailgun domain\n";
    echo "   From: info@lyqid.com but domain: $mailgunDomain\n\n";
}

echo "=== How to Fix ===\n\n";

echo "1. Go to your Mailgun Dashboard (https://app.mailgun.com)\n";
echo "2. Find your domain in the 'Domains' section\n";
echo "3. Update your .env file with the correct values:\n\n";

echo "For a sandbox domain (free tier):\n";
echo "MAIL_MAILER=mailgun\n";
echo "MAILGUN_DOMAIN=sandbox123.mailgun.org  # Replace with your actual sandbox domain\n";
echo "MAILGUN_SECRET=your-api-key-here\n";
echo "MAIL_FROM_ADDRESS=noreply@sandbox123.mailgun.org  # Use your sandbox domain\n";
echo "MAIL_FROM_NAME=\"Greenstreet\"\n\n";

echo "For a custom domain:\n";
echo "MAIL_MAILER=mailgun\n";
echo "MAILGUN_DOMAIN=lyqid.com  # Your custom domain\n";
echo "MAILGUN_SECRET=your-api-key-here\n";
echo "MAIL_FROM_ADDRESS=info@lyqid.com  # Your custom domain email\n";
echo "MAIL_FROM_NAME=\"Greenstreet\"\n\n";

echo "=== Alternative: Use Log Mailer for Testing ===\n";
echo "If you want to test without Mailgun, temporarily use:\n";
echo "MAIL_MAILER=log\n";
echo "This will log emails to storage/logs/laravel.log instead of sending them.\n\n";

echo "=== Testing Steps ===\n";
echo "1. Update your .env file with correct values\n";
echo "2. Run: php artisan config:clear\n";
echo "3. Run: php artisan config:cache\n";
echo "4. Test password reset functionality\n";
echo "5. Check Mailgun logs for any errors\n\n";

// Test current configuration
echo "=== Current Test ===\n";
try {
    $mailer = app('mailer');
    echo "✅ Mailer created successfully\n";
    
    // Try to send a test email
    Mail::raw('Test email', function($message) {
        $message->to('test@example.com')->subject('Test');
    });
    echo "✅ Email sent successfully\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    
    if (strpos($e->getMessage(), '401') !== false) {
        echo "\n🔧 401 Error = Authentication failed\n";
        echo "   - Check your MAILGUN_SECRET is correct\n";
        echo "   - Verify your API key in Mailgun dashboard\n";
    }
    
    if (strpos($e->getMessage(), '403') !== false) {
        echo "\n🔧 403 Error = Forbidden\n";
        echo "   - Check your domain is properly configured\n";
        echo "   - Verify your from address is authorized\n";
    }
}

echo "\n=== Quick Fix Commands ===\n";
echo "To switch to log mailer for testing:\n";
echo "sed -i '' 's/MAIL_MAILER=mailgun/MAIL_MAILER=log/' .env\n";
echo "php artisan config:clear\n";
echo "php artisan config:cache\n\n";

echo "To check logs after testing:\n";
echo "tail -f storage/logs/laravel.log\n"; 