<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\PaymentService;

class SendPaymentReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-payment-reminders {--dry-run : Show what would be sent without sending emails}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send payment reminders for pending payments';

    /**
     * Execute the console command.
     */
    public function handle(PaymentService $paymentService): int
    {
        $this->info('Sending payment reminders...');
        
        if ($this->option('dry-run')) {
            $this->warn('DRY RUN MODE - No emails will be sent');
        }
        
        try {
            $sentCount = $paymentService->sendPaymentReminders();
            
            if ($this->option('dry-run')) {
                $this->info("Would send {$sentCount} payment reminders");
            } else {
                $this->info("Successfully sent {$sentCount} payment reminders");
            }
            
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $this->error('Error sending payment reminders: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
