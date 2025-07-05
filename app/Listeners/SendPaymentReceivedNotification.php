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
        try {
            $user->notify(new PaymentConfirmedNotification($payment));
        } catch (\Exception $e) {
            \Log::error('Failed to send payment confirmation email to user', [
                'payment_id' => $payment->id,
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
        }

        // Send notification to all admins
        try {
            $admins = User::role('Admin')->get();
            foreach ($admins as $admin) {
                try {
                    $admin->notify(new AdminPaymentReceivedNotification($payment));
                } catch (\Exception $e) {
                    \Log::error('Failed to send admin payment notification', [
                        'payment_id' => $payment->id,
                        'admin_id' => $admin->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        } catch (\Exception $e) {
            \Log::error('Failed to send admin notifications', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage()
            ]);
        }
    }
} 