# Spatie Migration Testing Checklist

## Pre-Testing Setup
- [ ] Clear all caches: `php artisan config:clear && php artisan cache:clear && php artisan route:clear && php artisan view:clear`
- [ ] Run permission cache reset: `php artisan permission:cache-reset`

## Database Verification
- [ ] Verify `name` columns exist in `roles` and `permissions` tables
- [ ] Verify `name` values match `title` values (Admin, User, Trainer, etc.)
- [ ] Verify all permissions exist with correct `name` values

## Role/Permission Testing
- [ ] Run: `php artisan roles:list-permissions` - should show all roles and permissions
- [ ] Run: `php artisan test:checkin-access` - should work without errors
- [ ] Verify Admin role has all permissions
- [ ] Verify User role has appropriate permissions (booking, schedule, payment, etc.)
- [ ] Verify Trainer role has appropriate permissions

## User Testing (Test with different user types)

### Admin User Testing
- [ ] Login as admin user
- [ ] Access admin dashboard (`/admin`)
- [ ] Access users management (`/admin/users`)
- [ ] Access roles management (`/admin/roles`)
- [ ] Access permissions management (`/admin/permissions`)
- [ ] Access trainers management (`/admin/trainers`)
- [ ] Access bookings management (`/admin/bookings`)
- [ ] Access payments management (`/admin/payments`)
- [ ] Access categories management (`/admin/categories`)
- [ ] Access site settings (`/admin/site-settings`)

### User (Member) Testing
- [ ] Login as regular user
- [ ] Access user dashboard (`/home`)
- [ ] Access bookings (`/bookings`)
- [ ] Access payments (`/payments`)
- [ ] Access schedules (`/schedules`)
- [ ] Access profile (`/frontend/profile`)
- [ ] Access children management (`/children`)
- [ ] Verify CANNOT access admin areas

### Trainer Testing
- [ ] Login as trainer user
- [ ] Access trainer dashboard (`/trainer`)
- [ ] Access class details (`/trainer/class/{schedule}`)
- [ ] Verify CANNOT access admin areas
- [ ] Verify CANNOT access user-specific areas

## Public Routes Testing
- [ ] Access main page (`/`) - should work for guests
- [ ] Access login page (`/login`) - should work for guests
- [ ] Access register page (`/register`) - should work for guests
- [ ] Access checkin page (`/checkin`) - should work for guests (no 403 errors)

## Navigation Testing
- [ ] Admin navigation shows correct menu items based on permissions
- [ ] User navigation shows correct menu items based on permissions
- [ ] Trainer navigation shows correct menu items based on permissions
- [ ] No broken links or 403 errors in navigation

## Permission-Specific Testing
- [ ] Test `@can('dashboard_access')` in admin layout
- [ ] Test `@can('user_access')` in admin layout
- [ ] Test `@can('booking_access')` in admin layout
- [ ] Test `@can('payment_access')` in admin layout
- [ ] Test `@can('trainer_access')` in admin layout
- [ ] Test `@can('schedule_access')` in admin layout
- [ ] Test `@can('category_access')` in admin layout
- [ ] Test `@can('site_settings_access')` in admin layout

## Role-Specific Testing
- [ ] Test `@role('Admin')` in layouts
- [ ] Test `@role('User')` in layouts
- [ ] Test `@role('Trainer')` in layouts
- [ ] Test `hasRole('Admin')` in controllers
- [ ] Test `hasRole('User')` in controllers
- [ ] Test `hasRole('Trainer')` in controllers

## Error Testing
- [ ] Try to access admin areas as non-admin user - should get 403
- [ ] Try to access user areas as admin - should work
- [ ] Try to access trainer areas as regular user - should get 403
- [ ] No 500 errors on any page

## Database Consistency
- [ ] All users have correct roles assigned
- [ ] All roles have correct permissions assigned
- [ ] No orphaned role-permission relationships

## Performance Testing
- [ ] Page load times are reasonable
- [ ] No N+1 queries for role/permission checks
- [ ] Permission cache is working

## Rollback Plan (if needed)
- [ ] Database backup exists
- [ ] Git commit with working state exists
- [ ] Know how to rollback migration if needed

## Post-Deployment Testing (Cloud)
- [ ] All above tests pass on cloud environment
- [ ] No environment-specific issues
- [ ] All user flows work as expected
- [ ] No 403/500 errors on any pages

---

## Quick Commands for Testing

```bash
# Clear caches
php artisan config:clear && php artisan cache:clear && php artisan route:clear && php artisan view:clear && php artisan permission:cache-reset

# Test roles and permissions
php artisan roles:list-permissions
php artisan test:checkin-access

# Check specific roles
php artisan tinker --execute="echo 'Admin users: ' . App\Models\User::role('Admin')->count() . PHP_EOL;"
php artisan tinker --execute="echo 'User users: ' . App\Models\User::role('User')->count() . PHP_EOL;"
php artisan tinker --execute="echo 'Trainer users: ' . App\Models\User::role('Trainer')->count() . PHP_EOL;"
``` 