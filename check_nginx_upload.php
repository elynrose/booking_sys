<?php

// Check nginx and file upload configuration
// Run this on your cloud server

echo "🔍 Nginx & File Upload Check\n";
echo "============================\n\n";

// Load Laravel
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "1. PHP Upload Settings:\n";
echo "======================\n";

$uploadSettings = [
    'file_uploads' => ini_get('file_uploads'),
    'upload_max_filesize' => ini_get('upload_max_filesize'),
    'post_max_size' => ini_get('post_max_size'),
    'max_file_uploads' => ini_get('max_file_uploads'),
    'memory_limit' => ini_get('memory_limit'),
    'max_execution_time' => ini_get('max_execution_time'),
    'max_input_time' => ini_get('max_input_time')
];

foreach ($uploadSettings as $setting => $value) {
    echo "   📝 $setting: $value\n";
}

echo "\n2. Storage Directory Check:\n";
echo "==========================\n";

$storageDirs = [
    'storage/app/public',
    'public/storage',
    'storage/app/public/site',
    'storage/app/public/trainers',
    'storage/app/public/schedules'
];

foreach ($storageDirs as $dir) {
    if (file_exists($dir)) {
        $perms = substr(sprintf('%o', fileperms($dir)), -4);
        $writable = is_writable($dir) ? '✅ Writable' : '❌ Not Writable';
        echo "   📁 $dir: $perms - $writable\n";
    } else {
        echo "   ❌ $dir: Directory not found\n";
    }
}

echo "\n3. Storage Symlink Check:\n";
echo "========================\n";

$symlinkPath = public_path('storage');
$targetPath = storage_path('app/public');

if (is_link($symlinkPath)) {
    $target = readlink($symlinkPath);
    echo "   ✅ Symlink exists: $symlinkPath\n";
    echo "   📁 Points to: $target\n";
    
    if ($target === $targetPath) {
        echo "   ✅ Symlink is correct\n";
    } else {
        echo "   ❌ Symlink points to wrong location\n";
    }
} else {
    echo "   ❌ No symlink at: $symlinkPath\n";
}

echo "\n4. Test File Upload:\n";
echo "===================\n";

// Test if we can write to storage
try {
    $testFile = storage_path('app/public/test-upload-' . uniqid() . '.txt');
    $testContent = 'test-' . time();
    
    if (file_put_contents($testFile, $testContent)) {
        echo "   ✅ Can write to storage\n";
        
        if (file_exists($testFile)) {
            echo "   ✅ Can read from storage\n";
            unlink($testFile);
            echo "   ✅ Can delete from storage\n";
        }
    } else {
        echo "   ❌ Cannot write to storage\n";
    }
} catch (Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
}

echo "\n5. Laravel Storage Test:\n";
echo "=======================\n";

try {
    $disk = Storage::disk('public');
    $testFile = 'test-laravel-' . uniqid() . '.txt';
    $testContent = 'laravel-test-' . time();
    
    if ($disk->put($testFile, $testContent)) {
        echo "   ✅ Laravel Storage write successful\n";
        
        if ($disk->exists($testFile)) {
            echo "   ✅ Laravel Storage read successful\n";
            $disk->delete($testFile);
            echo "   ✅ Laravel Storage delete successful\n";
        }
    } else {
        echo "   ❌ Laravel Storage write failed\n";
    }
} catch (Exception $e) {
    echo "   ❌ Laravel Storage error: " . $e->getMessage() . "\n";
}

echo "\n6. Web Server Check:\n";
echo "===================\n";

// Check if we can access files via web
$testUrl = env('APP_URL') . '/storage/test-web-access.txt';
echo "   🔗 Test URL: $testUrl\n";

try {
    Storage::disk('public')->put('test-web-access.txt', 'web-test-' . time());
    echo "   ✅ Test file created\n";
    
    // Check if accessible via web
    $headers = @get_headers($testUrl);
    if ($headers && strpos($headers[0], '200') !== false) {
        echo "   ✅ File accessible via web\n";
    } else {
        echo "   ❌ File not accessible via web\n";
        echo "   💡 This might be a web server configuration issue\n";
    }
} catch (Exception $e) {
    echo "   ❌ Error creating test file: " . $e->getMessage() . "\n";
}

echo "\n🔧 TROUBLESHOOTING STEPS:\n";
echo "========================\n";
echo "1. If upload_max_filesize is too small, increase it in php.ini\n";
echo "2. If storage directories are not writable, run:\n";
echo "   sudo chown -R www-data:www-data storage/\n";
echo "   sudo chmod -R 755 storage/\n";
echo "3. If symlink is broken, run:\n";
echo "   sudo rm -rf public/storage\n";
echo "   sudo ln -s " . storage_path('app/public') . " public/storage\n";
echo "4. Check nginx configuration for client_max_body_size\n";
echo "5. Restart nginx and php-fpm after changes\n\n";

echo "💡 The nginx log shows file upload buffering, which is normal.\n";
echo "The issue is likely with storage permissions or symlink.\n"; 