<?php

// Quick debug for specific image file
echo "🔍 Quick Debug for Image: 4R5IRtHFIGohpQ5zNRGQugQ9d5My77HJpHJUKafz.png\n";
echo "========================================================\n\n";

$imagePath = 'storage/app/public/site/4R5IRtHFIGohpQ5zNRGQugQ9d5My77HJpHJUKafz.png';
$symlinkPath = 'public/storage/site/4R5IRtHFIGohpQ5zNRGQugQ9d5My77HJpHJUKafz.png';

echo "1. Checking if file exists in storage:\n";
if (file_exists($imagePath)) {
    echo "   ✅ File exists: $imagePath\n";
    echo "   📏 Size: " . filesize($imagePath) . " bytes\n";
    echo "   🔐 Permissions: " . substr(sprintf('%o', fileperms($imagePath)), -4) . "\n";
} else {
    echo "   ❌ File missing: $imagePath\n";
}

echo "\n2. Checking symlink access:\n";
if (file_exists($symlinkPath)) {
    echo "   ✅ File accessible through symlink: $symlinkPath\n";
} else {
    echo "   ❌ File not accessible through symlink: $symlinkPath\n";
}

echo "\n3. Checking symlink itself:\n";
if (is_link('public/storage')) {
    echo "   ✅ Symlink exists\n";
    echo "   📍 Points to: " . readlink('public/storage') . "\n";
} else {
    echo "   ❌ Symlink missing\n";
}

echo "\n4. Testing web server access:\n";
$testUrl = 'https://yourdomain.com/storage/site/4R5IRtHFIGohpQ5zNRGQugQ9d5My77HJpHJUKafz.png';
echo "   🔗 Try accessing: $testUrl\n";

echo "\n5. Checking all files in site directory:\n";
$siteDir = 'storage/app/public/site';
if (is_dir($siteDir)) {
    $files = scandir($siteDir);
    foreach ($files as $file) {
        if ($file != '.' && $file != '..') {
            echo "   📁 $file\n";
        }
    }
} else {
    echo "   ❌ Site directory doesn't exist\n";
}

echo "\n6. Checking web server configuration:\n";
echo "   🌐 Current working directory: " . getcwd() . "\n";
echo "   📂 Document root check: " . (file_exists('public/index.php') ? '✅' : '❌') . " public/index.php exists\n";

echo "\n🎯 Next Steps:\n";
echo "1. Try accessing the image directly in your browser\n";
echo "2. Check browser developer tools (F12) for any errors\n";
echo "3. Check if your web server (Apache/Nginx) is configured correctly\n";
echo "4. Try uploading a smaller image (< 500KB) to test\n"; 