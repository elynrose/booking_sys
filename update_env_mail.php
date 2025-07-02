<?php

echo "=== Update .env Mail Configuration ===\n\n";

// Read current .env file
$envFile = '.env';
if (!file_exists($envFile)) {
    echo "❌ .env file not found\n";
    exit;
}

$envContent = file_get_contents($envFile);
echo "Current .env content:\n";
echo $envContent . "\n\n";

// Update mail configuration
$updates = [
    'MAIL_MAILER=mailgun' => 'MAIL_MAILER=log',
    'MAIL_FROM_ADDRESS=info@lyqid.com' => 'MAIL_FROM_ADDRESS=info@lyqid.com',
    'MAIL_FROM_NAME=greenstreet' => 'MAIL_FROM_NAME="Greenstreet"'
];

$updatedContent = $envContent;
foreach ($updates as $old => $new) {
    if (strpos($updatedContent, $old) !== false) {
        $updatedContent = str_replace($old, $new, $updatedContent);
        echo "✅ Updated: $old -> $new\n";
    } else {
        // Add if not exists
        $updatedContent .= "\n" . $new;
        echo "✅ Added: $new\n";
    }
}

// Write back to .env file
if (file_put_contents($envFile, $updatedContent)) {
    echo "\n✅ .env file updated successfully\n";
    echo "Updated .env content:\n";
    echo $updatedContent . "\n";
} else {
    echo "\n❌ Failed to update .env file\n";
}

echo "\n=== Next Steps ===\n";
echo "1. Run: php artisan config:clear\n";
echo "2. Run: php artisan config:cache\n";
echo "3. Test password reset at: https://lyqid.com/password/email\n"; 