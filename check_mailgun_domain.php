<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== MAILGUN DOMAIN CHECK ===\n\n";

echo "Current Configuration:\n";
echo "- MAILGUN_DOMAIN: " . config('services.mailgun.domain') . "\n";
echo "- MAILGUN_SECRET: " . (config('services.mailgun.secret') ? 'SET' : 'NOT SET') . "\n";
echo "- MAILGUN_ENDPOINT: " . config('services.mailgun.endpoint') . "\n\n";

echo "ISSUE FOUND:\n";
echo "❌ Your MAILGUN_DOMAIN is set to 'smtp.mailgun.org'\n";
echo "   This is the SMTP server, NOT your Mailgun domain!\n\n";

echo "HOW TO FIX:\n";
echo "1. Go to https://app.mailgun.com/\n";
echo "2. Log in to your Mailgun account\n";
echo "3. Go to 'Sending' → 'Domains'\n";
echo "4. You'll see your domain(s) listed, such as:\n";
echo "   - mg.yourdomain.com\n";
echo "   - sandbox123456789.mailgun.org\n";
echo "   - yourdomain.mailgun.org\n\n";

echo "5. Copy your domain name and update your .env file:\n";
echo "   MAILGUN_DOMAIN=your-actual-domain.mailgun.org\n\n";

echo "COMMON EXAMPLES:\n";
echo "- If you have a custom domain: mg.yourdomain.com\n";
echo "- If you're using sandbox: sandbox123456789.mailgun.org\n";
echo "- If you have a Mailgun domain: yourdomain.mailgun.org\n\n";

echo "WHAT NOT TO USE:\n";
echo "❌ smtp.mailgun.org (SMTP server)\n";
echo "❌ api.mailgun.net (API endpoint)\n";
echo "❌ mailgun.org (main website)\n\n";

echo "After updating your domain, run:\n";
echo "php artisan config:clear\n";
echo "php test_mailgun.php\n\n";

echo "Need help? Check your Mailgun dashboard for the correct domain name.\n"; 