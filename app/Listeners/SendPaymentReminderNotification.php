<?php

namespace App\Listeners;

use App\Events\PaymentReminder;
use App\Notifications\PaymentReminderNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendPaymentReminderNotification
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
    public function handle(PaymentReminder $event): void
    {
        $booking = $event->booking;
        $user = $booking->user;

        // Send payment reminder notification
        $user->notify(new PaymentReminderNotification($booking));
    }
} 