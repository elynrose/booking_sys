<?php

namespace App\Notifications;

use App\Models\Schedule;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use App\Notifications\Channels\TwilioSmsChannel;

class ClassCancelledSmsNotification extends Notification implements ShouldQueue
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
        return [TwilioSmsChannel::class];
    }

    /**
     * Get the SMS representation of the notification.
     */
    public function toTwilioSms($notifiable)
    {
        $className = $this->schedule->title;
        $date = $this->schedule->start_date->format('M j, Y');
        $time = $this->schedule->start_time->format('g:i A');

        return "Hi {$notifiable->name}! Your class {$className} on {$date} at {$time} has been cancelled. We apologize for any inconvenience. Please check our schedule for alternative classes.";
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray($notifiable)
    {
        return [
            'message' => 'Class cancelled: ' . $this->schedule->title,
            'schedule_id' => $this->schedule->id,
            'schedule_title' => $this->schedule->title,
            'action_url' => '/schedules',
        ];
    }
} 