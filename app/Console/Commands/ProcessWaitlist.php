<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Waitlist;
use App\Models\Schedule;
use Carbon\Carbon;

class ProcessWaitlist extends Command
{
    protected $signature = 'waitlist:process';
    protected $description = 'Process the waitlist and notify users when spots become available';

    public function handle()
    {
        $this->info('Processing waitlist...');

        // Get all schedules that have spots available
        $schedules = Schedule::where('current_participants', '<', 'max_participants')
            ->where('status', '=', 'active')
            ->get();

        foreach ($schedules as $schedule) {
            $availableSpots = $schedule->max_participants - $schedule->current_participants;
            
            if ($availableSpots <= 0) {
                continue;
            }

            // Get pending waitlist entries for this schedule, ordered by creation date
            $waitlistEntries = Waitlist::where('schedule_id', $schedule->id)
                ->where('status', 'pending')
                ->orderBy('created_at', 'asc')
                ->take($availableSpots)
                ->get();

            foreach ($waitlistEntries as $entry) {
                // Check if user has already been notified in the last 24 hours
                if ($entry->notified_at && $entry->notified_at->addHours(24)->isFuture()) {
                    continue;
                }

                // Send notification
                $entry->user->notify(new \App\Notifications\SpotAvailableNotification($entry));

                // Update waitlist entry
                $entry->update([
                    'status' => 'notified',
                    'notified_at' => Carbon::now()
                ]);

                $this->info("Notified user {$entry->user->name} about available spot in schedule {$schedule->id}");
            }
        }

        $this->info('Waitlist processing completed.');
    }
} 