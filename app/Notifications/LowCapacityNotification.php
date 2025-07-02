<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Schedule;

class LowCapacityNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $schedule;
    protected $spotsRemaining;

    /**
     * Create a new notification instance.
     */
    public function __construct(Schedule $schedule, $spotsRemaining = null)
    {
        $this->schedule = $schedule;
        $this->spotsRemaining = $spotsRemaining ?: ($schedule->max_participants - $schedule->current_participants);
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
            ->subject('Limited Spots Available - ' . $this->schedule->title)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('This popular class is filling up fast!')
            ->line('**Class Details:**')
            ->line('- Class: ' . $this->schedule->title)
            ->line('- Date: ' . $this->schedule->start_date->format('M d, Y'))
            ->line('- Time: ' . $this->schedule->start_time->format('h:i A') . ' - ' . $this->schedule->end_time->format('h:i A'))
            ->line('- Trainer: ' . ($trainer ? $trainer->user->name : 'TBD'))
            ->line('- **Spots Remaining: ' . $this->spotsRemaining . '**')
            ->action('Book Now', route('frontend.schedules.show', $this->schedule))
            ->line('Don\'t miss out! Book your spot before it\'s gone.');
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
            'trainer_name' => $this->schedule->trainer ? $this->schedule->trainer->user->name : null,
            'spots_remaining' => $this->spotsRemaining,
            'message' => 'Limited spots available: ' . $this->schedule->title . ' (' . $this->spotsRemaining . ' spots left)',
            'action_url' => route('frontend.schedules.show', $this->schedule),
        ];
    }
} 