<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Schedule;
use Carbon\Carbon;
use Illuminate\Support\Facades\Route;

class ScheduleRescheduledNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $schedule;
    protected $oldDate;
    protected $oldTime;
    protected $reason;

    /**
     * Create a new notification instance.
     */
    public function __construct(Schedule $schedule, $oldDate = null, $oldTime = null, $reason = null)
    {
        $this->schedule = $schedule;
        $this->oldDate = $oldDate;
        $this->oldTime = $oldTime;
        $this->reason = $reason;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable)
    {
        $trainer = $this->schedule->trainer;
        
        $message = (new MailMessage)
            ->subject('Class Rescheduled - ' . $this->schedule->title)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('A class you were registered for has been rescheduled.')
            ->line('**Updated Class Details:**')
            ->line('- Class: ' . $this->schedule->title)
            ->line('- New Date: ' . $this->schedule->start_date->format('M d, Y'))
            ->line('- New Time: ' . $this->schedule->start_time->format('h:i A') . ' - ' . $this->schedule->end_time->format('h:i A'))
            ->line('- Trainer: ' . ($trainer ? $trainer->user->name : 'TBD'))
            ->line('- Location: ' . config('app.name') . ' Gym');

        if ($this->oldDate && $this->oldTime) {
            $message->line('**Previous Schedule:**')
                   ->line('- Date: ' . Carbon::parse($this->oldDate)->format('M d, Y'))
                   ->line('- Time: ' . $this->oldTime);
        }

        if ($this->reason) {
            $message->line('**Reason for Reschedule:**')
                   ->line($this->reason);
        }

        if (Route::has('schedules.show')) {
            $message->action('View Updated Schedule', route('schedules.show', $this->schedule));
        }
        $message->line('Please update your calendar and arrive 10 minutes before the new scheduled time.');

        return $message;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray($notifiable)
    {
        $data = [
            'schedule_id' => $this->schedule->id,
            'schedule_title' => $this->schedule->title,
            'old_date' => $this->oldDate,
            'old_time' => $this->oldTime,
            'new_date' => $this->schedule->start_date,
            'new_time' => $this->schedule->start_time->format('h:i A') . ' - ' . $this->schedule->end_time->format('h:i A'),
            'trainer_name' => $this->schedule->trainer ? $this->schedule->trainer->user->name : null,
            'reason' => $this->reason,
            'message' => 'Class rescheduled: ' . $this->schedule->title,
        ];
        if (Route::has('schedules.show')) {
            $data['action_url'] = route('schedules.show', $this->schedule);
        }
        return $data;
    }
} 