<?php

namespace App\Listeners;

use App\Events\BookingReminder;
use App\Notifications\BookingReminderNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendBookingReminderNotification
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
    public function handle(BookingReminder $event): void
    {
        $booking = $event->booking;
        $user = $booking->user;

        // Send booking reminder notification
        $user->notify(new BookingReminderNotification($booking));
    }
} 