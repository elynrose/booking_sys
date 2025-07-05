<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class GenerateMissingMemberIds extends Command
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
    protected $description = 'Generate member IDs for all users who do not have one';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Generating member IDs for users without one...');

        $usersWithoutMemberId = User::whereNull('member_id')->orWhere('member_id', '')->get();
        
        if ($usersWithoutMemberId->isEmpty()) {
            $this->info('✅ All users already have member IDs!');
            return 0;
        }

        $this->info("Found {$usersWithoutMemberId->count()} users without member IDs.");

        $bar = $this->output->createProgressBar($usersWithoutMemberId->count());
        $bar->start();

        foreach ($usersWithoutMemberId as $user) {
            $memberId = $user->generateMemberId();
            $this->line("\nGenerated member ID for {$user->name}: {$memberId}");
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('✅ Member IDs generated successfully for all users!');

        return 0;
    }
}
