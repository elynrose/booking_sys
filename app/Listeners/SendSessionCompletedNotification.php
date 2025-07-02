<?php

namespace App\Listeners;

use App\Events\SessionCompleted;
use App\Notifications\SessionCompletedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendSessionCompletedNotification
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
    public function handle(SessionCompleted $event): void
    {
        $booking = $event->booking;
        $checkin = $event->checkin;
        $duration = $event->duration;
        $user = $booking->user;

        // Send session completed notification
        $user->notify(new SessionCompletedNotification($booking, $checkin, $duration));
    }
} 