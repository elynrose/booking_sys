<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Trainer;
use App\Models\Child;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Checkin;
use Carbon\Carbon;

echo "=== USER ACCOUNT VERIFICATION REPORT ===\n\n";

// Get all users
$users = User::with(['roles', 'children', 'bookings', 'payments'])->get();

echo "TOTAL USERS: " . $users->count() . "\n";
echo "=" . str_repeat("=", 50) . "\n\n";

// Summary statistics
$verifiedUsers = $users->where('email_verified_at', '!=', null)->count();
$unverifiedUsers = $users->where('email_verified_at', null)->count();
$usersWithTwoFactor = $users->where('two_factor', true)->count();
$usersWithMemberId = $users->whereNotNull('member_id')->count();

echo "SUMMARY STATISTICS:\n";
echo "- Verified Users: {$verifiedUsers}\n";
echo "- Unverified Users: {$unverifiedUsers}\n";
echo "- Users with 2FA: {$usersWithTwoFactor}\n";
echo "- Users with Member ID: {$usersWithMemberId}\n\n";

// Check for users without member IDs
$usersWithoutMemberId = $users->whereNull('member_id');
if ($usersWithoutMemberId->count() > 0) {
    echo "⚠️  USERS WITHOUT MEMBER ID: {$usersWithoutMemberId->count()}\n";
    foreach ($usersWithoutMemberId as $user) {
        echo "  - ID: {$user->id}, Name: {$user->name}, Email: {$user->email}\n";
    }
    echo "\n";
}

// Check for unverified users
$unverifiedUsersList = $users->where('email_verified_at', null);
if ($unverifiedUsersList->count() > 0) {
    echo "⚠️  UNVERIFIED USERS: {$unverifiedUsersList->count()}\n";
    foreach ($unverifiedUsersList as $user) {
        echo "  - ID: {$user->id}, Name: {$user->name}, Email: {$user->email}\n";
    }
    echo "\n";
}

// Check for users without roles
$usersWithoutRoles = $users->where('roles', '[]')->where('roles', null);
if ($usersWithoutRoles->count() > 0) {
    echo "⚠️  USERS WITHOUT ROLES: {$usersWithoutRoles->count()}\n";
    foreach ($usersWithoutRoles as $user) {
        echo "  - ID: {$user->id}, Name: {$user->name}, Email: {$user->email}\n";
    }
    echo "\n";
}

// Check for duplicate emails
$emailCounts = $users->groupBy('email')->map->count();
$duplicateEmails = $emailCounts->filter(function($count) { return $count > 1; });
if ($duplicateEmails->count() > 0) {
    echo "⚠️  DUPLICATE EMAILS:\n";
    foreach ($duplicateEmails as $email => $count) {
        echo "  - Email: {$email} (used {$count} times)\n";
    }
    echo "\n";
}

// Check for users with missing required fields
$usersWithMissingFields = $users->filter(function($user) {
    return empty($user->name) || empty($user->email);
});
if ($usersWithMissingFields->count() > 0) {
    echo "⚠️  USERS WITH MISSING REQUIRED FIELDS: {$usersWithMissingFields->count()}\n";
    foreach ($usersWithMissingFields as $user) {
        echo "  - ID: {$user->id}, Name: " . ($user->name ?: 'NULL') . ", Email: " . ($user->email ?: 'NULL') . "\n";
    }
    echo "\n";
}

// Role distribution
echo "ROLE DISTRIBUTION:\n";
$roleCounts = [];
foreach ($users as $user) {
    foreach ($user->roles as $role) {
        $roleCounts[$role->title] = ($roleCounts[$role->title] ?? 0) + 1;
    }
}
foreach ($roleCounts as $role => $count) {
    echo "- {$role}: {$count} users\n";
}
echo "\n";

// Check trainer accounts
echo "TRAINER ACCOUNTS:\n";
$trainers = Trainer::with('user')->get();
echo "Total Trainer Records: {$trainers->count()}\n";

foreach ($trainers as $trainer) {
    $user = $trainer->user;
    $hasTrainerRole = $user->roles->where('title', 'Trainer')->count() > 0;
    $status = $hasTrainerRole ? "✓" : "⚠️";
    echo "{$status} Trainer ID: {$trainer->id}, User: {$user->name} (ID: {$user->id}), Email: {$user->email}\n";
    if (!$hasTrainerRole) {
        echo "    - Missing Trainer role!\n";
    }
}
echo "\n";

// Check for users with Trainer role but no trainer record
$usersWithTrainerRole = $users->filter(function($user) {
    return $user->roles->where('title', 'Trainer')->count() > 0;
});
$trainerUsersWithoutRecord = $usersWithTrainerRole->filter(function($user) use ($trainers) {
    return !$trainers->where('user_id', $user->id)->count();
});
if ($trainerUsersWithoutRecord->count() > 0) {
    echo "⚠️  USERS WITH TRAINER ROLE BUT NO TRAINER RECORD: {$trainerUsersWithoutRecord->count()}\n";
    foreach ($trainerUsersWithoutRecord as $user) {
        echo "  - ID: {$user->id}, Name: {$user->name}, Email: {$user->email}\n";
    }
    echo "\n";
}

// Check children accounts
echo "CHILDREN ACCOUNTS:\n";
$children = Child::with('user')->get();
echo "Total Children: {$children->count()}\n";

foreach ($children as $child) {
    $user = $child->user;
    $hasMemberRole = $user->roles->where('title', 'Member')->count() > 0;
    $status = $hasMemberRole ? "✓" : "⚠️";
    echo "{$status} Child: {$child->name}, Parent: {$user->name} (ID: {$user->id})\n";
    if (!$hasMemberRole) {
        echo "    - Parent missing Member role!\n";
    }
}
echo "\n";

// Check for users with Member role but no children
$usersWithMemberRole = $users->filter(function($user) {
    return $user->roles->where('title', 'Member')->count() > 0;
});
$memberUsersWithoutChildren = $usersWithMemberRole->filter(function($user) use ($children) {
    return !$children->where('user_id', $user->id)->count();
});
if ($memberUsersWithoutChildren->count() > 0) {
    echo "⚠️  USERS WITH MEMBER ROLE BUT NO CHILDREN: {$memberUsersWithoutChildren->count()}\n";
    foreach ($memberUsersWithoutChildren as $user) {
        echo "  - ID: {$user->id}, Name: {$user->name}, Email: {$user->email}\n";
    }
    echo "\n";
}

// Check for users with Admin role
$adminUsers = $users->filter(function($user) {
    return $user->roles->where('title', 'Admin')->count() > 0;
});
echo "ADMIN USERS: {$adminUsers->count()}\n";
foreach ($adminUsers as $user) {
    $verified = $user->email_verified_at ? "✓" : "⚠️";
    echo "{$verified} ID: {$user->id}, Name: {$user->name}, Email: {$user->email}\n";
}
echo "\n";

// Check for recently created users (last 7 days)
$recentUsers = $users->filter(function($user) {
    return $user->created_at->isAfter(Carbon::now()->subDays(7));
});
echo "RECENTLY CREATED USERS (last 7 days): {$recentUsers->count()}\n";
foreach ($recentUsers as $user) {
    $verified = $user->email_verified_at ? "✓" : "⚠️";
    echo "{$verified} ID: {$user->id}, Name: {$user->name}, Email: {$user->email}, Created: {$user->created_at->format('Y-m-d H:i')}\n";
}
echo "\n";

// Check for users with activity (bookings, payments, check-ins)
echo "USER ACTIVITY SUMMARY:\n";
$usersWithBookings = $users->filter(function($user) { return $user->bookings->count() > 0; });
$usersWithPayments = $users->filter(function($user) { return $user->payments->count() > 0; });
$usersWithCheckins = $users->filter(function($user) { 
    return Checkin::whereHas('booking', function($q) use ($user) {
        $q->where('user_id', $user->id);
    })->count() > 0;
});

echo "- Users with bookings: {$usersWithBookings->count()}\n";
echo "- Users with payments: {$usersWithPayments->count()}\n";
echo "- Users with check-ins: {$usersWithCheckins->count()}\n\n";

// Check for inactive users (no activity in last 30 days)
$inactiveUsers = $users->filter(function($user) {
    $lastActivity = $user->updated_at;
    return $lastActivity->isBefore(Carbon::now()->subDays(30));
});
echo "INACTIVE USERS (no activity in 30 days): {$inactiveUsers->count()}\n";
foreach ($inactiveUsers as $user) {
    echo "- ID: {$user->id}, Name: {$user->name}, Email: {$user->email}, Last Updated: {$user->updated_at->format('Y-m-d H:i')}\n";
}
echo "\n";

echo "=== VERIFICATION COMPLETE ===\n"; 