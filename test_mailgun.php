<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

echo "=== MAILGUN CONFIGURATION TEST ===\n\n";

// Check current configuration
echo "Current Mail Configuration:\n";
echo "- Mail Driver: " . config('mail.default') . "\n";
echo "- Mailgun Domain: " . (config('services.mailgun.domain') ?: 'NOT SET') . "\n";
echo "- Mailgun Secret: " . (config('services.mailgun.secret') ? 'SET' : 'NOT SET') . "\n";
echo "- From Address: " . config('mail.from.address') . "\n";
echo "- From Name: " . config('mail.from.name') . "\n\n";

// Check if Mailgun is properly configured
if (config('mail.default') !== 'mailgun') {
    echo "❌ Mail driver is not set to 'mailgun'\n";
    echo "   Update your .env file: MAIL_MAILER=mailgun\n\n";
    exit(1);
}

if (!config('services.mailgun.domain')) {
    echo "❌ Mailgun domain is not configured\n";
    echo "   Update your .env file: MAILGUN_DOMAIN=your-domain.mailgun.org\n\n";
    exit(1);
}

if (!config('services.mailgun.secret')) {
    echo "❌ Mailgun API key is not configured\n";
    echo "   Update your .env file: MAILGUN_SECRET=your-api-key\n\n";
    exit(1);
}

echo "✅ Mailgun configuration looks good!\n\n";

// Test sending email
echo "=== TESTING EMAIL SENDING ===\n";

try {
    // Test with a simple email
    Mail::raw('This is a test email from GymApp via Mailgun at ' . now(), function($message) {
        $message->to('test@example.com')
                ->subject('GymApp Mailgun Test')
                ->from(config('mail.from.address'), config('mail.from.name'));
    });
    
    echo "✅ Email test completed successfully!\n";
    echo "📧 Email was sent via Mailgun\n";
    echo "   Check your Mailgun dashboard for delivery status\n\n";
    
} catch (Exception $e) {
    echo "❌ Email test failed!\n";
    echo "Error: " . $e->getMessage() . "\n\n";
    
    // Check for common Mailgun errors
    if (strpos($e->getMessage(), '401') !== false) {
        echo "🔍 This looks like an authentication error.\n";
        echo "   Check your Mailgun API key in .env\n";
    } elseif (strpos($e->getMessage(), '403') !== false) {
        echo "🔍 This looks like a domain verification error.\n";
        echo "   Check your Mailgun domain is verified\n";
    } elseif (strpos($e->getMessage(), '400') !== false) {
        echo "🔍 This looks like a configuration error.\n";
        echo "   Check your from address matches your domain\n";
    }
    
    echo "\nCheck Laravel logs for more details:\n";
    echo "tail -f storage/logs/laravel.log\n\n";
}

// Test with a real email address (if provided)
echo "=== TESTING WITH REAL EMAIL ===\n";
echo "To test with your real email address, run:\n";
echo "php test_mailgun.php your-email@example.com\n\n";

if (isset($argv[1])) {
    $testEmail = $argv[1];
    echo "Testing with email: {$testEmail}\n";
    
    try {
        Mail::raw('This is a test email from GymApp via Mailgun at ' . now(), function($message) use ($testEmail) {
            $message->to($testEmail)
                    ->subject('GymApp Mailgun Test - Real Email')
                    ->from(config('mail.from.address'), config('mail.from.name'));
        });
        
        echo "✅ Real email test completed!\n";
        echo "📧 Check your inbox at: {$testEmail}\n";
        
    } catch (Exception $e) {
        echo "❌ Real email test failed: " . $e->getMessage() . "\n";
    }
}

echo "\n=== NEXT STEPS ===\n";
echo "1. If tests pass, your Mailgun is working!\n";
echo "2. Update your app to use real email addresses\n";
echo "3. Monitor Mailgun dashboard for delivery rates\n";
echo "4. Check Laravel Cloud logs if deployed\n\n"; 