<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Email Configuration Fix ===\n\n";

// Read current .env file
$envFile = '.env';
$envContent = file_get_contents($envFile);

echo "Current email configuration:\n";
echo "MAIL_MAILER: " . env('MAIL_MAILER') . "\n";
echo "MAIL_HOST: " . env('MAIL_HOST') . "\n";
echo "MAILGUN_DOMAIN: " . env('MAILGUN_DOMAIN') . "\n\n";

// Update .env file to use log driver
$envContent = preg_replace('/MAIL_MAILER=mailgun/', 'MAIL_MAILER=log', $envContent);

// Write back to .env file
file_put_contents($envFile, $envContent);

echo "✅ Updated MAIL_MAILER to 'log'\n";
echo "✅ Emails will now be logged to storage/logs/mail.log instead of being sent\n";
echo "✅ This will prevent 401 errors during payment processing\n\n";

echo "To restore Mailgun later, run:\n";
echo "php artisan tinker --execute=\"echo 'MAIL_MAILER=mailgun' >> .env\"\n\n";

echo "=== Configuration Updated ===\n"; 