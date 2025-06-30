<?php

// Targeted fix for storage directories and symlink
// Run this on your cloud server

echo "🔧 Fixing Storage Directories & Symlink\n";
echo "=======================================\n\n";

// Load Laravel
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "1. Creating missing storage directories...\n";
echo "==========================================\n";

$directories = [
    'storage/app/public',
    'storage/app/public/site',
    'storage/app/public/trainers',
    'storage/app/public/schedules'
];

foreach ($directories as $dir) {
    if (!file_exists($dir)) {
        if (mkdir($dir, 0755, true)) {
            echo "   ✅ Created: $dir\n";
        } else {
            echo "   ❌ Failed to create: $dir\n";
        }
    } else {
        echo "   ✅ Already exists: $dir\n";
    }
}

echo "\n2. Fixing storage symlink...\n";
echo "============================\n";

$symlinkPath = public_path('storage');
$targetPath = storage_path('app/public');

echo "   🔗 Symlink path: $symlinkPath\n";
echo "   📁 Target path: $targetPath\n";

// Remove existing symlink/file/directory if it exists
if (file_exists($symlinkPath)) {
    if (is_link($symlinkPath)) {
        unlink($symlinkPath);
        echo "   🔄 Removed existing symlink\n";
    } elseif (is_dir($symlinkPath)) {
        // Try to remove directory (might fail if not empty)
        if (rmdir($symlinkPath)) {
            echo "   🔄 Removed existing directory\n";
        } else {
            echo "   ⚠️  Directory not empty, trying to remove recursively...\n";
            system("rm -rf $symlinkPath");
            echo "   🔄 Removed directory recursively\n";
        }
    } else {
        unlink($symlinkPath);
        echo "   🔄 Removed existing file\n";
    }
}

// Create new symlink
if (symlink($targetPath, $symlinkPath)) {
    echo "   ✅ Created storage symlink\n";
} else {
    echo "   ❌ Failed to create symlink\n";
    echo "   💡 Trying manual command...\n";
    system("ln -s $targetPath $symlinkPath");
    if (is_link($symlinkPath)) {
        echo "   ✅ Symlink created manually\n";
    } else {
        echo "   ❌ Manual symlink creation failed\n";
    }
}

echo "\n3. Setting permissions...\n";
echo "========================\n";

$permissionDirs = [
    'storage',
    'storage/app',
    'storage/app/public',
    'storage/app/public/site',
    'storage/app/public/trainers',
    'storage/app/public/schedules'
];

foreach ($permissionDirs as $dir) {
    if (file_exists($dir)) {
        if (chmod($dir, 0755)) {
            echo "   ✅ Set permissions for: $dir\n";
        } else {
            echo "   ❌ Failed to set permissions for: $dir\n";
        }
    }
}

echo "\n4. Testing file operations...\n";
echo "============================\n";

// Test direct file write
try {
    $testFile = storage_path('app/public/test-direct-' . uniqid() . '.txt');
    $testContent = 'direct-test-' . time();
    
    if (file_put_contents($testFile, $testContent)) {
        echo "   ✅ Direct file write successful\n";
        
        if (file_exists($testFile)) {
            echo "   ✅ Direct file read successful\n";
            unlink($testFile);
            echo "   ✅ Direct file delete successful\n";
        }
    } else {
        echo "   ❌ Direct file write failed\n";
    }
} catch (Exception $e) {
    echo "   ❌ Direct file error: " . $e->getMessage() . "\n";
}

// Test Laravel Storage
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

echo "\n5. Testing web access...\n";
echo "=======================\n";

try {
    $disk = Storage::disk('public');
    $testFile = 'test-web-' . uniqid() . '.txt';
    $testContent = 'web-test-' . time();
    
    if ($disk->put($testFile, $testContent)) {
        echo "   ✅ Test file created: $testFile\n";
        
        $testUrl = env('APP_URL') . '/storage/' . $testFile;
        echo "   🔗 Test URL: $testUrl\n";
        
        // Check if accessible via web
        $headers = @get_headers($testUrl);
        if ($headers && strpos($headers[0], '200') !== false) {
            echo "   ✅ File accessible via web\n";
        } else {
            echo "   ❌ File not accessible via web\n";
            echo "   💡 This might be a web server configuration issue\n";
        }
        
        // Clean up
        $disk->delete($testFile);
    } else {
        echo "   ❌ Could not create test file\n";
    }
} catch (Exception $e) {
    echo "   ❌ Web test error: " . $e->getMessage() . "\n";
}

echo "\n6. Final verification...\n";
echo "=======================\n";

$verifyDirs = [
    'storage/app/public',
    'public/storage',
    'storage/app/public/site',
    'storage/app/public/trainers',
    'storage/app/public/schedules'
];

foreach ($verifyDirs as $dir) {
    if (file_exists($dir)) {
        $perms = substr(sprintf('%o', fileperms($dir)), -4);
        $writable = is_writable($dir) ? '✅ Writable' : '❌ Not Writable';
        echo "   📁 $dir: $perms - $writable\n";
    } else {
        echo "   ❌ $dir: Still missing\n";
    }
}

// Check symlink
if (is_link(public_path('storage'))) {
    $target = readlink(public_path('storage'));
    echo "   🔗 Symlink: " . public_path('storage') . " -> $target\n";
} else {
    echo "   ❌ Symlink still missing\n";
}

echo "\n🎉 Storage fix complete!\n";
echo "=======================\n";
echo "Try uploading a file now. If web access still doesn't work,\n";
echo "the issue might be with nginx configuration.\n"; 