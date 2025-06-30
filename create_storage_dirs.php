<?php

// Simple script to create missing storage directories
// Run this on your cloud server

echo "📁 Creating Storage Directories\n";
echo "===============================\n\n";

// Load Laravel
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$directories = [
    'storage/app/public',
    'public/storage',
    'storage/app/public/site',
    'storage/app/public/trainers',
    'storage/app/public/schedules'
];

echo "Creating directories...\n";
echo "======================\n";

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

echo "\nFixing storage symlink...\n";
echo "========================\n";

$symlinkPath = public_path('storage');
$targetPath = storage_path('app/public');

echo "   🔗 Symlink path: $symlinkPath\n";
echo "   📁 Target path: $targetPath\n";

// Remove existing symlink/file if it exists
if (file_exists($symlinkPath)) {
    if (is_link($symlinkPath)) {
        unlink($symlinkPath);
        echo "   🔄 Removed existing symlink\n";
    } elseif (is_dir($symlinkPath)) {
        rmdir($symlinkPath);
        echo "   🔄 Removed existing directory\n";
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
    echo "   💡 Try manually: ln -s $targetPath $symlinkPath\n";
}

echo "\nSetting permissions...\n";
echo "=====================\n";

$permissionDirs = [
    'storage',
    'storage/app',
    'storage/app/public',
    'public/storage'
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

echo "\n🎉 Storage directories created!\n";
echo "==============================\n";
echo "Try uploading a file now.\n"; 