<?php

namespace App\Listeners;

use App\Events\ScheduleCancelled;
use App\Notifications\ScheduleCancelledNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendScheduleCancelledNotification
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
    public function handle(ScheduleCancelled $event): void
    {
        $schedule = $event->schedule;
        $reason = $event->reason;

        // Get all users with bookings for this schedule
        $bookings = $schedule->bookings()->with('user')->get();

        foreach ($bookings as $booking) {
            $user = $booking->user;
            
            // Send schedule cancelled notification to each user
            $user->notify(new ScheduleCancelledNotification($schedule, $reason));
        }
    }
} 