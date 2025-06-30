<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Role;

class FixUserRoles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:fix-roles';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Assign User role to users who have no roles assigned';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userRole = Role::where('name', 'User')->first();
        
        if (!$userRole) {
            $this->error('User role not found!');
            return 1;
        }

        // Find users without any roles
        $usersWithoutRoles = User::whereDoesntHave('roles')->get();
        
        if ($usersWithoutRoles->count() === 0) {
            $this->info('All users already have roles assigned.');
            return 0;
        }

        $this->info("Found {$usersWithoutRoles->count()} users without roles.");

        foreach ($usersWithoutRoles as $user) {
            $user->roles()->attach($userRole);
            $this->line("Assigned User role to: {$user->name} ({$user->email})");
        }

        $this->info('✅ All users now have the User role assigned!');
        
        return 0;
    }
} 