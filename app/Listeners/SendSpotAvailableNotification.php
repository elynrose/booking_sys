<?php

namespace App\Listeners;

use App\Events\SpotAvailable;
use App\Notifications\SpotAvailableNotification;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendSpotAvailableNotification
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
    public function handle(SpotAvailable $event): void
    {
        $schedule = $event->schedule;

        // If a specific user is provided, notify them
        if ($event->user) {
            $event->user->notify(new SpotAvailableNotification($schedule));
            return;
        }

        // Otherwise, notify all users who might be interested
        // This could be users on a waitlist or users who previously tried to book
        $interestedUsers = User::whereHas('bookings', function($query) use ($schedule) {
            $query->where('schedule_id', $schedule->id)
                  ->where('status', 'cancelled');
        })->get();

        foreach ($interestedUsers as $user) {
            $user->notify(new SpotAvailableNotification($schedule));
        }
    }
} 