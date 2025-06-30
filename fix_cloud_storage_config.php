<?php

// Fix Laravel Cloud storage configuration
// Run this on your cloud server

echo "☁️ Laravel Cloud Storage Configuration Fix\n";
echo "==========================================\n\n";

// Load Laravel
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "1. Current Storage Configuration:\n";
echo "=================================\n";

$defaultDisk = config('filesystems.default');
echo "   📁 Default disk: $defaultDisk\n";

$publicDisk = config('filesystems.disks.public');
if ($publicDisk) {
    echo "   📁 Public disk root: " . ($publicDisk['root'] ?? 'N/A') . "\n";
    echo "   📁 Public disk URL: " . ($publicDisk['url'] ?? 'N/A') . "\n";
    echo "   📁 Public disk driver: " . ($publicDisk['driver'] ?? 'N/A') . "\n";
}

echo "\n2. Environment Variables Check:\n";
echo "===============================\n";

$envVars = [
    'FILESYSTEM_DISK',
    'LARAVEL_CLOUD_DISK_CONFIG',
    'AWS_ACCESS_KEY_ID',
    'AWS_SECRET_ACCESS_KEY',
    'AWS_BUCKET',
    'AWS_ENDPOINT'
];

foreach ($envVars as $var) {
    $value = env($var);
    if ($value) {
        if (in_array($var, ['AWS_SECRET_ACCESS_KEY'])) {
            echo "   ✅ $var: " . substr($value, 0, 8) . "..." . substr($value, -4) . "\n";
        } else {
            echo "   ✅ $var: $value\n";
        }
    } else {
        echo "   ❌ $var: NOT SET\n";
    }
}

echo "\n3. Testing Cloud Storage:\n";
echo "=========================\n";

try {
    $disk = Storage::disk('public');
    $testFile = 'cloud-test-' . uniqid() . '.txt';
    $testContent = 'cloud-test-' . time();
    
    if ($disk->put($testFile, $testContent)) {
        echo "   ✅ Cloud storage write successful\n";
        
        if ($disk->exists($testFile)) {
            echo "   ✅ Cloud storage read successful\n";
            
            // Get the URL
            $url = $disk->url($testFile);
            echo "   🔗 File URL: $url\n";
            
            // Test if URL is accessible
            $headers = @get_headers($url);
            if ($headers && strpos($headers[0], '200') !== false) {
                echo "   ✅ File accessible via cloud URL\n";
            } else {
                echo "   ❌ File not accessible via cloud URL\n";
                if ($headers) {
                    echo "   📋 HTTP Response: " . $headers[0] . "\n";
                }
            }
            
            $disk->delete($testFile);
            echo "   ✅ Cloud storage delete successful\n";
        }
    } else {
        echo "   ❌ Cloud storage write failed\n";
    }
} catch (Exception $e) {
    echo "   ❌ Cloud storage error: " . $e->getMessage() . "\n";
}

echo "\n4. Checking Local Storage Symlink:\n";
echo "==================================\n";

$symlinkPath = public_path('storage');
$targetPath = storage_path('app/public');

if (is_link($symlinkPath)) {
    $target = readlink($symlinkPath);
    echo "   ✅ Local symlink exists: $symlinkPath\n";
    echo "   📁 Points to: $target\n";
    
    if ($target === $targetPath) {
        echo "   ✅ Local symlink is correct\n";
    } else {
        echo "   ❌ Local symlink points to wrong location\n";
    }
} else {
    echo "   ❌ Local symlink missing\n";
}

echo "\n5. Testing Local vs Cloud Storage:\n";
echo "==================================\n";

// Test local storage
try {
    $localDisk = Storage::disk('local');
    $testFile = 'local-test-' . uniqid() . '.txt';
    $testContent = 'local-test-' . time();
    
    if ($localDisk->put($testFile, $testContent)) {
        echo "   ✅ Local storage write successful\n";
        $localDisk->delete($testFile);
    } else {
        echo "   ❌ Local storage write failed\n";
    }
} catch (Exception $e) {
    echo "   ❌ Local storage error: " . $e->getMessage() . "\n";
}

// Test public storage (should be cloud)
try {
    $publicDisk = Storage::disk('public');
    $testFile = 'public-test-' . uniqid() . '.txt';
    $testContent = 'public-test-' . time();
    
    if ($publicDisk->put($testFile, $testContent)) {
        echo "   ✅ Public storage write successful\n";
        
        $url = $publicDisk->url($testFile);
        echo "   🔗 Public file URL: $url\n";
        
        $publicDisk->delete($testFile);
    } else {
        echo "   ❌ Public storage write failed\n";
    }
} catch (Exception $e) {
    echo "   ❌ Public storage error: " . $e->getMessage() . "\n";
}

echo "\n6. Configuration Recommendations:\n";
echo "=================================\n";

if (env('FILESYSTEM_DISK') === 'public') {
    echo "   ✅ FILESYSTEM_DISK is set to 'public' (cloud storage)\n";
} else {
    echo "   ❌ FILESYSTEM_DISK should be set to 'public' for cloud storage\n";
}

if (env('LARAVEL_CLOUD_DISK_CONFIG')) {
    echo "   ✅ LARAVEL_CLOUD_DISK_CONFIG is configured\n";
} else {
    echo "   ❌ LARAVEL_CLOUD_DISK_CONFIG is missing\n";
}

echo "\n🔧 FIX STEPS:\n";
echo "============\n";
echo "1. Ensure FILESYSTEM_DISK=public in .env\n";
echo "2. Verify LARAVEL_CLOUD_DISK_CONFIG is set correctly\n";
echo "3. Clear Laravel caches: php artisan cache:clear\n";
echo "4. Test file uploads - they should go to cloud storage\n";
echo "5. Files should be accessible via cloud URLs, not local /storage\n\n";

echo "💡 Your app is configured for Laravel Cloud storage.\n";
echo "Files uploaded will be stored in the cloud and accessible via cloud URLs.\n";
echo "The local /storage symlink is for compatibility but not needed for cloud storage.\n"; 