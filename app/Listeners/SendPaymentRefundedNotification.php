<?php

namespace App\Listeners;

use App\Events\PaymentRefunded;
use App\Notifications\PaymentRefundedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendPaymentRefundedNotification
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(PaymentRefunded $event): void
    {
        $payment = $event->payment;
        $user = $payment->user;

        // Send payment refunded notification
        $user->notify(new PaymentRefundedNotification(
            $payment, 
            $event->refundAmount, 
            $event->reason
        ));
    }
} 