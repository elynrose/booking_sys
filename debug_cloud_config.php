<?php

// Comprehensive cloud storage debug script
// Run this on your cloud server to diagnose all issues

echo "🔍 Cloud Storage Debug Report\n";
echo "============================\n\n";

// Load Laravel
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "1. ENVIRONMENT VARIABLES CHECK:\n";
echo "===============================\n";

$envVars = [
    'FILESYSTEM_DISK',
    'AWS_ACCESS_KEY_ID',
    'AWS_SECRET_ACCESS_KEY',
    'AWS_DEFAULT_REGION',
    'AWS_BUCKET',
    'AWS_URL',
    'AWS_ENDPOINT',
    'AWS_USE_PATH_STYLE_ENDPOINT',
    'LARAVEL_CLOUD_DISK_CONFIG'
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

echo "\n2. FILESYSTEM CONFIGURATION:\n";
echo "============================\n";

$defaultDisk = config('filesystems.default');
echo "   📁 Default disk: $defaultDisk\n";

$disks = config('filesystems.disks');
foreach ($disks as $diskName => $diskConfig) {
    echo "   🔧 Disk '$diskName':\n";
    echo "      Driver: " . ($diskConfig['driver'] ?? 'N/A') . "\n";
    if (isset($diskConfig['root'])) {
        echo "      Root: " . $diskConfig['root'] . "\n";
    }
    if (isset($diskConfig['bucket'])) {
        echo "      Bucket: " . $diskConfig['bucket'] . "\n";
    }
    if (isset($diskConfig['endpoint'])) {
        echo "      Endpoint: " . $diskConfig['endpoint'] . "\n";
    }
    echo "\n";
}

echo "3. STORAGE TESTING:\n";
echo "==================\n";

// Test each disk
foreach (['local', 'public', 's3'] as $diskName) {
    echo "   Testing disk '$diskName':\n";
    
    try {
        $disk = Storage::disk($diskName);
        echo "      ✅ Disk initialized\n";
        
        // Test write
        $testFile = 'test-' . uniqid() . '.txt';
        $testContent = 'test-' . time();
        
        if ($disk->put($testFile, $testContent)) {
            echo "      ✅ Write successful\n";
            
            // Test read
            if ($disk->exists($testFile)) {
                echo "      ✅ Read successful\n";
                
                // Test delete
                if ($disk->delete($testFile)) {
                    echo "      ✅ Delete successful\n";
                } else {
                    echo "      ❌ Delete failed\n";
                }
            } else {
                echo "      ❌ Read failed\n";
            }
        } else {
            echo "      ❌ Write failed\n";
        }
        
    } catch (Exception $e) {
        echo "      ❌ Error: " . $e->getMessage() . "\n";
    }
    
    echo "\n";
}

echo "4. EXISTING FILES CHECK:\n";
echo "=======================\n";

// Check what files exist in each disk
foreach (['local', 'public'] as $diskName) {
    echo "   Files in '$diskName' disk:\n";
    
    try {
        $disk = Storage::disk($diskName);
        $files = $disk->allFiles();
        
        if (empty($files)) {
            echo "      📁 No files found\n";
        } else {
            echo "      📁 Found " . count($files) . " files:\n";
            foreach (array_slice($files, 0, 10) as $file) {
                echo "         - $file\n";
            }
            if (count($files) > 10) {
                echo "         ... and " . (count($files) - 10) . " more\n";
            }
        }
    } catch (Exception $e) {
        echo "      ❌ Error: " . $e->getMessage() . "\n";
    }
    
    echo "\n";
}

echo "5. SYMLINK CHECK:\n";
echo "=================\n";

$symlinkPath = public_path('storage');
$targetPath = storage_path('app/public');

if (is_link($symlinkPath)) {
    echo "   ✅ Symlink exists at: $symlinkPath\n";
    echo "   📁 Points to: " . readlink($symlinkPath) . "\n";
    
    if (file_exists($targetPath)) {
        echo "   ✅ Target directory exists\n";
    } else {
        echo "   ❌ Target directory missing\n";
    }
} else {
    echo "   ❌ Symlink missing at: $symlinkPath\n";
}

echo "\n6. WEB SERVER ACCESS:\n";
echo "====================\n";

$testUrl = env('APP_URL') . '/storage/test-access.txt';
echo "   🔗 Test URL: $testUrl\n";

// Create a test file
try {
    Storage::disk('public')->put('test-access.txt', 'test-' . time());
    echo "   ✅ Test file created\n";
} catch (Exception $e) {
    echo "   ❌ Could not create test file: " . $e->getMessage() . "\n";
}

echo "\n🔧 RECOMMENDED FIXES:\n";
echo "====================\n";

if (!env('AWS_ACCESS_KEY_ID')) {
    echo "1. ❌ Set AWS_ACCESS_KEY_ID in .env\n";
}
if (!env('AWS_SECRET_ACCESS_KEY')) {
    echo "2. ❌ Set AWS_SECRET_ACCESS_KEY in .env\n";
}
if (!env('AWS_BUCKET')) {
    echo "3. ❌ Set AWS_BUCKET in .env\n";
}
if (!env('AWS_ENDPOINT')) {
    echo "4. ❌ Set AWS_ENDPOINT for Cloudflare R2\n";
}
if (env('FILESYSTEM_DISK') !== 's3') {
    echo "5. ❌ Set FILESYSTEM_DISK=s3 in .env\n";
}

echo "\n💡 For Cloudflare R2, you need:\n";
echo "   AWS_ACCESS_KEY_ID=your_r2_key\n";
echo "   AWS_SECRET_ACCESS_KEY=your_r2_secret\n";
echo "   AWS_BUCKET=your_bucket_name\n";
echo "   AWS_ENDPOINT=https://your_account_id.r2.cloudflarestorage.com\n";
echo "   AWS_DEFAULT_REGION=auto\n";
echo "   FILESYSTEM_DISK=s3\n"; 