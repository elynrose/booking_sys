<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🔍 Cloud 500 Error Debug Script\n";
echo "================================\n\n";

// 1. Check environment variables
echo "1. Environment Variables:\n";
$requiredEnvVars = [
    'APP_NAME',
    'APP_ENV', 
    'APP_KEY',
    'APP_URL',
    'DB_CONNECTION',
    'DB_HOST',
    'DB_DATABASE',
    'DB_USERNAME',
    'DB_PASSWORD',
    'CACHE_DRIVER',
    'SESSION_DRIVER',
    'QUEUE_CONNECTION'
];

foreach ($requiredEnvVars as $var) {
    $value = env($var);
    if (empty($value)) {
        echo "   ❌ {$var}: NOT SET\n";
    } else {
        echo "   ✅ {$var}: " . (strlen($value) > 20 ? substr($value, 0, 20) . '...' : $value) . "\n";
    }
}

// 2. Check database connection
echo "\n2. Database Connection:\n";
try {
    $pdo = DB::connection()->getPdo();
    echo "   ✅ Database connected successfully\n";
    echo "   📊 Database: " . $pdo->query('SELECT current_database()')->fetchColumn() . "\n";
} catch (Exception $e) {
    echo "   ❌ Database connection failed: " . $e->getMessage() . "\n";
}

// 3. Check file permissions
echo "\n3. File Permissions:\n";
$directories = [
    'storage' => 'storage/',
    'bootstrap/cache' => 'bootstrap/cache/',
    'public/storage' => 'public/storage/',
    'storage/logs' => 'storage/logs/',
    'storage/framework' => 'storage/framework/',
    'storage/app' => 'storage/app/'
];

foreach ($directories as $name => $path) {
    if (is_dir($path)) {
        $perms = substr(sprintf('%o', fileperms($path)), -4);
        $writable = is_writable($path);
        echo "   " . ($writable ? "✅" : "❌") . " {$name}: {$perms} " . ($writable ? "(writable)" : "(not writable)") . "\n";
    } else {
        echo "   ❌ {$name}: Directory not found\n";
    }
}

// 4. Check storage link
echo "\n4. Storage Link:\n";
if (is_link('public/storage')) {
    echo "   ✅ Storage link exists\n";
    $target = readlink('public/storage');
    echo "   📁 Target: {$target}\n";
} else {
    echo "   ❌ Storage link missing\n";
}

// 5. Check SiteSettings
echo "\n5. SiteSettings:\n";
try {
    $settings = \App\Models\SiteSettings::getSettings();
    echo "   ✅ SiteSettings loaded: " . $settings->site_name . "\n";
} catch (Exception $e) {
    echo "   ❌ SiteSettings error: " . $e->getMessage() . "\n";
}

// 6. Check admin user
echo "\n6. Admin User:\n";
try {
    $admin = \App\Models\User::where('email', 'admin@admin.com')->first();
    if ($admin) {
        echo "   ✅ Admin user exists: " . $admin->name . "\n";
        $roles = $admin->roles->pluck('name')->toArray();
        echo "   👥 Roles: " . implode(', ', $roles) . "\n";
    } else {
        echo "   ❌ Admin user not found\n";
    }
} catch (Exception $e) {
    echo "   ❌ Admin user check failed: " . $e->getMessage() . "\n";
}

// 7. Check recent errors
echo "\n7. Recent Errors:\n";
$logFile = 'storage/logs/laravel.log';
if (file_exists($logFile)) {
    $lines = file($logFile);
    $recentLines = array_slice($lines, -10);
    foreach ($recentLines as $line) {
        if (strpos($line, 'ERROR') !== false || strpos($line, 'Exception') !== false) {
            echo "   ⚠️  " . trim($line) . "\n";
        }
    }
} else {
    echo "   ❌ Log file not found\n";
}

// 8. Check cache status
echo "\n8. Cache Status:\n";
try {
    $cacheDriver = config('cache.default');
    echo "   📦 Cache driver: {$cacheDriver}\n";
    
    Cache::put('test_key', 'test_value', 60);
    $value = Cache::get('test_key');
    echo "   " . ($value === 'test_value' ? "✅" : "❌") . " Cache working\n";
} catch (Exception $e) {
    echo "   ❌ Cache error: " . $e->getMessage() . "\n";
}

// 9. Check route caching
echo "\n9. Route Caching:\n";
$routeCache = 'bootstrap/cache/routes.php';
if (file_exists($routeCache)) {
    echo "   ✅ Route cache exists\n";
} else {
    echo "   ❌ Route cache missing\n";
}

// 10. Check view caching
echo "\n10. View Caching:\n";
$viewCache = 'storage/framework/views/';
if (is_dir($viewCache) && count(scandir($viewCache)) > 2) {
    echo "   ✅ View cache exists\n";
} else {
    echo "   ❌ View cache missing or empty\n";
}

echo "\n🔧 Recommended fixes:\n";
echo "1. If database connection fails: Check DB credentials in .env\n";
echo "2. If permissions are wrong: Run 'chmod -R 775 storage/ bootstrap/cache/'\n";
echo "3. If storage link missing: Run 'php artisan storage:link'\n";
echo "4. If caches missing: Run 'php artisan config:cache && php artisan route:cache && php artisan view:cache'\n";
echo "5. If SiteSettings fails: Run 'php artisan db:seed --class=SiteSettingsSeeder --force'\n";
echo "6. If admin user missing: Run 'php artisan user:verify-admin'\n";
echo "\n📝 For detailed error logs, check: storage/logs/laravel.log\n"; 