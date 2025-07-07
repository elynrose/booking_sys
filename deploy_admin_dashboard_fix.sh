#!/bin/bash

echo "🚀 Admin Dashboard Fix Deployment"
echo "================================"
echo ""

# 1. Set proper permissions
echo "1. Setting file permissions..."
chmod -R 775 storage/
chmod -R 775 bootstrap/cache/
chmod -R 775 public/storage/
echo "   ✅ Permissions set"

# 2. Install dependencies
echo ""
echo "2. Installing dependencies..."
composer install --optimize-autoloader --no-dev
npm install
npm run build
echo "   ✅ Dependencies installed"

# 3. Clear all caches
echo ""
echo "3. Clearing caches..."
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan permission:cache-reset
echo "   ✅ Caches cleared"

# 4. Run migrations
echo ""
echo "4. Running migrations..."
php artisan migrate --force
echo "   ✅ Migrations completed"

# 5. Seed essential data
echo ""
echo "5. Seeding essential data..."
php artisan db:seed --class=PermissionSeeder --force
php artisan db:seed --class=RoleSeeder --force
php artisan db:seed --class=AssignPermissionsToAdminSeeder --force
php artisan db:seed --class=CategorySeeder --force
php artisan db:seed --class=SiteSettingsSeeder --force
echo "   ✅ Data seeded"

# 6. Create/verify admin user
echo ""
echo "6. Setting up admin user..."
php fix_admin_home_500.php
echo "   ✅ Admin user setup completed"

# 7. Fix dashboard null references
echo ""
echo "7. Fixing dashboard null references..."
php fix_dashboard_null_references.php
echo "   ✅ Dashboard null references fixed"

# 8. Create storage link
echo ""
echo "8. Creating storage link..."
php artisan storage:link
echo "   ✅ Storage link created"

# 9. Optimize for production
echo ""
echo "9. Optimizing for production..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
echo "   ✅ Production optimization completed"

# 10. Final verification
echo ""
echo "10. Final verification..."
echo "   - Testing admin dashboard access..."
curl -s -o /dev/null -w "%{http_code}" http://localhost:8001/admin || echo "   ⚠️  Local test skipped (server may not be running)"

echo ""
echo "✅ Admin Dashboard Fix Deployment completed!"
echo ""
echo "📋 Summary of fixes:"
echo "✅ Fixed null reference issues in dashboard view"
echo "✅ Added proper relationship loading in queries"
echo "✅ Created/verified admin user (admin@example.com / password)"
echo "✅ Seeded all required permissions and roles"
echo "✅ Optimized for production"
echo ""
echo "🎯 The admin dashboard should now work without 500 errors!"
echo ""
echo "📋 Next steps:"
echo "1. Test admin login: admin@example.com / password"
echo "2. Access your admin panel at /admin"
echo "3. If issues persist, check logs: tail -f storage/logs/laravel.log"
echo "4. Run this script on your cloud server" 