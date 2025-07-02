<?php

namespace App\Observers;

use App\Events\ScheduleCancelled;
use App\Events\ScheduleRescheduled;
use App\Models\Schedule;

class ScheduleObserver
{
    /**
     * Handle the Schedule "created" event.
     */
    public function created(Schedule $schedule): void
    {
        // Handle schedule creation if needed
    }

    /**
     * Handle the Schedule "updated" event.
     */
    public function updated(Schedule $schedule): void
    {
        // Check if status changed to cancelled
        if ($schedule->wasChanged('status') && $schedule->status === 'cancelled') {
            event(new ScheduleCancelled($schedule));
        }
        
        // Check if dates or times changed (rescheduled)
        if ($schedule->wasChanged(['start_date', 'end_date', 'start_time', 'end_time'])) {
            $oldDate = $schedule->getOriginal('start_date');
            $oldTime = $schedule->getOriginal('start_time') . ' - ' . $schedule->getOriginal('end_time');
            
            event(new ScheduleRescheduled($schedule, $oldDate, $oldTime));
        }
    }

    /**
     * Handle the Schedule "deleted" event.
     */
    public function deleted(Schedule $schedule): void
    {
        // Fire schedule cancelled event when deleted
        event(new ScheduleCancelled($schedule, 'Schedule deleted'));
    }

    /**
     * Handle the Schedule "restored" event.
     */
    public function restored(Schedule $schedule): void
    {
        // Handle schedule restoration if needed
    }

    /**
     * Handle the Schedule "force deleted" event.
     */
    public function forceDeleted(Schedule $schedule): void
    {
        // Handle force deletion if needed
    }
} 