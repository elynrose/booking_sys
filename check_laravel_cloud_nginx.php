<?php

// Check Laravel Cloud nginx configuration options
// Run this on your cloud server

echo "🔧 Laravel Cloud Nginx Configuration Check\n";
echo "==========================================\n\n";

// Load Laravel
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "1. Laravel Cloud Environment Check:\n";
echo "==================================\n";

$cloudVars = [
    'LARAVEL_CLOUD_ENVIRONMENT',
    'LARAVEL_CLOUD_APP_NAME',
    'LARAVEL_CLOUD_APP_ENV',
    'LARAVEL_CLOUD_DISK_CONFIG',
    'NGINX_CLIENT_MAX_BODY_SIZE',
    'NGINX_UPLOAD_MAX_FILESIZE',
    'NGINX_CUSTOM_CONFIG'
];

foreach ($cloudVars as $var) {
    $value = env($var);
    if ($value) {
        echo "   ✅ $var: $value\n";
    } else {
        echo "   ❌ $var: NOT SET\n";
    }
}

echo "\n2. Current PHP Upload Settings:\n";
echo "===============================\n";

$uploadSettings = [
    'upload_max_filesize' => ini_get('upload_max_filesize'),
    'post_max_size' => ini_get('post_max_size'),
    'max_file_uploads' => ini_get('max_file_uploads'),
    'memory_limit' => ini_get('memory_limit')
];

foreach ($uploadSettings as $setting => $value) {
    echo "   📝 $setting: $value\n";
}

echo "\n3. Laravel Cloud CLI Check:\n";
echo "===========================\n";

// Check if Laravel Cloud CLI is available
$output = shell_exec('which laravel-cloud 2>/dev/null');
if ($output) {
    echo "   ✅ Laravel Cloud CLI found: " . trim($output) . "\n";
    
    // Try to get configuration
    $config = shell_exec('laravel-cloud config:list 2>/dev/null');
    if ($config) {
        echo "   📋 Available configurations:\n";
        echo $config;
    } else {
        echo "   ❌ Could not get configuration list\n";
    }
} else {
    echo "   ❌ Laravel Cloud CLI not found\n";
    echo "   💡 Install with: composer global require laravel/cloud-cli\n";
}

echo "\n4. Nginx Configuration Suggestions:\n";
echo "==================================\n";

echo "For Laravel Cloud, try these commands:\n\n";

echo "# Check current configuration\n";
echo "laravel-cloud config:list\n\n";

echo "# Set nginx client max body size\n";
echo "laravel-cloud config:set nginx.client_max_body_size 100M\n\n";

echo "# Set custom nginx configuration\n";
echo "laravel-cloud config:set nginx.custom_config '\n";
echo "location /storage {\n";
echo "    try_files \$uri \$uri/ =404;\n";
echo "    expires 1y;\n";
echo "    add_header Cache-Control \"public, immutable\";\n";
echo "}\n";
echo "'\n\n";

echo "# Set environment variables\n";
echo "laravel-cloud config:set NGINX_CLIENT_MAX_BODY_SIZE 100M\n";
echo "laravel-cloud config:set NGINX_UPLOAD_MAX_FILESIZE 100M\n\n";

echo "5. Alternative Solutions:\n";
echo "========================\n";

echo "If nginx configuration isn't available:\n\n";

echo "1. Use Laravel Cloud's built-in file storage:\n";
echo "   - Files are automatically served via cloud URLs\n";
echo "   - No nginx configuration needed\n\n";

echo "2. Check Laravel Cloud documentation:\n";
echo "   - https://cloud.laravel.com/docs\n";
echo "   - Look for nginx configuration options\n\n";

echo "3. Contact Laravel Cloud support:\n";
echo "   - They can help with custom nginx configurations\n\n";

echo "4. Use environment variables:\n";
echo "   - Set PHP upload limits via environment variables\n";
echo "   - Laravel Cloud might respect these settings\n\n";

echo "🔧 MANUAL STEPS:\n";
echo "================\n";
echo "1. Install Laravel Cloud CLI: composer global require laravel/cloud-cli\n";
echo "2. Check available config: laravel-cloud config:list\n";
echo "3. Set nginx config if available\n";
echo "4. Deploy changes: laravel-cloud deploy\n";
echo "5. Check Laravel Cloud dashboard for nginx options\n\n";

echo "💡 Laravel Cloud handles most nginx configuration automatically.\n";
echo "The issue might be with PHP upload limits rather than nginx.\n"; 