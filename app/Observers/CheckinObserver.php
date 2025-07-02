<?php

namespace App\Observers;

use App\Events\SessionCompleted;
use App\Models\Checkin;

class CheckinObserver
{
    /**
     * Handle the Checkin "created" event.
     */
    public function created(Checkin $checkin): void
    {
        // Handle checkin creation if needed
    }

    /**
     * Handle the Checkin "updated" event.
     */
    public function updated(Checkin $checkin): void
    {
        // Check if checkout_time was set (session completed)
        if ($checkin->wasChanged('checkout_time') && $checkin->checkout_time) {
            $booking = $checkin->booking;
            
            // Calculate duration
            $duration = $checkin->checkin_time->diff($checkin->checkout_time);
            $durationString = sprintf('%02d:%02d:%02d', 
                $duration->h, 
                $duration->i, 
                $duration->s
            );
            
            // Fire session completed event
            event(new SessionCompleted($booking, $checkin, $durationString));
        }
    }

    /**
     * Handle the Checkin "deleted" event.
     */
    public function deleted(Checkin $checkin): void
    {
        // Handle checkin deletion if needed
    }

    /**
     * Handle the Checkin "restored" event.
     */
    public function restored(Checkin $checkin): void
    {
        // Handle checkin restoration if needed
    }

    /**
     * Handle the Checkin "force deleted" event.
     */
    public function forceDeleted(Checkin $checkin): void
    {
        // Handle force deletion if needed
    }
} 