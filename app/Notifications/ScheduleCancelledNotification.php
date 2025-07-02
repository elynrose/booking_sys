<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Schedule;
use Illuminate\Support\Facades\Route;

class ScheduleCancelledNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $schedule;
    protected $reason;

    /**
     * Create a new notification instance.
     */
    public function __construct(Schedule $schedule, $reason = null)
    {
        $this->schedule = $schedule;
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
            ->subject('Class Cancelled - ' . $this->schedule->title)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('A class you were registered for has been cancelled.')
            ->line('**Cancelled Class Details:**')
            ->line('- Class: ' . $this->schedule->title)
            ->line('- Date: ' . $this->schedule->start_date->format('M d, Y'))
            ->line('- Time: ' . $this->schedule->start_time->format('h:i A') . ' - ' . $this->schedule->end_time->format('h:i A'))
            ->line('- Trainer: ' . ($this->schedule->trainer ? $this->schedule->trainer->user->name : 'TBD'));

        if ($this->reason) {
            $message->line('**Reason for Cancellation:**')
                   ->line($this->reason);
        }

        if (Route::has('schedules.index')) {
            $message->action('View Available Classes', route('schedules.index'));
        }

        $message->line('We apologize for any inconvenience. Please check our schedule for alternative classes.');

        return $message;
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
            'reason' => $this->reason,
            'message' => 'Class cancelled: ' . $this->schedule->title,
            'action_url' => route('schedules.index'),
        ];
    }
} 