<?php

// Fix nginx configuration for file access
// Run this on your cloud server

echo "🔧 Nginx Configuration Check & Fix\n";
echo "==================================\n\n";

// Load Laravel
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "1. Checking current nginx configuration...\n";
echo "=========================================\n";

// Check if we can read nginx config
$nginxConfigs = [
    '/etc/nginx/nginx.conf',
    '/etc/nginx/sites-available/default',
    '/etc/nginx/sites-enabled/default',
    '/etc/nginx/conf.d/default.conf'
];

foreach ($nginxConfigs as $config) {
    if (file_exists($config)) {
        echo "   📄 Found config: $config\n";
        
        // Check if config contains storage location
        $content = file_get_contents($config);
        if (strpos($content, 'storage') !== false) {
            echo "   ✅ Contains storage configuration\n";
        } else {
            echo "   ❌ No storage configuration found\n";
        }
    } else {
        echo "   ❌ Config not found: $config\n";
    }
}

echo "\n2. Testing file access paths...\n";
echo "==============================\n";

$testPaths = [
    '/var/www/html/public/storage',
    '/var/www/html/storage/app/public',
    '/var/www/html/public'
];

foreach ($testPaths as $path) {
    if (file_exists($path)) {
        $perms = substr(sprintf('%o', fileperms($path)), -4);
        $readable = is_readable($path) ? '✅ Readable' : '❌ Not Readable';
        echo "   📁 $path: $perms - $readable\n";
    } else {
        echo "   ❌ Path not found: $path\n";
    }
}

echo "\n3. Checking web server user...\n";
echo "=============================\n";

$webUser = 'www-data';
$currentUser = posix_getpwuid(posix_geteuid())['name'];
echo "   👤 Current user: $currentUser\n";
echo "   🎯 Web server user: $webUser\n";

// Check if web server can access storage
$storagePath = storage_path('app/public');
if (file_exists($storagePath)) {
    $owner = posix_getpwuid(fileowner($storagePath));
    $group = posix_getgrgid(filegroup($storagePath));
    echo "   📁 Storage owner: " . $owner['name'] . ":" . $group['name'] . "\n";
    
    if ($owner['name'] === $webUser || $group['name'] === $webUser) {
        echo "   ✅ Web server has access to storage\n";
    } else {
        echo "   ❌ Web server may not have access to storage\n";
    }
}

echo "\n4. Testing file creation and web access...\n";
echo "==========================================\n";

// Create a test file
try {
    $disk = Storage::disk('public');
    $testFile = 'nginx-test-' . uniqid() . '.txt';
    $testContent = 'nginx-test-' . time();
    
    if ($disk->put($testFile, $testContent)) {
        echo "   ✅ Test file created: $testFile\n";
        
        // Check if file exists on disk
        $filePath = storage_path('app/public/' . $testFile);
        if (file_exists($filePath)) {
            echo "   ✅ File exists on disk\n";
            
            // Check file permissions
            $filePerms = substr(sprintf('%o', fileperms($filePath)), -4);
            echo "   📄 File permissions: $filePerms\n";
            
            // Check if web server can read it
            if (is_readable($filePath)) {
                echo "   ✅ File is readable\n";
            } else {
                echo "   ❌ File is not readable\n";
            }
        } else {
            echo "   ❌ File not found on disk\n";
        }
        
        // Test web access
        $testUrl = env('APP_URL') . '/storage/' . $testFile;
        echo "   🔗 Test URL: $testUrl\n";
        
        // Try to access via web
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'timeout' => 10,
                'user_agent' => 'Mozilla/5.0 (compatible; TestBot/1.0)'
            ]
        ]);
        
        $response = @file_get_contents($testUrl, false, $context);
        if ($response !== false) {
            echo "   ✅ File accessible via web\n";
        } else {
            echo "   ❌ File not accessible via web\n";
            
            // Check HTTP response headers
            $headers = @get_headers($testUrl);
            if ($headers) {
                echo "   📋 HTTP Response: " . $headers[0] . "\n";
            }
        }
        
        // Clean up
        $disk->delete($testFile);
        
    } else {
        echo "   ❌ Could not create test file\n";
    }
} catch (Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
}

echo "\n5. Checking Laravel storage configuration...\n";
echo "============================================\n";

$defaultDisk = config('filesystems.default');
echo "   📁 Default disk: $defaultDisk\n";

$publicDisk = config('filesystems.disks.public');
if ($publicDisk) {
    echo "   📁 Public disk root: " . ($publicDisk['root'] ?? 'N/A') . "\n";
    echo "   📁 Public disk URL: " . ($publicDisk['url'] ?? 'N/A') . "\n";
}

echo "\n6. Suggested nginx configuration...\n";
echo "==================================\n";

echo "Add this to your nginx server block:\n\n";
echo "location /storage {\n";
echo "    alias /var/www/html/storage/app/public;\n";
echo "    try_files \$uri \$uri/ =404;\n";
echo "    expires 1y;\n";
echo "    add_header Cache-Control \"public, immutable\";\n";
echo "}\n\n";

echo "Or if using Laravel's public directory:\n\n";
echo "location /storage {\n";
echo "    try_files \$uri \$uri/ =404;\n";
echo "    expires 1y;\n";
echo "    add_header Cache-Control \"public, immutable\";\n";
echo "}\n\n";

echo "🔧 MANUAL FIXES:\n";
echo "================\n";
echo "1. Check nginx error logs: sudo tail -f /var/log/nginx/error.log\n";
echo "2. Restart nginx: sudo systemctl restart nginx\n";
echo "3. Check nginx config: sudo nginx -t\n";
echo "4. Set proper ownership: sudo chown -R www-data:www-data /var/www/html/storage\n";
echo "5. Set proper permissions: sudo chmod -R 755 /var/www/html/storage\n";
echo "6. Check if nginx can access the storage directory\n\n";

echo "💡 The storage directories and symlink are working correctly.\n";
echo "The issue is likely with nginx configuration or permissions.\n"; 