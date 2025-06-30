<?php

// Script to update .env file for cloud storage
// Run this on your cloud server

echo "🔧 Updating .env for Cloud Storage\n";
echo "==================================\n\n";

$envFile = '.env';

if (!file_exists($envFile)) {
    echo "❌ .env file not found!\n";
    exit(1);
}

echo "1. Reading current .env file...\n";
$envContent = file_get_contents($envFile);

// Check current FILESYSTEM_DISK setting
if (preg_match('/FILESYSTEM_DISK=(.*)/', $envContent, $matches)) {
    $currentDisk = trim($matches[1]);
    echo "   📝 Current FILESYSTEM_DISK: $currentDisk\n";
    
    if ($currentDisk === 'public') {
        echo "   ✅ Already configured for cloud storage\n";
        exit(0);
    }
} else {
    echo "   ❌ FILESYSTEM_DISK not found, will add it\n";
}

echo "\n2. Updating FILESYSTEM_DISK to 'public'...\n";

// Replace or add FILESYSTEM_DISK setting
if (preg_match('/FILESYSTEM_DISK=(.*)/', $envContent)) {
    $envContent = preg_replace('/FILESYSTEM_DISK=(.*)/', 'FILESYSTEM_DISK=public', $envContent);
    echo "   ✅ Updated existing FILESYSTEM_DISK setting\n";
} else {
    $envContent .= "\nFILESYSTEM_DISK=public\n";
    echo "   ✅ Added FILESYSTEM_DISK setting\n";
}

echo "\n3. Writing updated .env file...\n";
if (file_put_contents($envFile, $envContent)) {
    echo "   ✅ .env file updated successfully\n";
} else {
    echo "   ❌ Failed to write .env file\n";
    exit(1);
}

echo "\n4. Clearing Laravel caches...\n";
system('php artisan cache:clear');
system('php artisan config:clear');
system('php artisan view:clear');
echo "   ✅ Caches cleared\n";

echo "\n🎉 Cloud storage configuration complete!\n";
echo "📝 Your app will now use Cloudflare R2 for file storage\n";
echo "🔄 Try uploading a new image to test the configuration\n"; 