<?php

namespace App\Notifications;

use App\Models\Schedule;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use App\Notifications\Channels\TwilioSmsChannel;

class ClassRescheduledSmsNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $schedule;
    protected $oldDate;
    protected $oldTime;

    /**
     * Create a new notification instance.
     */
    public function __construct(Schedule $schedule, $oldDate = null, $oldTime = null)
    {
        $this->schedule = $schedule;
        $this->oldDate = $oldDate;
        $this->oldTime = $oldTime;
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
        $newDate = $this->schedule->start_date->format('M j, Y');
        $newTime = $this->schedule->start_time->format('g:i A');
        
        $oldInfo = '';
        if ($this->oldDate && $this->oldTime) {
            $oldInfo = " (previously {$this->oldDate} at {$this->oldTime})";
        }

        return "Hi {$notifiable->name}! Your class {$className} has been rescheduled to {$newDate} at {$newTime}{$oldInfo}. Please update your calendar.";
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray($notifiable)
    {
        return [
            'message' => 'Class rescheduled: ' . $this->schedule->title,
            'schedule_id' => $this->schedule->id,
            'schedule_title' => $this->schedule->title,
            'action_url' => '/schedules/' . $this->schedule->id,
        ];
    }
} 