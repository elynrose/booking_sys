<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EmergencyNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $emergencyType;
    protected $message;
    protected $instructions;

    /**
     * Create a new notification instance.
     */
    public function __construct($emergencyType = null, $message = null, $instructions = null)
    {
        $this->emergencyType = $emergencyType;
        $this->message = $message;
        $this->instructions = $instructions;
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
            ->subject('🚨 EMERGENCY ALERT - ' . config('app.name'))
            ->greeting('URGENT: ' . $notifiable->name)
            ->line('This is an emergency notification from ' . config('app.name') . '.');

        if ($this->emergencyType) {
            $message->line('**Emergency Type:** ' . $this->emergencyType);
        }

        if ($this->message) {
            $message->line('**Message:** ' . $this->message);
        }

        if ($this->instructions) {
            $message->line('**Instructions:** ' . $this->instructions);
        }

        $message->line('**Please follow all safety protocols and instructions from staff.**')
               ->line('**Emergency Contact:** ' . config('app.emergency_contact', '911'))
               ->line('**Gym Phone:** ' . config('app.gym_phone', 'Contact staff immediately'))
               ->line('Stay safe and follow all emergency procedures.');

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
            'emergency_type' => $this->emergencyType,
            'message' => $this->message,
            'instructions' => $this->instructions,
            'notification_type' => 'emergency',
            'action_url' => config('app.url'),
        ];
    }
} 