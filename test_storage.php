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
}

// Test 3: Check if we can access a file through the symlink
echo "\n3. Testing file access through symlink...\n";
$testFile = 'public/storage/site/test.txt';
file_put_contents('storage/app/public/site/test.txt', 'test');
if (file_exists($testFile)) {
    echo "   ✅ Can access files through symlink\n";
    unlink('storage/app/public/site/test.txt'); // Clean up
} else {
    echo "   ❌ Cannot access files through symlink\n";
}

// Test 4: Check Laravel asset helper
echo "\n4. Testing Laravel asset helper...\n";
$assetUrl = asset('storage/site/test.txt');
echo "   🔗 Asset URL: $assetUrl\n";

// Test 5: Check permissions
echo "\n5. Checking permissions...\n";
$storagePerms = substr(sprintf('%o', fileperms('storage')), -4);
$publicPerms = substr(sprintf('%o', fileperms('public')), -4);
echo "   📁 Storage permissions: $storagePerms\n";
echo "   🌐 Public permissions: $publicPerms\n";

echo "\n🎉 Storage test completed!\n"; 