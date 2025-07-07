<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🔧 Admin Home 500 Error Fix\n";
echo "===========================\n\n";

// 1. Clear all caches
echo "1. Clearing all caches...\n";
try {
    \Artisan::call('cache:clear');
    \Artisan::call('config:clear');
    \Artisan::call('route:clear');
    \Artisan::call('view:clear');
    \Artisan::call('permission:cache-reset');
    echo "   ✅ All caches cleared\n";
} catch (Exception $e) {
    echo "   ❌ Cache clear failed: " . $e->getMessage() . "\n";
}

// 2. Ensure database is up to date
echo "\n2. Running migrations...\n";
try {
    \Artisan::call('migrate', ['--force' => true]);
    echo "   ✅ Migrations completed\n";
} catch (Exception $e) {
    echo "   ❌ Migrations failed: " . $e->getMessage() . "\n";
}

// 3. Seed essential data
echo "\n3. Seeding essential data...\n";
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

// 4. Create or verify admin user
echo "\n4. Creating/verifying admin user...\n";
try {
    $adminEmail = 'admin@example.com';
    $adminUser = \App\Models\User::where('email', $adminEmail)->first();
    
    if (!$adminUser) {
        // Create admin user
        $adminUser = \App\Models\User::create([
            'name' => 'Admin User',
            'email' => $adminEmail,
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
            'is_admin' => true,
        ]);
        echo "   ✅ Created admin user: {$adminEmail}\n";
    } else {
        echo "   ✅ Admin user exists: {$adminEmail}\n";
    }
    
    // Ensure admin user has Admin role
    $adminRole = \App\Models\Role::where('title', 'Admin')->first();
    if ($adminRole && !$adminUser->hasRole('Admin')) {
        $adminUser->roles()->sync([$adminRole->id]);
        echo "   ✅ Assigned Admin role to user\n";
    } elseif ($adminUser->hasRole('Admin')) {
        echo "   ✅ Admin user already has Admin role\n";
    }
    
} catch (Exception $e) {
    echo "   ❌ Admin user setup failed: " . $e->getMessage() . "\n";
}

// 5. Verify permissions exist
echo "\n5. Verifying permissions...\n";
try {
    $requiredPermissions = [
        'dashboard_access',
        'user_access',
        'booking_access',
        'payment_access',
        'schedule_access',
        'trainer_access',
        'category_access',
        'role_access',
        'permission_access'
    ];
    
    foreach ($requiredPermissions as $permission) {
        $perm = \App\Models\Permission::where('title', $permission)->first();
        if (!$perm) {
            echo "   ⚠️  Missing permission: {$permission}\n";
        } else {
            echo "   ✅ Permission exists: {$permission}\n";
        }
    }
} catch (Exception $e) {
    echo "   ❌ Permission check failed: " . $e->getMessage() . "\n";
}

// 6. Test HomeController components
echo "\n6. Testing HomeController components...\n";
try {
    // Test basic queries
    $totalBookings = \App\Models\Booking::count();
    echo "   ✅ Total bookings: {$totalBookings}\n";
    
    $totalRevenue = \App\Models\Payment::where('payments.status', 'paid')->sum('amount') ?: 0;
    echo "   ✅ Total revenue: {$totalRevenue}\n";
    
    $totalUsers = \App\Models\User::count();
    echo "   ✅ Total users: {$totalUsers}\n";
    
    // Test complex queries
    $potentialRevenue = \App\Models\Payment::where('payments.status', 'paid')
        ->join('bookings', 'payments.booking_id', '=', 'bookings.id')
        ->join('schedules', 'bookings.schedule_id', '=', 'schedules.id')
        ->sum(DB::raw('schedules.price'));
    echo "   ✅ Complex query: {$potentialRevenue}\n";
    
} catch (Exception $e) {
    echo "   ❌ Query test failed: " . $e->getMessage() . "\n";
}

// 7. Test view rendering
echo "\n7. Testing view rendering...\n";
try {
    $view = view('admin.dashboard');
    echo "   ✅ Admin dashboard view renders\n";
} catch (Exception $e) {
    echo "   ❌ View rendering failed: " . $e->getMessage() . "\n";
}

// 8. Test SiteSettings
echo "\n8. Testing SiteSettings...\n";
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

// 9. Optimize for production
echo "\n9. Optimizing for production...\n";
try {
    \Artisan::call('config:cache');
    \Artisan::call('route:cache');
    \Artisan::call('view:cache');
    echo "   ✅ Production optimization completed\n";
} catch (Exception $e) {
    echo "   ❌ Optimization failed: " . $e->getMessage() . "\n";
}

// 10. Final verification
echo "\n10. Final verification...\n";
try {
    $adminUser = \App\Models\User::where('email', 'admin@example.com')->first();
    if ($adminUser && $adminUser->can('dashboard_access')) {
        echo "   ✅ Admin user has dashboard access\n";
    } else {
        echo "   ❌ Admin user missing dashboard access\n";
    }
} catch (Exception $e) {
    echo "   ❌ Final verification failed: " . $e->getMessage() . "\n";
}

echo "\n✅ Admin Home 500 Error Fix completed!\n";
echo "\n📋 Next steps:\n";
echo "1. Test admin login with admin@example.com / password\n";
echo "2. Access /admin route\n";
echo "3. If issues persist, check cloud logs\n";
echo "4. Run this script on the cloud server\n"; 