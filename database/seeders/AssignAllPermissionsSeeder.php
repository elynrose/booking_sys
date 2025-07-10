<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\Role;

class AssignAllPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Assigning all permissions to roles...');

        // Get all roles
        $adminRole = Role::where('title', 'Admin')->first();
        $userRole = Role::where('title', 'User')->first();
        $trainerRole = Role::where('title', 'Trainer')->first();

        if (!$adminRole || !$userRole || !$trainerRole) {
            $this->command->error('Required roles not found! Please run RoleSeeder first.');
            return;
        }

        // Get all permissions
        $allPermissions = Permission::all();

        // Define permission assignments for each role
        $rolePermissions = [
            'Admin' => [
                // Admin gets ALL permissions
                'all' => true
            ],
            'User' => [
                // User permissions - basic access to frontend features
                'home_access', 'home_show',
                'schedule_access', 'schedule_show',
                'booking_access', 'booking_create', 'booking_show', 'booking_edit',
                'payment_access', 'payment_create', 'payment_show',
                'child_access', 'child_create', 'child_show', 'child_edit',
                'profile_access', 'profile_edit', 'profile_show',
                'user_alert_access', 'user_alert_show',
                'checkin_access', 'checkin_create', 'checkin_show'
            ],
            'Trainer' => [
                // Trainer permissions - can manage their own schedules and see bookings
                'home_access', 'home_show',
                'schedule_access', 'schedule_show', 'schedule_create', 'schedule_edit',
                'booking_access', 'booking_show',
                'payment_access', 'payment_show',
                'child_access', 'child_show',
                'profile_access', 'profile_edit', 'profile_show',
                'user_alert_access', 'user_alert_show',
                'checkin_access', 'checkin_create', 'checkin_show',
                'trainer_access', 'trainer_show', 'trainer_edit'
            ]
        ];

        // Assign permissions to each role
        foreach ($rolePermissions as $roleTitle => $permissions) {
            $role = Role::where('title', $roleTitle)->first();
            
            if (!$role) {
                $this->command->warn("Role '$roleTitle' not found, skipping...");
                continue;
            }
            
            if ($permissions === ['all' => true]) {
                // Admin gets all permissions
                $role->permissions()->sync($allPermissions->pluck('id')->toArray());
                $this->command->info("Assigned ALL permissions to $roleTitle role");
            } else {
                // Get specific permissions
                $permissionIds = Permission::whereIn('title', $permissions)->pluck('id')->toArray();
                $role->permissions()->sync($permissionIds);
                $this->command->info("Assigned " . count($permissionIds) . " permissions to $roleTitle role");
            }
        }

        $this->command->info('Permission assignment completed!');

        // Verify assignments
        $this->command->info('Verifying assignments:');
        $roles = Role::with('permissions')->get();

        foreach ($roles as $role) {
            $this->command->info("Role: {$role->title} - {$role->permissions->count()} permissions");
        }

        $this->command->info('All permissions have been assigned successfully!');
    }
} 