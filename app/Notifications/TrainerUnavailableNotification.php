<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Trainer;
use App\Models\Schedule;

class TrainerUnavailableNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $trainer;
    protected $schedule;
    protected $reason;
    protected $unavailableUntil;

    /**
     * Create a new notification instance.
     */
    public function __construct(Trainer $trainer, Schedule $schedule = null, $reason = null, $unavailableUntil = null)
    {
        $this->trainer = $trainer;
        $this->schedule = $schedule;
        $this->reason = $reason;
        $this->unavailableUntil = $unavailableUntil;
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
        $message = (new MailMessage)
            ->subject('Trainer Unavailable - ' . $this->trainer->user->name)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Unfortunately, your trainer ' . $this->trainer->user->name . ' is currently unavailable.');

        if ($this->schedule) {
            $message->line('**Affected Class:**')
                   ->line('- Class: ' . $this->schedule->title)
                   ->line('- Date: ' . $this->schedule->start_date->format('M d, Y'))
                   ->line('- Time: ' . $this->schedule->start_time->format('h:i A') . ' - ' . $this->schedule->end_time->format('h:i A'));
        }

        if ($this->reason) {
            $message->line('**Reason:**')
                   ->line($this->reason);
        }

        if ($this->unavailableUntil) {
            $message->line('**Expected Return:**')
                   ->line($this->unavailableUntil);
        }

        $message->action('View Available Classes', route('frontend.schedules.index'))
                ->line('We are working to reschedule your classes or provide alternative arrangements. We will contact you soon with updates.');

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
            'trainer_id' => $this->trainer->id,
            'trainer_name' => $this->trainer->user->name,
            'schedule_id' => $this->schedule ? $this->schedule->id : null,
            'schedule_title' => $this->schedule ? $this->schedule->title : null,
            'reason' => $this->reason,
            'unavailable_until' => $this->unavailableUntil,
            'message' => 'Trainer ' . $this->trainer->user->name . ' is unavailable',
            'action_url' => route('frontend.schedules.index'),
        ];
    }
} 