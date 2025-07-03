<?php

namespace App\Listeners;

use App\Events\BookingCreated;
use App\Notifications\BookingCreatedNotification;
use App\Notifications\BookingCreatedSmsNotification;
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

        // Send email notification
        $user->notify(new BookingCreatedNotification($booking));

        // Send SMS notification if user has enabled SMS notifications
        if ($user->wantsSmsNotification('booking_created')) {
            $user->notify(new BookingCreatedSmsNotification($booking));
        }
    }
} 