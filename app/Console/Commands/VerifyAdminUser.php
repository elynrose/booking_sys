<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class VerifyAdminUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:verify-admin {email? : The admin email to verify}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verify the admin user account';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email') ?? 'admin@admin.com';
        
        $this->info("Looking for admin user with email: {$email}");
        
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            $this->error("User with email '{$email}' not found!");
            return 1;
        }
        
        $this->info("Found user: {$user->name} ({$user->email})");
        
        // Check current verification status
        $this->info("Current verification status:");
        $this->line("- Email verified: " . ($user->email_verified_at ? 'Yes' : 'No'));
        $this->line("- Account verified: " . ($user->verified ? 'Yes' : 'No'));
        $this->line("- Verified at: " . ($user->verified_at ? $user->verified_at : 'Not set'));
        
        // Update verification status
        $user->update([
            'email_verified_at' => now(),
            'verified' => 1,
            'verified_at' => now(),
        ]);
        
        $this->info("✅ User '{$user->name}' has been verified successfully!");
        
        // Show updated status
        $user->refresh();
        $this->info("Updated verification status:");
        $this->line("- Email verified: " . ($user->email_verified_at ? 'Yes' : 'No'));
        $this->line("- Account verified: " . ($user->verified ? 'Yes' : 'No'));
        $this->line("- Verified at: " . ($user->verified_at ? $user->verified_at : 'Not set'));
        
        return 0;
    }
} 