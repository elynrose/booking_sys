<?php

// Improved Cloudflare R2 fix script
// Run this on your cloud server to properly configure Cloudflare R2

echo "☁️ Cloudflare R2 Configuration Fix v2\n";
echo "=====================================\n\n";

// Load Laravel
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "1. Checking current configuration...\n";
echo "===================================\n";

$currentDisk = config('filesystems.default');
echo "   📁 Current default disk: $currentDisk\n";

$envFile = '.env';
if (!file_exists($envFile)) {
    echo "   ❌ .env file not found!\n";
    exit(1);
}

$envContent = file_get_contents($envFile);

// Check what's currently set
$checks = [
    'FILESYSTEM_DISK' => 'local',
    'AWS_ACCESS_KEY_ID' => '',
    'AWS_SECRET_ACCESS_KEY' => '',
    'AWS_BUCKET' => '',
    'AWS_ENDPOINT' => '',
    'AWS_DEFAULT_REGION' => 'us-east-1'
];

foreach ($checks as $var => $default) {
    if (preg_match('/' . $var . '=(.*)/', $envContent, $matches)) {
        $value = trim($matches[1]);
        if (in_array($var, ['AWS_SECRET_ACCESS_KEY'])) {
            echo "   ✅ $var: " . substr($value, 0, 8) . "..." . substr($value, -4) . "\n";
        } else {
            echo "   ✅ $var: $value\n";
        }
    } else {
        echo "   ❌ $var: NOT SET\n";
    }
}

echo "\n2. Updating .env for Cloudflare R2...\n";
echo "=====================================\n";

// Update or add required environment variables
$updates = [
    'FILESYSTEM_DISK=r2',
    'AWS_DEFAULT_REGION=us-east-1',
    'AWS_USE_PATH_STYLE_ENDPOINT=false'
];

foreach ($updates as $update) {
    list($key, $value) = explode('=', $update, 2);
    
    if (preg_match('/' . $key . '=(.*)/', $envContent)) {
        $envContent = preg_replace('/' . $key . '=(.*)/', $key . '=' . $value, $envContent);
        echo "   ✅ Updated $key to $value\n";
    } else {
        $envContent .= "\n$key=$value\n";
        echo "   ✅ Added $key=$value\n";
    }
}

// Write updated .env
if (file_put_contents($envFile, $envContent)) {
    echo "   ✅ .env file updated successfully\n";
} else {
    echo "   ❌ Failed to write .env file\n";
    exit(1);
}

echo "\n3. Testing Cloudflare R2 connection...\n";
echo "=====================================\n";

try {
    // Clear config cache to load new .env values
    Artisan::call('config:clear');
    echo "   ✅ Config cache cleared\n";
    
    // Test R2 disk
    $disk = Storage::disk('r2');
    echo "   ✅ R2 disk initialized\n";
    
    // Test write
    $testFile = 'test-' . uniqid() . '.txt';
    $testContent = 'test-' . time();
    
    if ($disk->put($testFile, $testContent)) {
        echo "   ✅ Write to Cloudflare R2 successful\n";
        
        // Test read
        if ($disk->exists($testFile)) {
            echo "   ✅ Read from Cloudflare R2 successful\n";
            
            // Test delete
            if ($disk->delete($testFile)) {
                echo "   ✅ Delete from Cloudflare R2 successful\n";
            } else {
                echo "   ❌ Delete from Cloudflare R2 failed\n";
            }
        } else {
            echo "   ❌ Read from Cloudflare R2 failed\n";
        }
    } else {
        echo "   ❌ Write to Cloudflare R2 failed\n";
    }
    
} catch (Exception $e) {
    echo "   ❌ Cloudflare R2 error: " . $e->getMessage() . "\n";
    echo "\n🔧 TROUBLESHOOTING:\n";
    echo "1. Check your AWS_ACCESS_KEY_ID and AWS_SECRET_ACCESS_KEY\n";
    echo "2. Verify AWS_BUCKET is correct\n";
    echo "3. Ensure AWS_ENDPOINT is in format: https://account_id.r2.cloudflarestorage.com\n";
    echo "4. Make sure your Cloudflare R2 API token has proper permissions\n";
    echo "5. Verify the region is set correctly (us-east-1 is standard for R2)\n";
}

echo "\n4. Checking existing files in Cloudflare R2...\n";
echo "=============================================\n";

try {
    $disk = Storage::disk('r2');
    $files = $disk->allFiles();
    
    if (empty($files)) {
        echo "   📁 No files found in Cloudflare R2\n";
    } else {
        echo "   📁 Found " . count($files) . " files in Cloudflare R2:\n";
        foreach (array_slice($files, 0, 10) as $file) {
            echo "      - $file\n";
        }
        if (count($files) > 10) {
            echo "      ... and " . (count($files) - 10) . " more\n";
        }
    }
} catch (Exception $e) {
    echo "   ❌ Error accessing Cloudflare R2: " . $e->getMessage() . "\n";
}

echo "\n5. Clearing all caches...\n";
echo "========================\n";

Artisan::call('cache:clear');
Artisan::call('config:clear');
Artisan::call('view:clear');
Artisan::call('route:clear');
echo "   ✅ All caches cleared\n";

echo "\n🎉 Cloudflare R2 Configuration Complete!\n";
echo "========================================\n";
echo "✅ Your app is now configured to use Cloudflare R2\n";
echo "📝 New uploads will be stored in Cloudflare R2\n";
echo "🔄 Try uploading a new image to test\n";
echo "\n💡 If you still have issues:\n";
echo "1. Check your Cloudflare R2 credentials\n";
echo "2. Verify your bucket permissions\n";
echo "3. Run: php debug_cloud_config.php for detailed diagnostics\n";
echo "4. Make sure AWS_ENDPOINT is correct for your Cloudflare account\n"; 