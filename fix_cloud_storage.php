<?php

// Fix script for cloud storage configuration
// Run this on your cloud server to configure Cloudflare R2 storage

echo "☁️ Configuring Cloud Storage\n";
echo "============================\n\n";

// Load Laravel
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "1. Checking current filesystem configuration...\n";
$currentDisk = config('filesystems.default');
echo "   📁 Current default disk: $currentDisk\n";

if ($currentDisk === 'local') {
    echo "   ⚠️  Currently using local storage\n";
} else {
    echo "   ✅ Already using cloud storage\n";
}

echo "\n2. Checking environment variables...\n";
$envFile = '.env';
if (file_exists($envFile)) {
    $envContent = file_get_contents($envFile);
    
    // Check for FILESYSTEM_DISK
    if (preg_match('/FILESYSTEM_DISK=(.*)/', $envContent, $matches)) {
        echo "   📝 FILESYSTEM_DISK: " . trim($matches[1]) . "\n";
    } else {
        echo "   ❌ FILESYSTEM_DISK not found in .env\n";
    }
    
    // Check for LARAVEL_CLOUD_DISK_CONFIG
    if (preg_match('/LARAVEL_CLOUD_DISK_CONFIG=(.*)/', $envContent, $matches)) {
        echo "   ✅ LARAVEL_CLOUD_DISK_CONFIG found\n";
        $config = json_decode($matches[1], true);
        if ($config && isset($config[0])) {
            echo "   🌐 Cloud endpoint: " . $config[0]['endpoint'] . "\n";
            echo "   🪣 Bucket: " . $config[0]['bucket'] . "\n";
        }
    } else {
        echo "   ❌ LARAVEL_CLOUD_DISK_CONFIG not found\n";
    }
} else {
    echo "   ❌ .env file not found\n";
}

echo "\n3. Testing cloud storage connection...\n";
try {
    // Temporarily set cloud as default
    config(['filesystems.default' => 'public']);
    
    $disk = Storage::disk('public');
    echo "   ✅ Storage disk initialized\n";
    
    // Test if we can write to cloud storage
    $testContent = 'test-' . time();
    $testPath = 'test-' . uniqid() . '.txt';
    
    if ($disk->put($testPath, $testContent)) {
        echo "   ✅ Can write to cloud storage\n";
        
        if ($disk->exists($testPath)) {
            echo "   ✅ Can read from cloud storage\n";
            $disk->delete($testPath);
            echo "   ✅ Can delete from cloud storage\n";
        } else {
            echo "   ❌ Cannot read from cloud storage\n";
        }
    } else {
        echo "   ❌ Cannot write to cloud storage\n";
    }
    
} catch (Exception $e) {
    echo "   ❌ Cloud storage error: " . $e->getMessage() . "\n";
}

echo "\n4. Checking existing images in cloud storage...\n";
try {
    $disk = Storage::disk('public');
    $files = $disk->allFiles('site');
    
    if (empty($files)) {
        echo "   ❌ No files found in cloud storage site directory\n";
    } else {
        echo "   ✅ Found " . count($files) . " files in cloud storage:\n";
        foreach ($files as $file) {
            echo "   📁 $file\n";
        }
    }
} catch (Exception $e) {
    echo "   ❌ Error accessing cloud storage: " . $e->getMessage() . "\n";
}

echo "\n🔧 FIX STEPS:\n";
echo "1. Set FILESYSTEM_DISK=public in your .env file\n";
echo "2. Clear all caches: php artisan cache:clear\n";
echo "3. Restart your web server if needed\n";
echo "4. Try uploading a new image\n";
echo "\n💡 The images should now be stored in and served from Cloudflare R2\n"; 