<?php

// Debug script for image display issues
// Run this on your cloud server to diagnose image problems

echo "🔍 Debugging Image Display Issues\n";
echo "=================================\n\n";

// Load Laravel
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\SiteSettings;

echo "1. Checking SiteSettings...\n";
try {
    $settings = SiteSettings::getSettings();
    echo "   ✅ SiteSettings loaded\n";
    echo "   📝 Logo field: " . ($settings->logo ?: 'NULL') . "\n";
    echo "   📝 Favicon field: " . ($settings->favicon ?: 'NULL') . "\n";
    echo "   📝 OG Image field: " . ($settings->og_image ?: 'NULL') . "\n";
} catch (Exception $e) {
    echo "   ❌ SiteSettings error: " . $e->getMessage() . "\n";
}

echo "\n2. Checking image URLs...\n";
if (isset($settings)) {
    echo "   🔗 Logo URL: " . ($settings->logo_url ?: 'NULL') . "\n";
    echo "   🔗 Favicon URL: " . ($settings->favicon_url ?: 'NULL') . "\n";
    echo "   🔗 OG Image URL: " . ($settings->og_image_url ?: 'NULL') . "\n";
}

echo "\n3. Checking actual files...\n";
if (isset($settings) && $settings->logo) {
    $logoPath = 'storage/app/public/' . $settings->logo;
    if (file_exists($logoPath)) {
        echo "   ✅ Logo file exists: $logoPath\n";
        echo "   📏 File size: " . filesize($logoPath) . " bytes\n";
        echo "   🔐 Permissions: " . substr(sprintf('%o', fileperms($logoPath)), -4) . "\n";
    } else {
        echo "   ❌ Logo file missing: $logoPath\n";
        echo "   🔧 This means the upload failed but the database was updated\n";
    }
}

echo "\n4. Checking symlink access...\n";
if (isset($settings) && $settings->logo) {
    $symlinkPath = 'public/storage/' . $settings->logo;
    if (file_exists($symlinkPath)) {
        echo "   ✅ File accessible through symlink: $symlinkPath\n";
    } else {
        echo "   ❌ File not accessible through symlink: $symlinkPath\n";
    }
}

echo "\n5. Checking web server access...\n";
// Create directory if it doesn't exist
if (!is_dir('storage/app/public')) {
    mkdir('storage/app/public', 0755, true);
}
$testFile = 'public/storage/test.txt';
file_put_contents('storage/app/public/test.txt', 'test');
if (file_exists($testFile)) {
    echo "   ✅ Web server can access files through symlink\n";
    unlink('storage/app/public/test.txt');
} else {
    echo "   ❌ Web server cannot access files through symlink\n";
}

echo "\n6. Checking all files in storage...\n";
$storagePath = 'storage/app/public';
if (is_dir($storagePath)) {
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($storagePath));
    $filesFound = false;
    foreach ($iterator as $file) {
        if ($file->isFile()) {
            $filesFound = true;
            $relativePath = str_replace($storagePath . '/', '', $file->getPathname());
            echo "   📁 $relativePath (" . filesize($file->getPathname()) . " bytes)\n";
        }
    }
    if (!$filesFound) {
        echo "   ❌ No files found in storage directory\n";
    }
} else {
    echo "   ❌ Storage directory doesn't exist\n";
}

echo "\n7. Checking asset() function...\n";
$assetUrl = asset('storage/test.txt');
echo "   🔗 Asset URL: $assetUrl\n";

echo "\n8. Checking APP_URL...\n";
echo "   🌐 APP_URL: " . config('app.url') . "\n";

echo "\n🎯 DIAGNOSIS:\n";
if (isset($settings) && $settings->logo && !file_exists('storage/app/public/' . $settings->logo)) {
    echo "❌ The image file is missing from disk but exists in the database.\n";
    echo "   This indicates a failed upload that wasn't properly handled.\n";
    echo "\n🔧 SOLUTION:\n";
    echo "1. Clear the logo field from the database\n";
    echo "2. Try uploading the image again\n";
    echo "3. Check the upload process for errors\n";
} else {
    echo "✅ Files appear to be in place\n";
}

echo "\n📋 Next Steps:\n";
echo "1. Clear the logo field: UPDATE site_settings SET logo = NULL;\n";
echo "2. Try uploading a new image through the admin panel\n";
echo "3. Check the upload process for any errors\n";
echo "4. Monitor the Laravel logs during upload\n"; 