<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Role;

class Disable2FA extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = '2fa:disable {email?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Disable 2FA for a user (for testing purposes)';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $email = $this->argument('email') ?? 'admin@example.com';
        
        $this->info("=== 2FA Disable Script ===");
        
        try {
            // Find user
            $user = User::where('email', $email)->first();
            
            if (!$user) {
                $this->warn("User not found. Creating admin user...");
                
                // Create admin user if it doesn't exist
                $user = User::create([
                    'name' => 'Admin User',
                    'email' => $email,
                    'password' => bcrypt('password'),
                    'email_verified_at' => now(),
                ]);
                
                // Assign admin role
                $adminRole = Role::where('title', 'Admin')->first();
                if ($adminRole) {
                    $user->roles()->attach($adminRole->id);
                    $this->info("✅ Admin role assigned");
                }
                
                $this->info("✅ Admin user created successfully!");
            } else {
                $this->info("✅ User found: {$user->email}");
            }
            
            // Disable 2FA by clearing the two_factor_code
            $user->resetTwoFactorCode();
            
            $this->info("✅ 2FA disabled for user");
            $this->line("📧 Email: {$user->email}");
            $this->line("🔑 Password: password");
            $this->newLine();
            
            $this->info("🔧 Now you can:");
            $this->line("1. Go to: http://localhost:8008/login");
            $this->line("2. Login with {$user->email} / password");
            $this->line("3. You should NOT be redirected to 2FA");
            $this->line("4. Go to: http://localhost:8008/admin/schedules/import");
            $this->line("5. Test the CSV import functionality");
            $this->newLine();
            
            $this->warn("⚠️  IMPORTANT: This is for testing only!");
            $this->line("   - 2FA is disabled for security");
            $this->line("   - Re-enable 2FA after testing");
            $this->line("   - Change the password in production");
            $this->newLine();
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error("❌ Error: " . $e->getMessage());
            $this->error("Stack trace:");
            $this->error($e->getTraceAsString());
            return 1;
        }
    }
} 