<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class VerifyAllEmailsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Verifying all user emails...');

        // Get all users
        $users = User::all();
        
        if ($users->count() === 0) {
            $this->command->warn('No users found in the database.');
            return;
        }

        $verifiedCount = 0;
        $alreadyVerifiedCount = 0;

        foreach ($users as $user) {
            $updated = false;

            // Check if email is not verified
            if (!$user->email_verified_at) {
                $user->email_verified_at = now();
                $updated = true;
            }

            // Check if user is not verified
            if (!$user->verified) {
                $user->verified = 1;
                $user->verified_at = now();
                $updated = true;
            }

            if ($updated) {
                $user->save();
                $verifiedCount++;
                $this->command->info("✓ Verified email for: {$user->name} ({$user->email})");
            } else {
                $alreadyVerifiedCount++;
                $this->command->info("• Already verified: {$user->name} ({$user->email})");
            }
        }

        $this->command->info('');
        $this->command->info("Email verification completed!");
        $this->command->info("✓ Newly verified: {$verifiedCount} users");
        $this->command->info("• Already verified: {$alreadyVerifiedCount} users");
        $this->command->info("• Total users: " . $users->count());
    }
} 