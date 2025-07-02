<?php

namespace App\Listeners;

use App\Events\ScheduleRescheduled;
use App\Notifications\ScheduleRescheduledNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendScheduleRescheduledNotification
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
    public function handle(ScheduleRescheduled $event): void
    {
        $schedule = $event->schedule;
        $oldDate = $event->oldDate;
        $oldTime = $event->oldTime;
        $reason = $event->reason;

        // Get all users with bookings for this schedule
        $bookings = $schedule->bookings()->with('user')->get();

        foreach ($bookings as $booking) {
            $user = $booking->user;
            
            // Send schedule rescheduled notification to each user
            $user->notify(new ScheduleRescheduledNotification(
                $schedule, 
                $oldDate, 
                $oldTime, 
                $reason
            ));
        }
    }
} 