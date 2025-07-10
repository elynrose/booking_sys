# Gym App Documentation: Checkin Process & Schedule Types

## Table of Contents
1. [Checkin Process Overview](#checkin-process-overview)
2. [Checkin Workflow](#checkin-workflow)
3. [Schedule Types](#schedule-types)
4. [Categories](#categories)
5. [Booking Conditions](#booking-conditions)
6. [User Roles & Permissions](#user-roles--permissions)
7. [Technical Implementation](#technical-implementation)

---

## Checkin Process Overview

The gym app features a comprehensive checkin system that allows members to check in and out of classes, with support for different types of schedules and automated processes.

### Key Features
- **Member ID Verification**: Secure checkin using unique member IDs
- **Real-time Availability**: Check trainer availability before checkin
- **Late Checkin Tracking**: Monitor and report late arrivals
- **Auto Checkout**: Automatic checkout when classes end
- **Session Tracking**: Track session duration and attendance
- **Multi-role Support**: Different workflows for users, trainers, and admins

---

## Checkin Workflow

### 1. Member Verification
```
Member enters Member ID → System validates → Shows available bookings
```

**Process:**
1. Member enters their unique Member ID
2. System validates the ID exists and belongs to a User role
3. System displays all available bookings for that member
4. Member can see active checkins, upcoming classes, and session status

### 2. Checkin Process
```
Select Booking → Verify Availability → Checkin → Confirmation
```

**Steps:**
1. **Booking Selection**: Member selects the class they want to check into
2. **Availability Check**: System verifies:
   - Class has started (no future checkins)
   - Trainer is available (for unlimited classes)
   - Member has remaining sessions (for limited classes)
   - No active checkin for today (for non-unlimited classes)
3. **Checkin Creation**: System creates checkin record with:
   - Checkin timestamp
   - Late checkin flag (if applicable)
   - Session tracking
4. **Confirmation**: Member receives success message

### 3. Checkout Process
```
Active Checkin → Checkout → Session Complete
```

**Steps:**
1. **Find Active Checkin**: System locates today's active checkin
2. **Update Record**: Set checkout timestamp
3. **Decrement Sessions**: Reduce remaining sessions (for limited classes)
4. **Calculate Duration**: Track total session time
5. **Complete Session**: Mark booking as completed

### 4. Auto Checkout
```
Class Ends → Auto Checkout → Notification
```

**Process:**
- System automatically checks out members when class end time is reached
- Sends notifications to members and admins
- Updates session records and statistics

---

## Schedule Types

The gym app supports three main schedule types, each with different booking and checkin behaviors:

### 1. Group Classes (`group`)
**Characteristics:**
- Multiple participants (up to max_participants)
- Fixed session limits
- Standard checkin/checkout process
- Waitlist support when full

**Booking Rules:**
- One booking per child per schedule
- Must check out before new bookings
- Sessions tracked individually
- Can book again after using all sessions

**Checkin Behavior:**
- One checkin per day per booking
- Must check out before next checkin
- Session count decrements on checkout
- No trainer availability check required

### 2. Private/Individual Training (`private`)
**Characteristics:**
- One-on-one sessions
- Flexible scheduling
- Trainer availability required
- Higher pricing structure

**Booking Rules:**
- Individual booking slots
- Trainer availability verification
- Flexible session limits
- Direct trainer assignment

**Checkin Behavior:**
- Trainer availability check required
- Individual session tracking
- Flexible timing options
- Direct trainer communication

### 3. Special Classes (`special`)
**Characteristics:**
- Unique or limited-time offerings
- Special pricing or conditions
- Custom checkin rules
- Event-based scheduling

**Booking Rules:**
- Special registration requirements
- Limited availability
- Custom pricing structures
- Event-specific conditions

**Checkin Behavior:**
- Custom checkin workflows
- Special tracking requirements
- Event-specific notifications
- Unique session management

---

## Categories

The app organizes schedules into categories for better organization and filtering:

### Available Categories
1. **Gymnastics** - Classical gymnastics training for all skill levels
2. **Swimming** - Swimming lessons and water safety training
3. **Martial Arts** - Karate, Taekwondo, and self-defense training
4. **Dance** - Ballet, Jazz, Hip-hop, and contemporary dance
5. **Soccer** - Youth soccer training and team development
6. **Basketball** - Basketball skills, drills, and team play

### Category Features
- **Active/Inactive Status**: Control category visibility
- **Slug-based URLs**: SEO-friendly category pages
- **Schedule Filtering**: Filter schedules by category
- **Description Support**: Detailed category information
- **Icon Support**: Visual category representation

---

## Booking Conditions

### Same Child, Same Schedule Check
- **Active Checkin**: Cannot book if child has active checkin (not checked out)
- **Remaining Sessions**: Cannot book if child still has remaining sessions
- **Used Sessions**: Can book again once all sessions are used

### Different Child Check
- **Multiple Bookings**: Parent can book same schedule for different children
- **Separate Tracking**: Each child's sessions tracked independently
- **Individual Limits**: Each child has their own session limits

### Active Checkin Check
- **Checkout Required**: Must check out before making new bookings
- **Active Session**: Cannot have multiple active checkins
- **Session Completion**: Must complete current session first

### General Booking Conditions
- **Schedule Active**: Only active schedules can be booked
- **Capacity Check**: Class must not be full
- **User Authentication**: Parent must be logged in
- **Child Registration**: Child must be registered in system
- **Payment Method**: Payment method must be specified

### Waitlist System
- **Automatic Addition**: Users added to waitlist when class is full
- **Hourly Processing**: System checks for available spots hourly
- **Notification System**: Notifies next person when spot becomes available
- **24-Hour Window**: Users have 24 hours to book after notification
- **Order Processing**: Waitlist processed in join order

---

## User Roles & Permissions

### Admin Role
**Checkin Permissions:**
- View all checkins across the system
- Manage checkin records
- Override checkin restrictions
- Access checkin statistics and reports
- Process manual checkouts
- Manage auto-checkout settings

**Schedule Management:**
- Create and manage all schedule types
- Assign trainers to schedules
- Set pricing and capacity
- Manage categories and schedule types
- View booking analytics

### Trainer Role
**Checkin Permissions:**
- View checkins for their assigned classes
- Check in/out students for their classes
- Access class attendance reports
- Manage their availability settings
- Receive checkin notifications

**Schedule Management:**
- View their assigned schedules
- Update their availability
- Set unavailability periods
- Access student information for their classes

### User Role
**Checkin Permissions:**
- Check in/out for their own bookings
- View their checkin history
- Access their session statistics
- Receive checkin notifications

**Schedule Access:**
- View available schedules
- Book classes for their children
- Manage their bookings
- Access class information

---

## Technical Implementation

### Database Structure

#### Checkins Table
```sql
checkins
├── id (Primary Key)
├── booking_id (Foreign Key)
├── user_id (Foreign Key)
├── checkin_time (Timestamp)
├── checkout_time (Timestamp, Nullable)
├── is_late_checkin (Boolean)
├── late_minutes (Integer)
├── created_at (Timestamp)
└── updated_at (Timestamp)
```

#### Schedules Table
```sql
schedules
├── id (Primary Key)
├── category_id (Foreign Key)
├── title (String)
├── slug (String)
├── type (Enum: group, private, special)
├── description (Text)
├── trainer_id (Foreign Key)
├── start_date (Date)
├── end_date (Date)
├── start_time (Time)
├── end_time (Time)
├── max_participants (Integer)
├── price (Decimal)
├── status (Enum: active, inactive)
├── allow_unlimited_bookings (Boolean)
└── created_at/updated_at (Timestamps)
```

### Key Services

#### CheckinService
- **checkin()**: Process member checkin
- **checkout()**: Process member checkout
- **autoCheckout()**: Automatic checkout when class ends
- **isCheckedIn()**: Check if member is currently checked in
- **getActiveCheckin()**: Get current active checkin
- **getCheckinStats()**: Get checkin statistics
- **getSessionDuration()**: Calculate session duration
- **processAutoCheckouts()**: Process all auto checkouts

#### Schedule Management
- **Trainer Availability**: Check trainer availability for private classes
- **Capacity Management**: Track class capacity and waitlist
- **Session Tracking**: Monitor session usage and limits
- **Notification System**: Send checkin/checkout notifications

### API Endpoints

#### Checkin Routes
```
GET    /checkin                    # Checkin index page
POST   /checkin/verify            # Verify member ID
POST   /checkin/checkin           # Process checkin
POST   /checkin/checkout          # Process checkout
POST   /checkin/quick-checkout    # Quick checkout for logged users
POST   /checkin/auto-checkout     # Auto checkout (admin only)
GET    /checkin/auto-checkout-success # Auto checkout success page
```

#### Schedule Routes
```
GET    /schedules                 # View all schedules
GET    /schedules/{schedule}      # View specific schedule
POST   /schedules/{schedule}/book # Book a schedule
GET    /admin/schedules           # Admin schedule management
POST   /admin/schedules           # Create new schedule
PUT    /admin/schedules/{id}      # Update schedule
DELETE /admin/schedules/{id}      # Delete schedule
```

### Security Features

#### Authentication & Authorization
- **Member ID Verification**: Secure member identification
- **Role-based Access**: Different permissions per user role
- **Session Management**: Secure session handling
- **CSRF Protection**: Cross-site request forgery protection

#### Data Validation
- **Input Validation**: Validate all checkin inputs
- **Business Logic**: Enforce booking and checkin rules
- **Error Handling**: Comprehensive error handling
- **Logging**: Detailed activity logging

### Notifications

#### Checkin Notifications
- **Checkin Confirmation**: Notify member of successful checkin
- **Checkout Confirmation**: Notify member of successful checkout
- **Late Checkin Alert**: Notify admin of late arrivals
- **Auto Checkout Notification**: Notify member of automatic checkout

#### Schedule Notifications
- **Booking Confirmation**: Confirm successful booking
- **Waitlist Notification**: Notify when spot becomes available
- **Schedule Changes**: Notify of schedule modifications
- **Reminder Notifications**: Send class reminders

---

## Best Practices

### For Administrators
1. **Regular Monitoring**: Check checkin statistics regularly
2. **Capacity Planning**: Monitor class capacity and waitlist
3. **Trainer Management**: Ensure trainer availability is up to date
4. **System Maintenance**: Run auto-checkout processes regularly
5. **User Support**: Provide clear instructions for checkin process

### For Trainers
1. **Availability Updates**: Keep availability calendar current
2. **Class Preparation**: Review checkin list before class
3. **Student Communication**: Communicate with students about checkin
4. **Attendance Tracking**: Monitor class attendance
5. **Feedback**: Provide feedback on checkin process

### For Users
1. **Member ID**: Keep member ID handy for checkin
2. **Timely Checkin**: Arrive early to avoid late checkin
3. **Checkout**: Remember to checkout after class
4. **Booking Management**: Monitor remaining sessions
5. **Communication**: Contact admin for any issues

---

## Troubleshooting

### Common Issues

#### Checkin Problems
- **Member ID Not Found**: Verify member ID is correct
- **Already Checked In**: Must checkout before new checkin
- **Class Not Started**: Cannot checkin for future classes
- **No Sessions Remaining**: Purchase more sessions
- **Trainer Unavailable**: Check trainer availability

#### Booking Problems
- **Class Full**: Join waitlist for full classes
- **Active Checkin**: Checkout before new booking
- **Payment Required**: Complete payment before booking
- **Child Not Registered**: Register child in system

#### System Issues
- **Auto Checkout Not Working**: Check system cron jobs
- **Notifications Not Sending**: Verify email/SMS settings
- **Capacity Not Updating**: Refresh page or clear cache
- **Session Tracking Errors**: Contact administrator

### Support Contacts
- **Technical Issues**: Contact system administrator
- **Booking Questions**: Contact gym staff
- **Payment Issues**: Contact billing department
- **Emergency**: Use emergency contact information

---

*This documentation covers the comprehensive checkin process and schedule type management for the gym app. For additional support or questions, please contact the system administrator.* 