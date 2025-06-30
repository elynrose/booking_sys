<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Support\Facades\Gate;

class TestCheckinAccess extends Command
{
    protected $signature = 'test:checkin-access';
    protected $description = 'Test checkin access permissions and routes';

    public function handle()
    {
        $this->info('=== TESTING CHECKIN ACCESS ===');
        $this->line('');

        // 1. Check if checkin_access permission exists
        $this->info('1. Checking checkin_access permission...');
        $checkinAccess = Permission::where('title', 'checkin_access')->first();
        
        if ($checkinAccess) {
            $this->info("✅ checkin_access permission exists (ID: {$checkinAccess->id})");
        } else {
            $this->error("❌ checkin_access permission does NOT exist!");
        }
        $this->line('');

        // 2. Check which roles have checkin_access
        $this->info('2. Checking role assignments...');
        if ($checkinAccess) {
            $rolesWithPermission = Role::whereHas('permissions', function($query) use ($checkinAccess) {
                $query->where('permission_id', $checkinAccess->id);
            })->get();
            
            if ($rolesWithPermission->isEmpty()) {
                $this->warn("⚠️  No roles have checkin_access permission!");
            } else {
                $this->info("Roles with checkin_access:");
                foreach ($rolesWithPermission as $role) {
                    $this->line("  - {$role->title}");
                }
            }
        }
        $this->line('');

        // 3. Check all checkin-related permissions
        $this->info('3. All checkin-related permissions:');
        $checkinPermissions = Permission::where('name', 'like', 'checkin%')->get();
        
        if ($checkinPermissions->isEmpty()) {
            $this->warn("⚠️  No checkin permissions found!");
        } else {
            foreach ($checkinPermissions as $permission) {
                $this->line("  - {$permission->title}");
            }
        }
        $this->line('');

        // 4. Test Gate::denies for checkin_access
        $this->info('4. Testing Gate::denies for checkin_access...');
        try {
            $denied = Gate::denies('checkin_access');
            $this->info("Gate::denies('checkin_access') returns: " . ($denied ? 'true (DENIED)' : 'false (ALLOWED)'));
        } catch (\Exception $e) {
            $this->error("Gate::denies('checkin_access') threw exception: " . $e->getMessage());
        }
        $this->line('');

        // 5. Check if there are any users
        $this->info('5. Checking users...');
        $userCount = \App\Models\User::count();
        $this->info("Total users: {$userCount}");
        
        if ($userCount > 0) {
            $adminUsers = \App\Models\User::role('Admin')->count();
            $userUsers = \App\Models\User::role('User')->count();
            $trainerUsers = \App\Models\User::role('Trainer')->count();
            
            $this->info("Users with Admin role: {$adminUsers}");
            $this->info("Users with User role: {$userUsers}");
            $this->info("Users with Trainer role: {$trainerUsers}");
        }
        $this->line('');

        $this->info('=== TEST COMPLETED ===');
    }
} 