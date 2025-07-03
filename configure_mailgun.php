<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== MAILGUN CONFIGURATION GUIDE ===\n\n";

echo "STEP 1: Get Mailgun Credentials\n";
echo "1. Go to https://app.mailgun.com/\n";
echo "2. Sign up or log in to your account\n";
echo "3. Go to 'Sending' → 'Domains'\n";
echo "4. Add your domain or use the sandbox domain\n";
echo "5. Copy your API key from 'Settings' → 'API Keys'\n\n";

echo "STEP 2: Update your .env file\n";
echo "Add these lines to your .env file:\n\n";
echo "MAIL_MAILER=mailgun\n";
echo "MAILGUN_DOMAIN=your-domain.mailgun.org\n";
echo "MAILGUN_SECRET=your-api-key-here\n";
echo "MAIL_FROM_ADDRESS=noreply@your-domain.mailgun.org\n";
echo "MAIL_FROM_NAME=\"GymApp\"\n\n";

echo "STEP 3: Install Mailgun Package (if not already installed)\n";
echo "Run: composer require guzzlehttp/guzzle\n\n";

echo "STEP 4: Test Configuration\n";
echo "After updating .env, run: php test_mailgun.php\n\n";

echo "=== COMMON MAILGUN SETTINGS ===\n";
echo "For testing, you can use:\n";
echo "- Domain: sandbox123456789.mailgun.org (replace with your actual domain)\n";
echo "- API Key: key-123456789 (replace with your actual API key)\n";
echo "- From Address: noreply@sandbox123456789.mailgun.org\n\n";

echo "=== SECURITY REMINDERS ===\n";
echo "✅ Never commit your .env file to git\n";
echo "✅ Keep your API key secure\n";
echo "✅ Use environment variables in production\n\n";

echo "=== TROUBLESHOOTING ===\n";
echo "If emails don't send:\n";
echo "1. Check your domain is verified in Mailgun\n";
echo "2. Verify your API key is correct\n";
echo "3. Check Laravel logs: storage/logs/laravel.log\n";
echo "4. Test with sandbox domain first\n\n";

echo "Ready to proceed? Update your .env file and then run the test script.\n"; 