# Quick Reference Guide: Checkin Process & Schedule Types

## 🚀 Quick Checkin Process

### For Members
1. **Enter Member ID** → 2. **Select Class** → 3. **Check In** → 4. **Check Out**

### For Staff
1. **Verify Member** → 2. **Process Checkin** → 3. **Monitor Session** → 4. **Complete Checkout**

---

## 📋 Schedule Types Overview

| Type | Participants | Session Limits | Trainer Check | Waitlist |
|------|-------------|----------------|---------------|----------|
| **Group** | Multiple | Fixed | No | Yes |
| **Private** | One-on-one | Flexible | Required | No |
| **Special** | Variable | Custom | Custom | Custom |

---

## 🔑 Key Features

### Checkin System
- ✅ **Member ID Verification**
- ✅ **Real-time Availability**
- ✅ **Late Checkin Tracking**
- ✅ **Auto Checkout**
- ✅ **Session Duration Tracking**

### Schedule Management
- ✅ **Three Schedule Types** (Group, Private, Special)
- ✅ **Category Organization** (6 main categories)
- ✅ **Trainer Availability Integration**
- ✅ **Waitlist System**
- ✅ **Capacity Management**

---

## 📊 User Roles & Permissions

### Admin
- **Full System Access**
- **Manage All Checkins**
- **Create/Edit Schedules**
- **View Analytics**

### Trainer
- **View Assigned Classes**
- **Check In/Out Students**
- **Manage Availability**
- **Access Reports**

### User
- **Check In/Out Own Bookings**
- **View Schedules**
- **Book Classes**
- **Track Sessions**

---

## ⚡ Common Workflows

### Booking a Class
```
Login → Browse Schedules → Select Class → Choose Child → Pay → Confirmation
```

### Checking In
```
Enter Member ID → Verify → Select Class → Check In → Success
```

### Checking Out
```
Find Active Session → Check Out → Session Complete → Update Count
```

---

## 🚨 Important Rules

### Booking Rules
- **One booking per child per schedule**
- **Must checkout before new bookings**
- **Active checkin blocks new bookings**
- **Full classes → Waitlist**

### Checkin Rules
- **Cannot checkin for future classes**
- **Must have remaining sessions**
- **Trainer must be available (private classes)**
- **One checkin per day (group classes)**

### Session Rules
- **Sessions decrement on checkout**
- **Unlimited classes don't count sessions**
- **Late checkins are tracked**
- **Auto checkout when class ends**

---

## 🔧 Technical Quick Reference

### Database Tables
- **checkins** - Checkin records
- **schedules** - Class schedules
- **bookings** - Member bookings
- **categories** - Class categories
- **users** - Member accounts

### Key Services
- **CheckinService** - Process checkins/checkouts
- **BookingService** - Handle bookings
- **ScheduleService** - Manage schedules

### API Endpoints
- `/checkin` - Checkin interface
- `/schedules` - View schedules
- `/admin/schedules` - Manage schedules

---

## 📞 Support Quick Reference

### Common Issues
- **Member ID Not Found** → Verify ID is correct
- **Already Checked In** → Must checkout first
- **Class Full** → Join waitlist
- **No Sessions** → Purchase more sessions

### Contact Information
- **Technical Issues** → System Administrator
- **Booking Questions** → Gym Staff
- **Payment Issues** → Billing Department

---

## 📈 Best Practices

### For Members
1. **Keep Member ID handy**
2. **Arrive early to avoid late checkin**
3. **Remember to checkout**
4. **Monitor remaining sessions**
5. **Contact staff for issues**

### For Staff
1. **Monitor checkin statistics**
2. **Keep trainer availability updated**
3. **Process auto-checkouts regularly**
4. **Provide clear instructions**
5. **Support users with issues**

### For Administrators
1. **Regular system monitoring**
2. **Capacity planning**
3. **Trainer management**
4. **System maintenance**
5. **User support**

---

*This quick reference guide provides essential information for using the gym app's checkin system and schedule management. For detailed documentation, see the full documentation file.* 