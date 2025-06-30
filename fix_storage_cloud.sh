#!/bin/bash

echo "🔧 Fixing storage issues on cloud server..."
echo "==========================================="

# Check if we're in the right directory
if [ ! -f "artisan" ]; then
    echo "❌ Laravel artisan file not found!"
    echo "   Please run this script from your Laravel project root directory"
    exit 1
fi

echo "✅ Laravel project detected"

# Create storage directory structure if it doesn't exist
echo "📁 Creating storage directory structure..."
mkdir -p storage/app/public/site
mkdir -p storage/app/public/trainers
mkdir -p storage/app/public/schedules
mkdir -p storage/app/public/children
echo "✅ Storage directories created"

# Remove existing symlink if it exists
echo "📁 Removing existing storage symlink..."
rm -f public/storage


# Create new storage symlink
echo "🔗 Creating new storage symlink..."
php artisan storage:link

# Check if symlink was created
if [ -L "public/storage" ]; then
    echo "✅ Storage symlink created successfully"
    echo "📍 Symlink points to: $(readlink public/storage)"
else
    echo "❌ Failed to create storage symlink"
    echo "   Trying manual symlink creation..."
    ln -sf storage/app/public public/storage
    if [ -L "public/storage" ]; then
        echo "✅ Manual symlink creation successful"
    else
        echo "❌ Manual symlink creation failed"
        exit 1
    fi
fi

# Set proper permissions
echo "🔐 Setting file permissions..."
chmod -R 775 storage/
chmod -R 775 public/storage/
chown -R www-data:www-data storage/ 2>/dev/null || echo "⚠️  Could not set ownership (might not be root)"

# Clear all caches
echo "🧹 Clearing caches..."
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Test storage access
echo "🧪 Testing storage access..."
if [ -d "storage/app/public" ]; then
    echo "✅ Storage directory exists"
    echo "📁 Contents:"
    ls -la storage/app/public/
else
    echo "❌ Storage directory not found"
fi

# Test symlink
echo "🔗 Testing symlink..."
if [ -L "public/storage" ] && [ -d "public/storage" ]; then
    echo "✅ Symlink is working"
    echo "📁 Symlink contents:"
    ls -la public/storage/
else
    echo "❌ Symlink is not working"
fi

echo ""
echo "🎉 Storage fix completed!"
echo ""
echo "📋 Verification steps:"
echo "1. Check if images are now displaying in admin panel"
echo "2. Test direct access: https://yourdomain.com/storage/site/[filename]"
echo "3. If still not working, check web server configuration"
echo ""
echo "🔍 Debug commands:"
echo "   ls -la public/storage"
echo "   ls -la storage/app/public"
echo "   php test_storage.php" 