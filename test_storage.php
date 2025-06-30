<?php

// Simple storage test script for cloud server
// Run this on your cloud server to test storage functionality

echo "🧪 Testing Storage Configuration\n";
echo "================================\n\n";

// Test 1: Check if storage symlink exists
echo "1. Checking storage symlink...\n";
if (is_link('public/storage')) {
    echo "   ✅ Storage symlink exists\n";
    echo "   📍 Points to: " . readlink('public/storage') . "\n";
} else {
    echo "   ❌ Storage symlink missing\n";
}

// Test 2: Check if storage directory exists
echo "\n2. Checking storage directory...\n";
if (is_dir('storage/app/public')) {
    echo "   ✅ Storage directory exists\n";
    $files = scandir('storage/app/public');
    echo "   📁 Files in storage: " . implode(', ', array_filter($files, function($f) { return $f != '.' && $f != '..'; })) . "\n";
} else {
    echo "   ❌ Storage directory missing\n";
    echo "   🔧 Creating storage directory structure...\n";
    mkdir('storage/app/public', 0755, true);
    mkdir('storage/app/public/site', 0755, true);
    mkdir('storage/app/public/trainers', 0755, true);
    mkdir('storage/app/public/schedules', 0755, true);
    echo "   ✅ Storage directories created\n";
}

// Test 3: Check if we can access a file through the symlink
echo "\n3. Testing file access through symlink...\n";
$testFile = 'public/storage/site/test.txt';
if (is_dir('storage/app/public/site')) {
    file_put_contents('storage/app/public/site/test.txt', 'test');
    if (file_exists($testFile)) {
        echo "   ✅ Can access files through symlink\n";
        unlink('storage/app/public/site/test.txt'); // Clean up
    } else {
        echo "   ❌ Cannot access files through symlink\n";
    }
} else {
    echo "   ❌ Site directory doesn't exist\n";
}

// Test 4: Check permissions
echo "\n4. Checking permissions...\n";
if (is_dir('storage')) {
    $storagePerms = substr(sprintf('%o', fileperms('storage')), -4);
    echo "   📁 Storage permissions: $storagePerms\n";
} else {
    echo "   ❌ Storage directory not found\n";
}

if (is_dir('public')) {
    $publicPerms = substr(sprintf('%o', fileperms('public')), -4);
    echo "   🌐 Public permissions: $publicPerms\n";
} else {
    echo "   ❌ Public directory not found\n";
}

// Test 5: Check current working directory
echo "\n5. Current working directory...\n";
echo "   📂 " . getcwd() . "\n";

// Test 6: Check if we're in the right place
echo "\n6. Checking Laravel files...\n";
if (file_exists('artisan')) {
    echo "   ✅ Laravel artisan file found\n";
} else {
    echo "   ❌ Laravel artisan file not found - wrong directory?\n";
}

echo "\n🎉 Storage test completed!\n";
echo "\n📋 Next steps:\n";
echo "1. Run: php artisan storage:link\n";
echo "2. Run: chmod -R 775 storage/\n";
echo "3. Run: chmod -R 775 public/storage/\n"; 