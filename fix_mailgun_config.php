<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Mailgun Configuration Fix ===\n\n";

// Check if we're using the correct domain format
$mailgunDomain = env('MAILGUN_DOMAIN');
echo "Current MAILGUN_DOMAIN: " . $mailgunDomain . "\n";

// The issue is likely that you're using smtp.mailgun.org instead of your actual domain
if ($mailgunDomain === 'smtp.mailgun.org') {
    echo "❌ ERROR: MAILGUN_DOMAIN should be your actual Mailgun domain, not smtp.mailgun.org\n";
    echo "   Example: yourdomain.com or sandbox123.mailgun.org\n\n";
    
    echo "To fix this:\n";
    echo "1. Go to your Mailgun dashboard\n";
    echo "2. Find your domain (usually something like sandbox123.mailgun.org or yourdomain.com)\n";
    echo "3. Update your .env file:\n";
    echo "   MAILGUN_DOMAIN=your-actual-domain.mailgun.org\n\n";
} else {
    echo "✅ MAILGUN_DOMAIN format looks correct\n\n";
}

// Check from address
$fromAddress = env('MAIL_FROM_ADDRESS');
echo "Current MAIL_FROM_ADDRESS: " . $fromAddress . "\n";

if ($fromAddress === 'info@lyqid.com') {
    echo "⚠️  WARNING: Your from address (info@lyqid.com) might not match your Mailgun domain\n";
    echo "   The from address should be from your verified Mailgun domain\n\n";
    
    echo "To fix this:\n";
    echo "1. Use an email address from your Mailgun domain\n";
    echo "2. Example: noreply@" . $mailgunDomain . "\n";
    echo "3. Or verify info@lyqid.com in your Mailgun domain settings\n\n";
} else {
    echo "✅ MAIL_FROM_ADDRESS looks correct\n\n";
}

// Test with corrected configuration
echo "=== Testing with Current Configuration ===\n";

try {
    // Test basic mailer
    $mailer = app('mailer');
    echo "✅ Mailer created successfully\n";
    
    // Test with a simple email
    echo "Testing email send...\n";
    Mail::raw('Test email from Laravel', function($message) {
        $message->to('test@example.com')
               ->subject('Test Email');
    });
    echo "✅ Email sent successfully\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    
    if (strpos($e->getMessage(), '401') !== false) {
        echo "\n🔧 This is likely an authentication issue. Check:\n";
        echo "1. Your MAILGUN_SECRET is correct\n";
        echo "2. Your domain is properly configured in Mailgun\n";
        echo "3. Your API key has the correct permissions\n";
    }
    
    if (strpos($e->getMessage(), '403') !== false) {
        echo "\n🔧 This is likely a domain/from address issue. Check:\n";
        echo "1. Your from address is verified in Mailgun\n";
        echo "2. Your domain is properly set up\n";
    }
}

echo "\n=== Recommended Fix ===\n";
echo "1. Update your .env file with the correct Mailgun domain:\n";
echo "   MAILGUN_DOMAIN=your-actual-domain.mailgun.org\n";
echo "   MAIL_FROM_ADDRESS=noreply@your-actual-domain.mailgun.org\n\n";

echo "2. Clear config cache:\n";
echo "   php artisan config:clear\n";
echo "   php artisan config:cache\n\n";

echo "3. Test again with: php test_mailgun_config.php\n"; 