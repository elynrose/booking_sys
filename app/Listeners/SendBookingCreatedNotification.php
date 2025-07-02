<?php

namespace App\Listeners;

use App\Events\BookingCreated;
use App\Notifications\BookingConfirmedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendBookingCreatedNotification
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
    public function handle(BookingCreated $event): void
    {
        $booking = $event->booking;
        $user = $booking->user;

        // Send booking confirmation notification
        $user->notify(new BookingConfirmedNotification($booking));
    }
} 