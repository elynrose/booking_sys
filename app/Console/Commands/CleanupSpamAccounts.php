<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CleanupSpamAccounts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'spam:cleanup {--dry-run : Show what would be deleted without actually deleting}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Identify and clean up spam accounts';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        
        $this->info('Starting spam account cleanup...');
        if ($isDryRun) {
            $this->warn('DRY RUN MODE - No accounts will be deleted');
        }

        $totalSpamAccounts = 0;
        $deletedAccounts = 0;

        // 1. Find accounts with suspicious email patterns
        $suspiciousEmails = $this->findSuspiciousEmailAccounts();
        $totalSpamAccounts += count($suspiciousEmails);
        
        if (!empty($suspiciousEmails)) {
            $this->info("Found " . count($suspiciousEmails) . " accounts with suspicious email patterns:");
            foreach ($suspiciousEmails as $user) {
                $this->line("- {$user->email} (ID: {$user->id})");
            }
            
            if (!$isDryRun) {
                User::whereIn('id', collect($suspiciousEmails)->pluck('id'))->delete();
                $deletedAccounts += count($suspiciousEmails);
                $this->info("Deleted " . count($suspiciousEmails) . " accounts with suspicious emails");
            }
        }

        // 2. Find accounts with suspicious names
        $suspiciousNames = $this->findSuspiciousNameAccounts();
        $totalSpamAccounts += count($suspiciousNames);
        
        if (!empty($suspiciousNames)) {
            $this->info("Found " . count($suspiciousNames) . " accounts with suspicious names:");
            foreach ($suspiciousNames as $user) {
                $this->line("- {$user->name} ({$user->email})");
            }
            
            if (!$isDryRun) {
                User::whereIn('id', collect($suspiciousNames)->pluck('id'))->delete();
                $deletedAccounts += count($suspiciousNames);
                $this->info("Deleted " . count($suspiciousNames) . " accounts with suspicious names");
            }
        }

        // 3. Find accounts created in rapid succession
        $rapidAccounts = $this->findRapidCreationAccounts();
        $totalSpamAccounts += count($rapidAccounts);
        
        if (!empty($rapidAccounts)) {
            $this->info("Found " . count($rapidAccounts) . " accounts created in rapid succession:");
            foreach ($rapidAccounts as $user) {
                $this->line("- {$user->email} (created: {$user->created_at})");
            }
            
            if (!$isDryRun) {
                User::whereIn('id', collect($rapidAccounts)->pluck('id'))->delete();
                $deletedAccounts += count($rapidAccounts);
                $this->info("Deleted " . count($rapidAccounts) . " rapidly created accounts");
            }
        }

        // 4. Find accounts with no activity
        $inactiveAccounts = $this->findInactiveAccounts();
        $totalSpamAccounts += count($inactiveAccounts);
        
        if (!empty($inactiveAccounts)) {
            $this->info("Found " . count($inactiveAccounts) . " inactive accounts:");
            foreach ($inactiveAccounts as $user) {
                $this->line("- {$user->email} (last login: " . ($user->last_login_at ?? 'Never') . ")");
            }
            
            if (!$isDryRun) {
                User::whereIn('id', collect($inactiveAccounts)->pluck('id'))->delete();
                $deletedAccounts += count($inactiveAccounts);
                $this->info("Deleted " . count($inactiveAccounts) . " inactive accounts");
            }
        }

        $this->info("Cleanup completed!");
        $this->info("Total spam accounts found: {$totalSpamAccounts}");
        
        if (!$isDryRun) {
            $this->info("Total accounts deleted: {$deletedAccounts}");
        }
    }

    /**
     * Find accounts with suspicious email patterns
     */
    private function findSuspiciousEmailAccounts()
    {
        return User::where(function($query) {
            $query->where('email', 'regexp', '[0-9]{6,}') // 6+ consecutive numbers
                  ->orWhere('email', 'like', '%.%.%.%') // Too many dots
                  ->orWhere('email', 'like', '%@%@%') // Multiple @ symbols
                  ->orWhere('email', 'like', '%10minutemail%')
                  ->orWhere('email', 'like', '%guerrillamail%')
                  ->orWhere('email', 'like', '%mailinator%')
                  ->orWhere('email', 'like', '%tempmail%')
                  ->orWhere('email', 'like', '%throwaway%')
                  ->orWhere('email', 'like', '%yopmail%')
                  ->orWhere('email', 'like', '%sharklasers%')
                  ->orWhere('email', 'like', '%getairmail%');
        })->get();
    }

    /**
     * Find accounts with suspicious names
     */
    private function findSuspiciousNameAccounts()
    {
        return User::where(function($query) {
            $query->where('name', 'regexp', '[0-9]{3,}') // 3+ consecutive numbers
                  ->orWhere('name', 'regexp', '(.)\\1{4,}') // 5+ repeated characters
                  ->orWhere('name', 'like', '%test%')
                  ->orWhere('name', 'like', '%spam%')
                  ->orWhere('name', 'like', '%bot%')
                  ->orWhere('name', 'like', '%admin%')
                  ->orWhere('name', 'like', '%user%')
                  ->orWhere('name', 'like', '%demo%');
        })->get();
    }

    /**
     * Find accounts created in rapid succession (same IP, same day)
     */
    private function findRapidCreationAccounts()
    {
        // This would require tracking IP addresses during registration
        // For now, we'll look for accounts created within 1 minute of each other
        return User::where('created_at', '>=', Carbon::now()->subDays(7))
                  ->whereRaw('created_at IN (
                      SELECT created_at FROM users 
                      WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                      GROUP BY DATE(created_at), HOUR(created_at)
                      HAVING COUNT(*) > 5
                  )')
                  ->get();
    }

    /**
     * Find inactive accounts (no login for 30+ days)
     */
    private function findInactiveAccounts()
    {
        return User::where(function($query) {
            $query->whereNull('last_login_at')
                  ->orWhere('last_login_at', '<', Carbon::now()->subDays(30));
        })
        ->where('created_at', '<', Carbon::now()->subDays(30))
        ->whereDoesntHave('bookings') // No bookings
        ->whereDoesntHave('children') // No children registered
        ->get();
    }
}
