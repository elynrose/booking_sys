#!/bin/bash

# GymSaaS Deployment Script
# This script ensures the application behaves exactly like localhost in the cloud

echo "🚀 Starting GymSaaS deployment..."

# Set environment to production
export APP_ENV=production
export APP_DEBUG=false

# Clear all caches
echo "🧹 Clearing caches..."
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Optimize for production
echo "⚡ Optimizing for production..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run database migrations
echo "🗄️ Running database migrations..."
php artisan migrate --force

# Seed the database with permissions and roles
echo "🌱 Seeding database..."
php artisan db:seed --class=PermissionSeeder --force
php artisan db:seed --class=RoleSeeder --force
php artisan db:seed --class=AssignPermissionsToAdminSeeder --force
php artisan db:seed --class=CategorySeeder --force
php artisan db:seed --class=SiteSettingsSeeder --force

# Verify admin user exists and is verified
echo "👤 Verifying admin user..."
php artisan user:verify-admin

# Set proper permissions for storage and cache directories
echo "🔐 Setting file permissions..."
chmod -R 775 storage/
chmod -R 775 bootstrap/cache/
chmod -R 775 public/storage/

# Create storage link if it doesn't exist
echo "🔗 Creating storage link..."
php artisan storage:link

# Optimize Composer autoloader
echo "📦 Optimizing Composer autoloader..."
composer install --optimize-autoloader --no-dev

# Build frontend assets
echo "🎨 Building frontend assets..."
npm install
npm run build

# Set proper ownership (adjust user/group as needed for your server)
echo "👥 Setting file ownership..."
# chown -R www-data:www-data storage/
# chown -R www-data:www-data bootstrap/cache/

# Verify critical services
echo "🔍 Verifying critical services..."
php artisan about

echo "✅ Deployment completed successfully!"
echo "🌐 Your GymSaaS application is ready for production!"
echo ""
echo "📋 Post-deployment checklist:"
echo "   - Verify admin user can log in (admin@example.com / password)"
echo "   - Check site settings are accessible"
echo "   - Test file uploads work properly"
echo "   - Verify all permissions are working"
echo "   - Test booking and payment flows"
echo ""
echo "🔧 If you need to debug, temporarily set APP_DEBUG=true in .env" 