<?php

// Debug file save issue
// Run this on your cloud server

echo "🔍 File Save Debug\n";
echo "=================\n\n";

// Load Laravel
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "1. Checking Current Site Settings...\n";
echo "==================================\n";

try {
    $siteSettings = \App\Models\SiteSettings::first();
    if ($siteSettings) {
        echo "   ✅ SiteSettings found\n";
        echo "   📄 Logo: " . ($siteSettings->logo ?? 'NULL') . "\n";
        echo "   📄 Banner: " . ($siteSettings->banner ?? 'NULL') . "\n";
        
        // Check if files exist in storage
        if ($siteSettings->logo) {
            $logoExists = Storage::disk('public')->exists($siteSettings->logo);
            echo "   📁 Logo exists in storage: " . ($logoExists ? '✅ YES' : '❌ NO') . "\n";
            
            if ($logoExists) {
                $logoUrl = Storage::disk('public')->url($siteSettings->logo);
                echo "   🔗 Logo URL: $logoUrl\n";
            }
        }
        
        if ($siteSettings->banner) {
            $bannerExists = Storage::disk('public')->exists($siteSettings->banner);
            echo "   📁 Banner exists in storage: " . ($bannerExists ? '✅ YES' : '❌ NO') . "\n";
            
            if ($bannerExists) {
                $bannerUrl = Storage::disk('public')->url($siteSettings->banner);
                echo "   🔗 Banner URL: $bannerUrl\n";
            }
        }
    } else {
        echo "   ❌ No SiteSettings found\n";
    }
} catch (Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
}

echo "\n2. Testing File Upload Process...\n";
echo "=================================\n";

// Simulate the exact upload process from the controller
try {
    $disk = Storage::disk('public');
    
    // Create a test file like the controller would
    $testContent = 'test-file-content-' . time();
    $testPath = 'site/test-file-' . uniqid() . '.txt';
    
    echo "   📝 Testing file save to: $testPath\n";
    
    if ($disk->put($testPath, $testContent)) {
        echo "   ✅ File saved successfully\n";
        
        if ($disk->exists($testPath)) {
            echo "   ✅ File exists in storage\n";
            
            $url = $disk->url($testPath);
            echo "   🔗 File URL: $url\n";
            
            // Test if URL is accessible
            $headers = @get_headers($url);
            if ($headers && strpos($headers[0], '200') !== false) {
                echo "   ✅ File accessible via URL\n";
            } else {
                echo "   ❌ File not accessible via URL\n";
                if ($headers) {
                    echo "   📋 HTTP Response: " . $headers[0] . "\n";
                }
            }
            
            // Clean up
            $disk->delete($testPath);
            echo "   🗑️  Test file deleted\n";
        } else {
            echo "   ❌ File does not exist in storage after save\n";
        }
    } else {
        echo "   ❌ Failed to save file\n";
    }
} catch (Exception $e) {
    echo "   ❌ Error during file save test: " . $e->getMessage() . "\n";
}

echo "\n3. Checking Storage Configuration...\n";
echo "===================================\n";

$defaultDisk = config('filesystems.default');
echo "   📁 Default disk: $defaultDisk\n";

$publicDisk = config('filesystems.disks.public');
if ($publicDisk) {
    echo "   📁 Public disk driver: " . ($publicDisk['driver'] ?? 'N/A') . "\n";
    echo "   📁 Public disk root: " . ($publicDisk['root'] ?? 'N/A') . "\n";
    echo "   📁 Public disk URL: " . ($publicDisk['url'] ?? 'N/A') . "\n";
    echo "   📁 Public disk endpoint: " . ($publicDisk['endpoint'] ?? 'N/A') . "\n";
    echo "   📁 Public disk bucket: " . ($publicDisk['bucket'] ?? 'N/A') . "\n";
}

echo "\n4. Testing Different Storage Methods...\n";
echo "======================================\n";

// Test direct file upload simulation
try {
    // Simulate what happens when a file is uploaded via form
    $testFileName = 'test-upload-' . uniqid() . '.txt';
    $testContent = 'upload-test-' . time();
    
    echo "   📝 Testing upload simulation: $testFileName\n";
    
    // Method 1: Direct put
    $result1 = Storage::disk('public')->put($testFileName, $testContent);
    echo "   📁 Direct put result: " . ($result1 ? '✅ Success' : '❌ Failed') . "\n";
    
    // Method 2: Store method (like controller uses)
    $tempFile = tempnam(sys_get_temp_dir(), 'test');
    file_put_contents($tempFile, $testContent);
    
    $uploadedFile = new \Illuminate\Http\UploadedFile($tempFile, $testFileName, 'text/plain', null, true);
    $result2 = $uploadedFile->store('site', 'public');
    echo "   📁 Store method result: " . ($result2 ? "✅ Success: $result2" : '❌ Failed') . "\n";
    
    // Check if files exist
    if ($result1) {
        $exists1 = Storage::disk('public')->exists($testFileName);
        echo "   📁 Direct put file exists: " . ($exists1 ? '✅ YES' : '❌ NO') . "\n";
    }
    
    if ($result2) {
        $exists2 = Storage::disk('public')->exists($result2);
        echo "   📁 Store method file exists: " . ($exists2 ? '✅ YES' : '❌ NO') . "\n";
    }
    
    // Clean up
    if ($result1) Storage::disk('public')->delete($testFileName);
    if ($result2) Storage::disk('public')->delete($result2);
    unlink($tempFile);
    
} catch (Exception $e) {
    echo "   ❌ Error during upload simulation: " . $e->getMessage() . "\n";
}

echo "\n5. Checking Laravel Logs...\n";
echo "==========================\n";

$logFile = storage_path('logs/laravel.log');
if (file_exists($logFile)) {
    echo "   📄 Laravel log exists\n";
    
    // Get last few lines of log
    $logLines = file($logFile);
    $recentLines = array_slice($logLines, -10);
    
    echo "   📋 Recent log entries:\n";
    foreach ($recentLines as $line) {
        if (strpos($line, 'upload') !== false || strpos($line, 'storage') !== false || strpos($line, 'error') !== false) {
            echo "      " . trim($line) . "\n";
        }
    }
} else {
    echo "   ❌ Laravel log not found\n";
}

echo "\n6. Environment Check...\n";
echo "======================\n";

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

echo "\n🔧 POSSIBLE ISSUES:\n";
echo "==================\n";
echo "1. File is being saved to database but not to storage\n";
echo "2. Storage configuration issue\n";
echo "3. File upload validation failing silently\n";
echo "4. Storage permissions issue\n";
echo "5. Laravel Cloud storage configuration problem\n\n";

echo "💡 The file upload process seems to work, but files aren't persisting.\n";
echo "Check the Laravel logs for specific error messages.\n"; 