# GymSaaS Production Deployment Guide

This guide ensures your GymSaaS application behaves exactly like localhost in the cloud.

## 🚀 Quick Deployment

1. **Run the deployment script:**
   ```bash
   ./deploy.sh
   ```

## 📋 Pre-Deployment Checklist

### Environment Configuration

Create a `.env` file with these production settings:

```env
APP_NAME="Greenstreet"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

# Database
DB_CONNECTION=mysql
DB_HOST=your_db_host
DB_PORT=3306
DB_DATABASE=your_db_name
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password

# Mail (configure for your provider)
MAIL_MAILER=smtp
MAIL_HOST=your_smtp_host
MAIL_PORT=587
MAIL_USERNAME=your_email
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@yourdomain.com"
MAIL_FROM_NAME="Greenstreet"

# Stripe (if using payments)
STRIPE_KEY=your_stripe_publishable_key
STRIPE_SECRET=your_stripe_secret_key
STRIPE_WEBHOOK_SECRET=your_stripe_webhook_secret

# Security
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=strict
```

### Server Requirements

- **PHP**: 8.1 or higher
- **MySQL**: 5.7 or higher
- **Node.js**: 16 or higher (for asset compilation)
- **Composer**: Latest version
- **Web Server**: Apache/Nginx

### Required PHP Extensions

```bash
php -m | grep -E "(bcmath|ctype|fileinfo|json|mbstring|openssl|pdo|tokenizer|xml|gd|zip)"
```

## 🔧 Manual Deployment Steps

If you prefer manual deployment:

### 1. Install Dependencies
```bash
composer install --optimize-autoloader --no-dev
npm install
npm run build
```

### 2. Set Permissions
```bash
chmod -R 775 storage/
chmod -R 775 bootstrap/cache/
chmod -R 775 public/storage/
```

### 3. Database Setup
```bash
php artisan migrate --force
php artisan db:seed --class=PermissionSeeder --force
php artisan db:seed --class=RoleSeeder --force
php artisan db:seed --class=AssignPermissionsToAdminSeeder --force
php artisan db:seed --class=CategorySeeder --force
php artisan db:seed --class=SiteSettingsSeeder --force
```

### 4. Create Storage Link
```bash
php artisan storage:link
```

### 5. Optimize for Production
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 6. Verify Admin User
```bash
php artisan user:verify-admin
```

## 🔐 Permission System

The application uses a comprehensive permission system:

### Roles
- **Admin**: Full access to everything
- **Trainer**: Access to bookings, schedules, payments (no user management)
- **User**: Limited access to bookings, schedules, payments, profile

### Permissions
- `booking_*`: Booking management
- `payment_*`: Payment management
- `schedule_*`: Schedule management
- `user_*`: User management (admin only)
- `site_settings_*`: Site settings (admin only)
- `trainer_*`: Trainer management
- `category_*`: Category management
- `child_*`: Child management
- `checkin_*`: Check-in/out management

## 🧪 Post-Deployment Testing

### 1. Admin Access
- Login: `admin@example.com` / `password`
- Verify site settings are accessible
- Test file uploads (logo, favicon, etc.)

### 2. User Registration
- Test new user registration
- Verify email verification works
- Test password reset functionality

### 3. Core Features
- Create and manage schedules
- Book classes
- Process payments
- Check-in/out functionality
- Auto-checkout system

### 4. File Uploads
- Test image uploads in site settings
- Verify storage links work
- Check file permissions

## 🐛 Troubleshooting

### Common Issues

**400 Error on Site Settings:**
- Ensure permissions are seeded: `php artisan db:seed --class=PermissionSeeder`
- Check admin user has proper role: `php artisan user:verify-admin`

**File Upload Issues:**
- Verify storage link: `php artisan storage:link`
- Check permissions: `chmod -R 775 storage/`
- Ensure disk space available

**Database Connection:**
- Verify database credentials in `.env`
- Check database server is running
- Ensure database exists

**Permission Issues:**
- Re-run permission seeders
- Clear cache: `php artisan cache:clear`
- Verify user roles are assigned

### Debug Mode

For troubleshooting, temporarily enable debug mode:
```env
APP_DEBUG=true
```

**Remember to disable debug mode in production!**

## 🔄 Maintenance

### Regular Tasks
- Monitor log files: `tail -f storage/logs/laravel.log`
- Clear old cache: `php artisan cache:clear`
- Update dependencies: `composer update`
- Backup database regularly

### Updates
1. Pull latest code
2. Run `composer install`
3. Run `php artisan migrate`
4. Clear caches
5. Test functionality

## 📞 Support

If you encounter issues:
1. Check the logs: `storage/logs/laravel.log`
2. Verify all requirements are met
3. Test with debug mode enabled
4. Check server error logs

---

**Your GymSaaS application is now ready for production! 🎉** 