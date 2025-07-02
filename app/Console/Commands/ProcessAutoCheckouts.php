<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\CheckinService;

class ProcessAutoCheckouts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:process-auto-checkouts {--dry-run : Show what would be processed without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process automatic check-outs for completed sessions';

    /**
     * Execute the console command.
     */
    public function handle(CheckinService $checkinService): int
    {
        $this->info('Processing automatic check-outs...');
        
        if ($this->option('dry-run')) {
            $this->warn('DRY RUN MODE - No changes will be made');
        }
        
        try {
            $processedCount = $checkinService->processAutoCheckouts();
            
            if ($this->option('dry-run')) {
                $this->info("Would process {$processedCount} auto check-outs");
            } else {
                $this->info("Successfully processed {$processedCount} auto check-outs");
            }
            
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $this->error('Error processing auto check-outs: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
