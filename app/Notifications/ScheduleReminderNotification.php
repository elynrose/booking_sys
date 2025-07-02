<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Schedule;

class ScheduleReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $schedule;

    /**
     * Create a new notification instance.
     */
    public function __construct(Schedule $schedule)
    {
        $this->schedule = $schedule;
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
        
        return (new MailMessage)
            ->subject('Class Starting Soon - ' . $this->schedule->title)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Your class is starting in 1 hour!')
            ->line('**Class Details:**')
            ->line('- Class: ' . $this->schedule->title)
            ->line('- Date: ' . $this->schedule->start_date->format('M d, Y'))
            ->line('- Time: ' . $this->schedule->start_time->format('h:i A') . ' - ' . $this->schedule->end_time->format('h:i A'))
            ->line('- Trainer: ' . ($trainer ? $trainer->user->name : 'TBD'))
            ->line('- Location: ' . config('app.name') . ' Gym')
            ->action('View Class Details', route('frontend.schedules.show', $this->schedule))
            ->line('Please arrive 10 minutes before the scheduled time. Don\'t forget your workout gear!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray($notifiable)
    {
        return [
            'schedule_id' => $this->schedule->id,
            'schedule_title' => $this->schedule->title,
            'schedule_date' => $this->schedule->start_date,
            'schedule_time' => $this->schedule->start_time->format('h:i A') . ' - ' . $this->schedule->end_time->format('h:i A'),
            'trainer_name' => $this->schedule->trainer ? $this->schedule->trainer->user->name : null,
            'message' => 'Class starting soon: ' . $this->schedule->title,
            'action_url' => route('frontend.schedules.show', $this->schedule),
        ];
    }
} 