<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🔧 Cloud 500 Error Fix Script\n";
echo "=============================\n\n";

// 1. Clear all caches
echo "1. Clearing all caches...\n";
try {
    \Artisan::call('cache:clear');
    \Artisan::call('config:clear');
    \Artisan::call('route:clear');
    \Artisan::call('view:clear');
    echo "   ✅ All caches cleared\n";
} catch (Exception $e) {
    echo "   ❌ Cache clear failed: " . $e->getMessage() . "\n";
}

// 2. Set proper permissions
echo "\n2. Setting file permissions...\n";
$directories = ['storage/', 'bootstrap/cache/', 'public/storage/'];
foreach ($directories as $dir) {
    if (is_dir($dir)) {
        chmod($dir, 0775);
        echo "   ✅ Set permissions for {$dir}\n";
    }
}

// 3. Create storage link
echo "\n3. Creating storage link...\n";
try {
    if (!is_link('public/storage')) {
        \Artisan::call('storage:link');
        echo "   ✅ Storage link created\n";
    } else {
        echo "   ✅ Storage link already exists\n";
    }
} catch (Exception $e) {
    echo "   ❌ Storage link failed: " . $e->getMessage() . "\n";
}

// 4. Run migrations
echo "\n4. Running migrations...\n";
try {
    \Artisan::call('migrate', ['--force' => true]);
    echo "   ✅ Migrations completed\n";
} catch (Exception $e) {
    echo "   ❌ Migrations failed: " . $e->getMessage() . "\n";
}

// 5. Seed essential data
echo "\n5. Seeding essential data...\n";
$seeders = [
    'PermissionSeeder',
    'RoleSeeder', 
    'AssignPermissionsToAdminSeeder',
    'CategorySeeder',
    'SiteSettingsSeeder'
];

foreach ($seeders as $seeder) {
    try {
        \Artisan::call('db:seed', ['--class' => $seeder, '--force' => true]);
        echo "   ✅ {$seeder} completed\n";
    } catch (Exception $e) {
        echo "   ❌ {$seeder} failed: " . $e->getMessage() . "\n";
    }
}

// 6. Verify admin user
echo "\n6. Verifying admin user...\n";
try {
    \Artisan::call('user:verify-admin');
    echo "   ✅ Admin user verified\n";
} catch (Exception $e) {
    echo "   ❌ Admin verification failed: " . $e->getMessage() . "\n";
}

// 7. Optimize for production
echo "\n7. Optimizing for production...\n";
try {
    \Artisan::call('config:cache');
    \Artisan::call('route:cache');
    \Artisan::call('view:cache');
    echo "   ✅ Production optimization completed\n";
} catch (Exception $e) {
    echo "   ❌ Optimization failed: " . $e->getMessage() . "\n";
}

// 8. Check SiteSettings
echo "\n8. Checking SiteSettings...\n";
try {
    $settings = \App\Models\SiteSettings::getSettings();
    echo "   ✅ SiteSettings working: " . $settings->site_name . "\n";
} catch (Exception $e) {
    echo "   ❌ SiteSettings error: " . $e->getMessage() . "\n";
    
    // Try to create default settings
    try {
        \App\Models\SiteSettings::createDefaultSettings();
        echo "   ✅ Created default SiteSettings\n";
    } catch (Exception $e2) {
        echo "   ❌ Failed to create default settings: " . $e2->getMessage() . "\n";
    }
}

// 9. Test database connection
echo "\n9. Testing database connection...\n";
try {
    $pdo = DB::connection()->getPdo();
    echo "   ✅ Database connection working\n";
} catch (Exception $e) {
    echo "   ❌ Database connection failed: " . $e->getMessage() . "\n";
}

// 10. Check admin user permissions
echo "\n10. Checking admin permissions...\n";
try {
    $admin = \App\Models\User::where('email', 'admin@admin.com')->first();
    if ($admin) {
        $roles = $admin->roles->pluck('title')->toArray();
        if (in_array('Admin', $roles)) {
            echo "   ✅ Admin user has Admin role\n";
        } else {
            echo "   ⚠️  Admin user missing Admin role, fixing...\n";
            $adminRole = \App\Models\Role::where('title', 'Admin')->first();
            if ($adminRole) {
                $admin->roles()->sync([$adminRole->id]);
                echo "   ✅ Admin role assigned\n";
            }
        }
    } else {
        echo "   ❌ Admin user not found\n";
    }
} catch (Exception $e) {
    echo "   ❌ Admin check failed: " . $e->getMessage() . "\n";
}

echo "\n✅ Cloud 500 Error Fix Script completed!\n";
echo "\n📋 Next steps:\n";
echo "1. Test your admin panel\n";
echo "2. Check if 500 errors are resolved\n";
echo "3. If issues persist, run: php debug_cloud_500.php\n";
echo "4. Check logs: tail -f storage/logs/laravel.log\n"; 