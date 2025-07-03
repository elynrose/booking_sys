<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Trainer;
use App\Models\Child;
use App\Models\Role;
use Illuminate\Support\Facades\DB;

echo "=== FIXING USER ACCOUNT ISSUES ===\n\n";

// Get roles
$adminRole = Role::where('title', 'Admin')->first();
$trainerRole = Role::where('title', 'Trainer')->first();
$memberRole = Role::where('title', 'Member')->first();
$userRole = Role::where('title', 'User')->first();

if (!$adminRole || !$trainerRole || !$memberRole || !$userRole) {
    echo "❌ Error: Required roles not found!\n";
    exit(1);
}

echo "✓ All required roles found\n\n";

// Fix 1: Add Trainer role to users who have trainer records but missing the role
echo "1. FIXING TRAINER ROLE ASSIGNMENTS:\n";
$trainers = Trainer::with('user')->get();
$fixedTrainers = 0;

foreach ($trainers as $trainer) {
    $user = $trainer->user;
    $hasTrainerRole = $user->roles->where('title', 'Trainer')->count() > 0;
    
    if (!$hasTrainerRole) {
        $user->roles()->attach($trainerRole->id);
        echo "✓ Added Trainer role to: {$user->name} (ID: {$user->id})\n";
        $fixedTrainers++;
    }
}

echo "Fixed {$fixedTrainers} trainer role assignments\n\n";

// Fix 2: Add Member role to users who have children but missing the role
echo "2. FIXING MEMBER ROLE ASSIGNMENTS:\n";
$children = Child::with('user')->get();
$fixedMembers = 0;

foreach ($children as $child) {
    $user = $child->user;
    $hasMemberRole = $user->roles->where('title', 'Member')->count() > 0;
    
    if (!$hasMemberRole) {
        $user->roles()->attach($memberRole->id);
        echo "✓ Added Member role to: {$user->name} (ID: {$user->id}) for child: {$child->name}\n";
        $fixedMembers++;
    }
}

echo "Fixed {$fixedMembers} member role assignments\n\n";

// Fix 3: Add Trainer record for user with Trainer role but no trainer record
echo "3. FIXING MISSING TRAINER RECORDS:\n";
$usersWithTrainerRole = User::whereHas('roles', function($q) {
    $q->where('title', 'Trainer');
})->get();

$fixedTrainerRecords = 0;
foreach ($usersWithTrainerRole as $user) {
    $hasTrainerRecord = Trainer::where('user_id', $user->id)->exists();
    
    if (!$hasTrainerRecord) {
        Trainer::create([
            'user_id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'specialization' => 'General Training',
            'experience_years' => 1,
            'bio' => 'Trainer bio',
            'hourly_rate' => 50.00,
            'is_available' => true,
        ]);
        echo "✓ Created trainer record for: {$user->name} (ID: {$user->id})\n";
        $fixedTrainerRecords++;
    }
}

echo "Fixed {$fixedTrainerRecords} missing trainer records\n\n";

// Fix 4: Ensure all users have at least the User role
echo "4. ENSURING ALL USERS HAVE USER ROLE:\n";
$usersWithoutUserRole = User::whereDoesntHave('roles', function($q) {
    $q->where('title', 'User');
})->get();

$fixedUserRoles = 0;
foreach ($usersWithoutUserRole as $user) {
    $user->roles()->attach($userRole->id);
    echo "✓ Added User role to: {$user->name} (ID: {$user->id})\n";
    $fixedUserRoles++;
}

echo "Fixed {$fixedUserRoles} missing user role assignments\n\n";

// Fix 5: Ensure all users have member IDs
echo "5. ENSURING ALL USERS HAVE MEMBER IDS:\n";
$usersWithoutMemberId = User::whereNull('member_id')->get();
$fixedMemberIds = 0;

foreach ($usersWithoutMemberId as $user) {
    // Generate member ID based on role
    $role = $user->roles->first();
    $prefix = 'GYM-2025';
    
    if ($role) {
        switch ($role->title) {
            case 'Admin':
                $prefix = 'GYM-2025-A';
                break;
            case 'Trainer':
                $prefix = 'GYM-2025-T';
                break;
            case 'Member':
                $prefix = 'GYM-2025-M';
                break;
            default:
                $prefix = 'GYM-2025';
        }
    }
    
    $memberId = $prefix . '-' . str_pad($user->id, 4, '0', STR_PAD_LEFT);
    $user->update(['member_id' => $memberId]);
    
    echo "✓ Added member ID {$memberId} to: {$user->name} (ID: {$user->id})\n";
    $fixedMemberIds++;
}

echo "Fixed {$fixedMemberIds} missing member IDs\n\n";

// Summary
echo "=== SUMMARY ===\n";
echo "Total fixes applied:\n";
echo "- Trainer role assignments: {$fixedTrainers}\n";
echo "- Member role assignments: {$fixedMembers}\n";
echo "- Trainer records created: {$fixedTrainerRecords}\n";
echo "- User role assignments: {$fixedUserRoles}\n";
echo "- Member IDs added: {$fixedMemberIds}\n\n";

echo "=== FIXES COMPLETE ===\n"; 