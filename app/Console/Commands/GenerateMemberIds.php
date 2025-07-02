<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class GenerateMemberIds extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:generate-member-ids';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate member IDs for users who do not have one';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Generating member IDs for users...');

        $usersWithoutMemberId = User::whereNull('member_id')->orWhere('member_id', '')->get();
        
        if ($usersWithoutMemberId->isEmpty()) {
            $this->info('All users already have member IDs!');
            return 0;
        }

        $this->info("Found {$usersWithoutMemberId->count()} users without member IDs.");

        $bar = $this->output->createProgressBar($usersWithoutMemberId->count());
        $bar->start();

        foreach ($usersWithoutMemberId as $user) {
            $memberId = $user->generateMemberId();
            $this->line("\nGenerated member ID {$memberId} for user {$user->name} ({$user->email})");
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('Member ID generation completed successfully!');

        return 0;
    }
} 