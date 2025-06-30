<?php

// Fix script for broken image references
// Run this on your cloud server to clear broken image paths

echo "🔧 Fixing Broken Image References\n";
echo "=================================\n\n";

// Load Laravel
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\SiteSettings;
use Illuminate\Support\Facades\DB;

echo "1. Checking current SiteSettings...\n";
try {
    $settings = SiteSettings::getSettings();
    echo "   ✅ SiteSettings loaded\n";
    echo "   📝 Current logo: " . ($settings->logo ?: 'NULL') . "\n";
    echo "   📝 Current favicon: " . ($settings->favicon ?: 'NULL') . "\n";
    echo "   📝 Current OG image: " . ($settings->og_image ?: 'NULL') . "\n";
} catch (Exception $e) {
    echo "   ❌ SiteSettings error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n2. Checking if files exist...\n";
$brokenFields = [];

if ($settings->logo && !file_exists('storage/app/public/' . $settings->logo)) {
    echo "   ❌ Logo file missing: " . $settings->logo . "\n";
    $brokenFields[] = 'logo';
}

if ($settings->favicon && !file_exists('storage/app/public/' . $settings->favicon)) {
    echo "   ❌ Favicon file missing: " . $settings->favicon . "\n";
    $brokenFields[] = 'favicon';
}

if ($settings->og_image && !file_exists('storage/app/public/' . $settings->og_image)) {
    echo "   ❌ OG image file missing: " . $settings->og_image . "\n";
    $brokenFields[] = 'og_image';
}

if ($settings->welcome_cover_image && !file_exists('storage/app/public/' . $settings->welcome_cover_image)) {
    echo "   ❌ Welcome cover image missing: " . $settings->welcome_cover_image . "\n";
    $brokenFields[] = 'welcome_cover_image';
}

if (empty($brokenFields)) {
    echo "   ✅ All image files exist\n";
    echo "\n🎉 No broken image references found!\n";
    exit(0);
}

echo "\n3. Fixing broken image references...\n";
echo "   Found " . count($brokenFields) . " broken image reference(s)\n";

// Clear broken image fields
$updateData = [];
foreach ($brokenFields as $field) {
    $updateData[$field] = null;
    echo "   🔧 Clearing $field field\n";
}

try {
    $settings->update($updateData);
    SiteSettings::clearCache();
    echo "   ✅ Broken image references cleared\n";
} catch (Exception $e) {
    echo "   ❌ Error clearing broken references: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n4. Verifying fix...\n";
$settings = SiteSettings::getSettings();
echo "   📝 Logo: " . ($settings->logo ?: 'NULL') . "\n";
echo "   📝 Favicon: " . ($settings->favicon ?: 'NULL') . "\n";
echo "   📝 OG image: " . ($settings->og_image ?: 'NULL') . "\n";
echo "   📝 Welcome cover: " . ($settings->welcome_cover_image ?: 'NULL') . "\n";

echo "\n🎉 Fix completed!\n";
echo "\n📋 Next Steps:\n";
echo "1. Go to Admin > Site Settings\n";
echo "2. Upload your images again\n";
echo "3. The images should now display properly\n";
echo "\n💡 Tip: Make sure your images are under 1MB in size\n"; 