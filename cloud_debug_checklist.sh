#!/bin/bash

echo "=== LARAVEL CLOUD PERMISSION DEBUG CHECKLIST ==="
echo ""

# 1. Clear all Laravel caches
echo "1. Clearing Laravel caches..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan permission:cache-reset
echo "✅ Caches cleared"
echo ""

# 2. Check environment configuration
echo "2. Checking environment configuration..."
echo "Database connection:"
php artisan tinker --execute="echo 'DB connected: ' . (DB::connection()->getPdo() ? 'YES' : 'NO') . PHP_EOL;"
echo ""

# 3. Check if roles and permissions exist
echo "3. Checking roles and permissions..."
php artisan tinker --execute="
echo 'Roles count: ' . App\Models\Role::count() . PHP_EOL;
echo 'Permissions count: ' . App\Models\Permission::count() . PHP_EOL;
echo 'Users count: ' . App\Models\User::count() . PHP_EOL;
"
echo ""

# 4. List all roles and their permissions
echo "4. Listing roles and permissions..."
php artisan roles:list-permissions
echo ""

# 5. Check specific role assignments
echo "5. Checking specific role assignments..."
php artisan tinker --execute="
echo 'Admin role exists: ' . (App\Models\Role::where('title', 'Admin')->exists() ? 'YES' : 'NO') . PHP_EOL;
echo 'User role exists: ' . (App\Models\Role::where('title', 'User')->exists() ? 'YES' : 'NO') . PHP_EOL;
echo 'Trainer role exists: ' . (App\Models\Role::where('title', 'Trainer')->exists() ? 'YES' : 'NO') . PHP_EOL;

\$adminRole = App\Models\Role::where('title', 'Admin')->first();
if (\$adminRole) {
    echo 'Admin permissions: ' . \$adminRole->permissions->count() . PHP_EOL;
}

\$userRole = App\Models\Role::where('title', 'User')->first();
if (\$userRole) {
    echo 'User permissions: ' . \$userRole->permissions->count() . PHP_EOL;
}
"
echo ""

# 6. Check for users with roles
echo "6. Checking users with roles..."
php artisan tinker --execute="
echo 'Users with Admin role: ' . App\Models\User::role('Admin')->count() . PHP_EOL;
echo 'Users with User role: ' . App\Models\User::role('User')->count() . PHP_EOL;
echo 'Users with Trainer role: ' . App\Models\User::role('Trainer')->count() . PHP_EOL;
"
echo ""

# 7. Check for common permission issues
echo "7. Checking for common permission issues..."
php artisan tinker --execute="
\$commonPermissions = ['dashboard_access', 'user_access', 'booking_access', 'payment_access', 'schedule_access'];
foreach (\$commonPermissions as \$perm) {
    \$exists = App\Models\Permission::where('title', \$perm)->exists();
    echo \$perm . ': ' . (\$exists ? 'EXISTS' : 'MISSING') . PHP_EOL;
}
"
echo ""

# 8. Optional: Reseed roles and permissions
echo "8. Do you want to reseed roles and permissions? (y/n)"
read -r response
if [[ "$response" =~ ^[Yy]$ ]]; then
    echo "Reseeding roles and permissions..."
    php artisan db:seed --class=AssignPermissionsToAdminSeeder
    echo "✅ Reseeding completed"
else
    echo "Skipping reseeding"
fi
echo ""

# 9. Final check
echo "9. Final verification..."
php artisan roles:list-permissions
echo ""

echo "=== CHECKLIST COMPLETED ==="
echo ""
echo "If you still have issues:"
echo "1. Check your .env file for correct database settings"
echo "2. Verify that your database has the correct data"
echo "3. Check for any custom middleware that might be blocking access"
echo "4. Review the Laravel logs: tail -f storage/logs/laravel.log" 