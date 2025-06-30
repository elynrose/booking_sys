<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Role;
use App\Models\Permission;

class ListRolesPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'roles:list-permissions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List all roles and their permissions';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== ROLES AND PERMISSIONS REPORT ===');
        $this->line('');

        // Get all roles with their permissions
        $roles = Role::with('permissions')->get();

        if ($roles->isEmpty()) {
            $this->error('No roles found in the database!');
            return;
        }

        $this->info("Found {$roles->count()} role(s):");
        $this->line('');

        foreach ($roles as $role) {
            $this->info("Role: {$role->title} (ID: {$role->id})");
            $this->line("  Guard: {$role->guard_name}");
            
            if ($role->permissions->isEmpty()) {
                $this->warn("  ⚠️  No permissions assigned to this role!");
            } else {
                $this->line("  Permissions ({$role->permissions->count()}):");
                foreach ($role->permissions as $permission) {
                    $this->line("    ✓ {$permission->title}");
                }
            }
            $this->line('');
        }

        // Also list all available permissions
        $this->info('=== ALL AVAILABLE PERMISSIONS ===');
        $permissions = Permission::all();
        
        if ($permissions->isEmpty()) {
            $this->error('No permissions found in the database!');
            return;
        }

        $this->info("Found {$permissions->count()} permission(s):");
        $this->line('');

        foreach ($permissions as $permission) {
            $this->line("  • {$permission->title} (ID: {$permission->id})");
        }

        $this->line('');
        $this->info('=== SUMMARY ===');
        $this->line("Total Roles: {$roles->count()}");
        $this->line("Total Permissions: {$permissions->count()}");
        
        // Check for roles without permissions
        $rolesWithoutPermissions = $roles->filter(function($role) {
            return $role->permissions->isEmpty();
        });
        
        if ($rolesWithoutPermissions->isNotEmpty()) {
            $this->warn("Roles without permissions: " . $rolesWithoutPermissions->pluck('name')->implode(', '));
        }
    }
} 