<?php

// Improved fix script for folder permissions
// Run this on your cloud server to fix permission issues

echo "🔧 Folder Permissions Fix v2\n";
echo "============================\n\n";

// Load Laravel
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "1. Checking current directory permissions...\n";
echo "==========================================\n";

$directories = [
    'storage',
    'storage/app',
    'storage/app/public',
    'storage/framework',
    'storage/framework/cache',
    'storage/framework/sessions',
    'storage/framework/views',
    'storage/logs',
    'bootstrap/cache',
    'public/storage'
];

foreach ($directories as $dir) {
    if (file_exists($dir)) {
        $perms = substr(sprintf('%o', fileperms($dir)), -4);
        $owner = posix_getpwuid(fileowner($dir));
        $group = posix_getgrgid(filegroup($dir));
        
        echo "   📁 $dir:\n";
        echo "      Permissions: $perms\n";
        echo "      Owner: " . $owner['name'] . "\n";
        echo "      Group: " . $group['name'] . "\n";
        
        // Check if writable
        if (is_writable($dir)) {
            echo "      ✅ Writable\n";
        } else {
            echo "      ❌ NOT WRITABLE\n";
        }
        echo "\n";
    } else {
        echo "   ❌ $dir: Directory not found\n\n";
    }
}

echo "2. Checking web server user...\n";
echo "=============================\n";

// Try to determine web server user
$webUser = 'www-data'; // Default for most Linux servers
$webGroup = 'www-data';

// Check common web server users
$possibleUsers = ['www-data', 'apache', 'nginx', 'httpd', 'web'];
$currentUser = posix_getpwuid(posix_geteuid())['name'];

echo "   👤 Current user: $currentUser\n";

foreach ($possibleUsers as $user) {
    if (posix_getpwnam($user)) {
        echo "   ✅ Web server user '$user' exists\n";
        $webUser = $user;
        break;
    }
}

echo "   🎯 Using web server user: $webUser\n\n";

echo "3. Fixing permissions...\n";
echo "=======================\n";

// Fix storage directory permissions
$storageDirs = [
    'storage',
    'storage/app',
    'storage/app/public',
    'storage/framework',
    'storage/framework/cache',
    'storage/framework/sessions',
    'storage/framework/views',
    'storage/logs',
    'bootstrap/cache'
];

foreach ($storageDirs as $dir) {
    if (file_exists($dir)) {
        // Set directory permissions to 755
        if (chmod($dir, 0755)) {
            echo "   ✅ Set $dir permissions to 755\n";
        } else {
            echo "   ❌ Failed to set $dir permissions\n";
        }
        
        // Try to change ownership (requires sudo)
        if (function_exists('posix_getuid') && posix_getuid() === 0) {
            if (chown($dir, $webUser) && chgrp($dir, $webGroup)) {
                echo "   ✅ Changed $dir ownership to $webUser:$webGroup\n";
            } else {
                echo "   ⚠️  Could not change $dir ownership (run with sudo)\n";
            }
        } else {
            echo "   ⚠️  Cannot change ownership (not running as root)\n";
        }
    }
}

echo "\n4. Creating missing directories...\n";
echo "================================\n";

$missingDirs = [];
foreach ($directories as $dir) {
    if (!file_exists($dir)) {
        $missingDirs[] = $dir;
        if (mkdir($dir, 0755, true)) {
            echo "   ✅ Created $dir\n";
        } else {
            echo "   ❌ Failed to create $dir\n";
        }
    }
}

echo "\n5. Fixing storage symlink...\n";
echo "===========================\n";

$symlinkPath = public_path('storage');
$targetPath = storage_path('app/public');

echo "   🔗 Symlink path: $symlinkPath\n";
echo "   📁 Target path: $targetPath\n";

// Check if symlink exists
if (is_link($symlinkPath)) {
    echo "   ✅ Storage symlink exists\n";
    $target = readlink($symlinkPath);
    echo "   📁 Points to: $target\n";
    
    if ($target !== $targetPath) {
        echo "   ⚠️  Symlink points to wrong location\n";
        if (unlink($symlinkPath)) {
            echo "   🔄 Removed incorrect symlink\n";
        } else {
            echo "   ❌ Failed to remove incorrect symlink\n";
        }
    } else {
        echo "   ✅ Symlink is correct\n";
    }
} elseif (file_exists($symlinkPath)) {
    echo "   ⚠️  File/directory exists at symlink location\n";
    if (is_dir($symlinkPath)) {
        echo "   📁 It's a directory, removing it...\n";
        if (rmdir($symlinkPath)) {
            echo "   ✅ Removed directory\n";
        } else {
            echo "   ❌ Failed to remove directory (may not be empty)\n";
            echo "   💡 You may need to manually remove: $symlinkPath\n";
        }
    } else {
        echo "   📄 It's a file, removing it...\n";
        if (unlink($symlinkPath)) {
            echo "   ✅ Removed file\n";
        } else {
            echo "   ❌ Failed to remove file\n";
        }
    }
} else {
    echo "   ❌ No symlink or file exists\n";
}

// Create symlink if it doesn't exist
if (!file_exists($symlinkPath)) {
    if (symlink($targetPath, $symlinkPath)) {
        echo "   ✅ Created storage symlink\n";
    } else {
        echo "   ❌ Failed to create storage symlink\n";
        echo "   💡 Try running: ln -s $targetPath $symlinkPath\n";
    }
}

echo "\n6. Testing file upload permissions...\n";
echo "====================================\n";

// Test if we can write to storage
try {
    $testFile = storage_path('app/public/test-' . uniqid() . '.txt');
    $testContent = 'test-' . time();
    
    if (file_put_contents($testFile, $testContent)) {
        echo "   ✅ Can write to storage directory\n";
        
        if (file_exists($testFile)) {
            echo "   ✅ Can read from storage directory\n";
            unlink($testFile);
            echo "   ✅ Can delete from storage directory\n";
        }
    } else {
        echo "   ❌ Cannot write to storage directory\n";
    }
} catch (Exception $e) {
    echo "   ❌ Error testing storage: " . $e->getMessage() . "\n";
}

echo "\n7. Checking upload directory...\n";
echo "==============================\n";

$uploadDirs = [
    'storage/app/public/site',
    'storage/app/public/trainers',
    'storage/app/public/schedules'
];

foreach ($uploadDirs as $dir) {
    if (!file_exists($dir)) {
        if (mkdir($dir, 0755, true)) {
            echo "   ✅ Created $dir\n";
        } else {
            echo "   ❌ Failed to create $dir\n";
        }
    } else {
        echo "   ✅ $dir exists\n";
    }
}

echo "\n8. Testing web access...\n";
echo "======================\n";

$testUrl = env('APP_URL') . '/storage/test-access.txt';
echo "   🔗 Test URL: $testUrl\n";

// Create a test file
try {
    Storage::disk('public')->put('test-access.txt', 'test-' . time());
    echo "   ✅ Test file created\n";
    
    // Check if file exists via web
    $headers = get_headers($testUrl);
    if ($headers && strpos($headers[0], '200') !== false) {
        echo "   ✅ File accessible via web\n";
    } else {
        echo "   ❌ File not accessible via web\n";
    }
} catch (Exception $e) {
    echo "   ❌ Could not create test file: " . $e->getMessage() . "\n";
}

echo "\n🔧 MANUAL STEPS (if needed):\n";
echo "============================\n";
echo "If permissions are still not working, run these commands:\n\n";

echo "sudo chown -R $webUser:$webGroup storage/\n";
echo "sudo chown -R $webUser:$webGroup bootstrap/cache/\n";
echo "sudo chmod -R 755 storage/\n";
echo "sudo chmod -R 755 bootstrap/cache/\n";
echo "sudo chmod -R 775 storage/app/public/\n";
echo "sudo chmod -R 775 storage/logs/\n";
echo "sudo chmod -R 775 storage/framework/cache/\n";
echo "sudo chmod -R 775 storage/framework/sessions/\n";
echo "sudo chmod -R 775 storage/framework/views/\n";
echo "sudo rm -rf public/storage\n";
echo "sudo ln -s " . storage_path('app/public') . " public/storage\n\n";

echo "🎉 Permission fix complete!\n";
echo "==========================\n";
echo "Try uploading a file now. If it still doesn't work,\n";
echo "run the manual commands above with sudo.\n"; 