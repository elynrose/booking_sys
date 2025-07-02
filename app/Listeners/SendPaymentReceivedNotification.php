<?php

namespace App\Listeners;

use App\Events\PaymentReceived;
use App\Notifications\PaymentConfirmedNotification;
use App\Notifications\AdminPaymentReceivedNotification;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendPaymentReceivedNotification
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
    public function handle(PaymentReceived $event): void
    {
        $payment = $event->payment;
        $user = $payment->user;

        // Send payment confirmation to user
        $user->notify(new PaymentConfirmedNotification($payment));

        // Send notification to all admins
        $admins = User::role('Admin')->get();
        foreach ($admins as $admin) {
            $admin->notify(new AdminPaymentReceivedNotification($payment));
        }
    }
} 