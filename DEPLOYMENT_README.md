# 🚀 GymSaaS Cloud Deployment Guide

This guide ensures your GymSaaS application behaves **exactly like localhost** in the cloud.

## 📋 What We've Added

### ✅ Permission System
- **Complete role-based access control**
- **Admin**: Full access to everything including site settings
- **Trainer**: Access to bookings, schedules, payments (no user management)
- **User**: Limited access to bookings, schedules, payments, profile

### ✅ Site Settings Fix
- **Fixed 400 error** on site settings by adding proper permission checks
- **Added `site_settings_*` permissions** for admin-only access
- **Updated controllers** with proper Gate checks

### ✅ Deployment Automation
- **`deploy.sh`**: One-command deployment script
- **Production configurations**: Apache and Nginx configs
- **Comprehensive documentation**: Step-by-step guides

## 🚀 Quick Start

### 1. Run the Deployment Script
```bash
cd gymapp
./deploy.sh
```

### 2. Configure Your Environment
Create a `.env` file with your production settings (see `PRODUCTION_SETUP.md`)

### 3. Set Up Web Server
Use either `apache.conf` or `nginx.conf` as a template

## 🔧 Manual Deployment Steps

If you prefer manual deployment:

### Step 1: Install Dependencies
```bash
composer install --optimize-autoloader --no-dev
npm install
npm run build
```

### Step 2: Set Permissions
```bash
chmod -R 775 storage/
chmod -R 775 bootstrap/cache/
chmod -R 775 public/storage/
```

### Step 3: Database Setup
```bash
php artisan migrate --force
php artisan db:seed --class=PermissionSeeder --force
php artisan db:seed --class=RoleSeeder --force
php artisan db:seed --class=AssignPermissionsToAdminSeeder --force
php artisan db:seed --class=CategorySeeder --force
php artisan db:seed --class=SiteSettingsSeeder --force
```

### Step 4: Create Storage Link
```bash
php artisan storage:link
```

### Step 5: Optimize for Production
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Step 6: Verify Admin User
```bash
php artisan user:verify-admin
```

## 🔐 Permission System Details

### Roles & Permissions
```
Admin Role:
├── All permissions (user_*, site_settings_*, booking_*, etc.)
└── Full system access

Trainer Role:
├── booking_* (access, create, edit, delete, show)
├── schedule_* (access, create, edit, delete, show)
├── payment_* (access, create, edit, delete, show)
├── trainer_* (access, create, edit, delete, show)
├── category_* (access, create, edit, delete, show)
├── child_* (access, create, edit, delete, show)
├── checkin_* (access, create, edit, delete, show)
├── home_* (access, create, edit, delete, show)
├── profile_* (access, create, edit, delete, show)
└── user_alert_* (access, create, edit, delete, show)

User Role:
├── booking_* (access, create, edit, delete, show)
├── schedule_* (access, show)
├── payment_* (access, create, show)
├── profile_* (access, edit, show)
├── child_* (access, create, edit, delete, show)
├── checkin_* (access, create, show)
├── home_* (access, show)
└── user_alert_* (access, create, edit, delete, show)
```

## 🐛 Troubleshooting

### Common Issues & Solutions

#### 1. 400 Error on Site Settings
**Problem**: Site settings page returns 400 error
**Solution**: 
```bash
php artisan db:seed --class=PermissionSeeder
php artisan user:verify-admin
php artisan cache:clear
```

#### 2. File Upload Issues
**Problem**: Can't upload images in site settings
**Solution**:
```bash
php artisan storage:link
chmod -R 775 storage/
chmod -R 775 public/storage/
```

#### 3. Permission Denied Errors
**Problem**: Users can't access certain features
**Solution**:
```bash
php artisan db:seed --class=AssignPermissionsToAdminSeeder
php artisan cache:clear
```

#### 4. Database Connection Issues
**Problem**: Can't connect to database
**Solution**:
- Verify database credentials in `.env`
- Check database server is running
- Ensure database exists

## 🧪 Testing Checklist

After deployment, test these features:

### ✅ Admin Features
- [ ] Login as admin (`admin@example.com` / `password`)
- [ ] Access site settings
- [ ] Upload logo/favicon
- [ ] Manage users
- [ ] View all bookings
- [ ] Access dashboard

### ✅ User Features
- [ ] Register new user
- [ ] Book a class
- [ ] View schedules
- [ ] Check-in/out
- [ ] Manage profile
- [ ] View payments

### ✅ Trainer Features
- [ ] Login as trainer
- [ ] View assigned classes
- [ ] Manage bookings
- [ ] Access trainer dashboard

### ✅ System Features
- [ ] File uploads work
- [ ] Email notifications
- [ ] Auto-checkout system
- [ ] Payment processing
- [ ] Permission restrictions

## 🔄 Maintenance

### Regular Tasks
```bash
# Monitor logs
tail -f storage/logs/laravel.log

# Clear old cache
php artisan cache:clear

# Update dependencies
composer update

# Backup database
mysqldump -u username -p database_name > backup.sql
```

### Updates
1. Pull latest code
2. Run `composer install`
3. Run `php artisan migrate`
4. Clear caches
5. Test functionality

## 📁 File Structure

```
gymapp/
├── deploy.sh                    # One-command deployment
├── PRODUCTION_SETUP.md         # Detailed setup guide
├── DEPLOYMENT_README.md        # This file
├── apache.conf                 # Apache configuration
├── nginx.conf                  # Nginx configuration
├── app/
│   ├── Http/Controllers/Admin/
│   │   └── SiteSettingsController.php  # Fixed with permissions
│   └── Http/Middleware/
│       └── AdminMiddleware.php          # Role checking
└── database/seeders/
    ├── PermissionSeeder.php             # Creates all permissions
    ├── RoleSeeder.php                   # Creates roles
    └── AssignPermissionsToAdminSeeder.php # Assigns permissions
```

## 🎯 Key Features Working in Production

### ✅ User Management
- Role-based access control
- Email verification
- Password reset
- Two-factor authentication

### ✅ Booking System
- Class booking
- Payment processing
- Check-in/out
- Auto-checkout

### ✅ Admin Panel
- User management
- Schedule management
- Payment tracking
- Site customization

### ✅ File Management
- Image uploads
- Storage links
- Proper permissions

### ✅ Security
- Permission gates
- CSRF protection
- Input validation
- SQL injection prevention

## 🚀 Ready for Production!

Your GymSaaS application is now fully configured for cloud deployment with:

- ✅ **Complete permission system**
- ✅ **Fixed site settings**
- ✅ **Automated deployment**
- ✅ **Production configurations**
- ✅ **Comprehensive documentation**
- ✅ **Troubleshooting guides**

**Run `./deploy.sh` and your application will behave exactly like localhost in the cloud!** 🎉 