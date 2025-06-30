#!/bin/bash

echo "🔧 Fixing storage issues on cloud server..."

# Navigate to Laravel project directory (adjust path as needed)
# cd /var/www/html/gymapp

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
    exit 1
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
    ls -la storage/app/public/
else
    echo "❌ Storage directory not found"
fi

echo "🎉 Storage fix completed!"
echo ""
echo "📋 Next steps:"
echo "1. Check if images are now displaying"
echo "2. If still not working, check web server configuration"
echo "3. Verify the symlink: ls -la public/storage" 