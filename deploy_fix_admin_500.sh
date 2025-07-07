#!/bin/bash

echo "🚀 Deploying Admin Home 500 Error Fix"
echo "====================================="
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

# 3. Run the fix script
echo ""
echo "3. Running admin home fix script..."
php fix_admin_home_500.php

# 4. Create storage link if needed
echo ""
echo "4. Creating storage link..."
php artisan storage:link

# 5. Set final permissions
echo ""
echo "5. Setting final permissions..."
chmod -R 775 storage/
chmod -R 775 bootstrap/cache/
chmod -R 775 public/storage/

echo ""
echo "✅ Deployment completed!"
echo ""
echo "📋 Next steps:"
echo "1. Test admin login: admin@example.com / password"
echo "2. Access your admin panel"
echo "3. If issues persist, check logs: tail -f storage/logs/laravel.log"
echo "4. Contact support if needed" 