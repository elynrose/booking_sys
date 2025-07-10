# Demo Credentials for Gym App Testing

## Login Credentials

You can now login to the gym app using these demo accounts:

### 1. Admin Account
- **Email:** admin@demo.com
- **Password:** password
- **Role:** Admin (full permissions)
- **Access:** Admin dashboard, all features

### 2. Trainer Account
- **Email:** trainer@demo.com
- **Password:** password
- **Role:** Trainer
- **Access:** Trainer dashboard, availability management

### 3. Regular User Account
- **Email:** user@demo.com
- **Password:** password
- **Role:** User
- **Access:** Frontend booking, schedules

## Demo Data Created

### Categories
- **Demo Basketball Training** - Active category for testing

### Schedules
- **Demo Basketball Training** - Active schedule with trainer assignment
- **Price:** $25.00
- **Max Participants:** 10
- **Duration:** 1 hour (10:00 AM - 11:00 AM)

### Trainer Availability
- **Trainer:** Demo Trainer (trainer@demo.com)
- **Available Days:** Next 5 days (9:00 AM - 5:00 PM)
- **Unavailable:** Day 3 from 12:00 PM - 2:00 PM (lunch break)

## Testing Trainer Availability Functionality

### 1. Login as Trainer
1. Go to http://127.0.0.1:8000/login
2. Use trainer@demo.com / password
3. Access trainer dashboard

### 2. Test Availability Management
- Navigate to trainer availability section
- Set availability/unavailability periods
- Test calendar view

### 3. Login as Regular User
1. Use user@demo.com / password
2. Try to book the Demo Basketball Training
3. Test booking when trainer is available vs unavailable

### 4. Login as Admin
1. Use admin@demo.com / password
2. Access admin dashboard
3. View trainer availability reports
4. Manage schedules and bookings

## Key Features to Test

### Trainer Availability Integration
- ✅ Trainer can set availability/unavailability
- ✅ Booking system checks trainer availability
- ✅ Error messages show next available date
- ✅ Calendar displays availability status

### Booking Flow
- ✅ Users can book when trainer is available
- ✅ Users get error when trainer is unavailable
- ✅ System shows next available session

### Admin Dashboard
- ✅ View all trainer availability
- ✅ Manage schedules and bookings
- ✅ Access to all system features

## Test Scenarios

1. **Available Trainer Booking**
   - Login as user@demo.com
   - Book Demo Basketball Training for available dates
   - Should succeed

2. **Unavailable Trainer Booking**
   - Try to book on day 3 between 12-2 PM
   - Should show error with next available date

3. **Trainer Availability Management**
   - Login as trainer@demo.com
   - Set new availability/unavailability periods
   - Verify changes affect booking system

4. **Admin Overview**
   - Login as admin@demo.com
   - View trainer availability reports
   - Manage all aspects of the system

## Notes

- All demo users have proper roles and permissions
- Demo data includes realistic availability patterns
- The trainer availability system is fully integrated with the booking system
- Error handling shows helpful messages to users
- Admin has full access to manage all aspects

## Quick Access

- **App URL:** http://127.0.0.1:8000
- **Login Page:** http://127.0.0.1:8000/login
- **Admin Dashboard:** http://127.0.0.1:8000/admin
- **Frontend:** http://127.0.0.1:8000

The demo credentials are displayed on the login page for easy access! 