<?php

namespace App\Observers;

use App\Events\PaymentReceived;
use App\Events\PaymentRefunded;
use App\Models\Payment;

class PaymentObserver
{
    /**
     * Handle the Payment "created" event.
     */
    public function created(Payment $payment): void
    {
        // Fire payment received event
        event(new PaymentReceived($payment));
    }

    /**
     * Handle the Payment "updated" event.
     */
    public function updated(Payment $payment): void
    {
        // Check if status changed to refunded
        if ($payment->wasChanged('status') && $payment->status === 'refunded') {
            event(new PaymentRefunded($payment));
        }
    }

    /**
     * Handle the Payment "deleted" event.
     */
    public function deleted(Payment $payment): void
    {
        // Handle payment deletion if needed
    }

    /**
     * Handle the Payment "restored" event.
     */
    public function restored(Payment $payment): void
    {
        // Handle payment restoration if needed
    }

    /**
     * Handle the Payment "force deleted" event.
     */
    public function forceDeleted(Payment $payment): void
    {
        // Handle force deletion if needed
    }
} 