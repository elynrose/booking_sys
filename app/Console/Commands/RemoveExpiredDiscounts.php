<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Schedule;
use Carbon\Carbon;

class RemoveExpiredDiscounts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'discounts:remove-expired {--dry-run : Show what would be updated without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove expired discounts from schedules';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = Carbon::now();
        $dryRun = $this->option('dry-run');

        // Find schedules with expired discounts
        $expiredSchedules = Schedule::where('is_discounted', true)
            ->whereNotNull('discount_expiry_date')
            ->where('discount_expiry_date', '<', $now)
            ->get();

        if ($expiredSchedules->isEmpty()) {
            $this->info('No expired discounts found.');
            return 0;
        }

        $this->info("Found {$expiredSchedules->count()} schedule(s) with expired discounts:");

        foreach ($expiredSchedules as $schedule) {
            $this->line("- Schedule #{$schedule->id}: {$schedule->title}");
            $this->line("  Original Price: \${$schedule->price}");
            $this->line("  Discount: {$schedule->discount_percentage}%");
            $this->line("  Expired: {$schedule->discount_expiry_date->format('Y-m-d H:i:s')}");
            $this->line("");
        }

        if ($dryRun) {
            $this->info('DRY RUN: No changes made. Use without --dry-run to apply changes.');
            return 0;
        }

        if ($this->confirm('Do you want to remove these expired discounts?')) {
            $updated = 0;
            
            foreach ($expiredSchedules as $schedule) {
                $schedule->update([
                    'is_discounted' => false,
                    'discount_percentage' => null,
                    'discount_expiry_date' => null,
                ]);
                $updated++;
            }

            $this->info("Successfully removed expired discounts from {$updated} schedule(s).");
        } else {
            $this->info('Operation cancelled.');
        }

        return 0;
    }
}
