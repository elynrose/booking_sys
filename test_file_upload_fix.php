<?php

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\Storage;
use App\Models\SiteSettings;
use App\Models\User;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🔧 Testing File Upload Fix\n";
echo "==========================\n\n";

// Test 1: Check SiteSettings URL generation
echo "1. Testing SiteSettings URL generation...\n";
$settings = SiteSettings::getSettings();
if ($settings->logo) {
    echo "   ✅ Logo URL: " . $settings->logo_url . "\n";
    echo "   ✅ Logo exists in storage: " . (Storage::exists($settings->logo) ? 'YES' : 'NO') . "\n";
} else {
    echo "   ⚠️  No logo found\n";
}

// Test 2: Check User model photo field
echo "\n2. Testing User model photo field...\n";
$user = User::first();
if ($user) {
    echo "   ✅ User found: " . $user->name . "\n";
    echo "   ✅ Photo field exists: " . (isset($user->photo) ? 'YES' : 'NO') . "\n";
    if ($user->photo) {
        echo "   ✅ Photo URL: " . $user->profile_photo_url . "\n";
        echo "   ✅ Photo exists in storage: " . (Storage::exists($user->photo) ? 'YES' : 'NO') . "\n";
    } else {
        echo "   ⚠️  No photo found\n";
    }
} else {
    echo "   ❌ No users found\n";
}

// Test 3: Test file upload simulation
echo "\n3. Testing file upload simulation...\n";
$testContent = "Test file content - " . time();
$testFileName = 'test-upload-' . uniqid() . '.txt';
$testPath = 'users/' . $testFileName;

try {
    $result = Storage::put($testPath, $testContent);
    echo "   ✅ File upload simulation: " . ($result ? 'SUCCESS' : 'FAILED') . "\n";
    
    if ($result) {
        echo "   ✅ File exists in storage: " . (Storage::exists($testPath) ? 'YES' : 'NO') . "\n";
        echo "   ✅ File URL: " . Storage::url($testPath) . "\n";
        
        // Clean up
        Storage::delete($testPath);
        echo "   ✅ Test file cleaned up\n";
    }
} catch (Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
}

// Test 4: Check storage configuration
echo "\n4. Checking storage configuration...\n";
echo "   📁 Default disk: " . config('filesystems.default') . "\n";
echo "   📁 Public disk driver: " . config('filesystems.disks.public.driver') . "\n";
echo "   📁 Public disk URL: " . config('filesystems.disks.public.url') . "\n";

echo "\n✅ File upload fix test completed!\n"; 