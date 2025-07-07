<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class Enable2FA extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = '2fa:enable {email?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Re-enable 2FA for a user (after testing)';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $email = $this->argument('email') ?? 'admin@example.com';
        
        $this->info("=== 2FA Re-enable Script ===");
        
        try {
            // Find user
            $user = User::where('email', $email)->first();
            
            if (!$user) {
                $this->error("❌ User not found: {$email}");
                return 1;
            }
            
            $this->info("✅ User found: {$user->email}");
            
            // Re-enable 2FA by generating a new code
            $user->generateTwoFactorCode();
            
            $this->info("✅ 2FA re-enabled for user");
            $this->line("📧 Email: {$user->email}");
            $this->newLine();
            
            $this->warn("⚠️  2FA is now active again!");
            $this->line("   - Next login will require 2FA code");
            $this->line("   - Check your email for the 2FA code");
            $this->newLine();
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error("❌ Error: " . $e->getMessage());
            return 1;
        }
    }
} 