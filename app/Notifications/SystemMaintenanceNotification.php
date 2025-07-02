<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SystemMaintenanceNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $maintenanceDate;
    protected $duration;
    protected $description;

    /**
     * Create a new notification instance.
     */
    public function __construct($maintenanceDate = null, $duration = null, $description = null)
    {
        $this->maintenanceDate = $maintenanceDate;
        $this->duration = $duration;
        $this->description = $description;
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
            ->subject('Scheduled System Maintenance')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('We wanted to inform you about scheduled system maintenance.');

        if ($this->maintenanceDate) {
            $message->line('**Maintenance Date:** ' . $this->maintenanceDate);
        }

        if ($this->duration) {
            $message->line('**Expected Duration:** ' . $this->duration);
        }

        if ($this->description) {
            $message->line('**What\'s happening:** ' . $this->description);
        }

        $message->line('**What this means for you:**')
               ->line('- The gym app may be temporarily unavailable')
               ->line('- You won\'t be able to book or modify sessions during maintenance')
               ->line('- All existing bookings will remain intact')
               ->line('- We\'ll notify you when the system is back online')
               ->action('Contact Support', config('app.url') . '/contact')
               ->line('We apologize for any inconvenience and appreciate your patience.');

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
            'maintenance_date' => $this->maintenanceDate,
            'duration' => $this->duration,
            'description' => $this->description,
            'message' => 'Scheduled system maintenance notification',
            'action_url' => config('app.url') . '/contact',
        ];
    }
} 