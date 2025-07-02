<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\User;
use App\Models\Child;

class HealthCheckNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $user;
    protected $child;
    protected $dueDate;

    /**
     * Create a new notification instance.
     */
    public function __construct(User $user, Child $child = null, $dueDate = null)
    {
        $this->user = $user;
        $this->child = $child;
        $this->dueDate = $dueDate;
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
        $childName = $this->child ? $this->child->name : 'your child';
        
        $message = (new MailMessage)
            ->subject('Health Screening Reminder')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('This is a friendly reminder about health screening requirements.');

        if ($this->dueDate) {
            $message->line('**Due Date:** ' . $this->dueDate);
        }

        $message->line('**For:** ' . $childName)
               ->line('**Required Health Information:**')
               ->line('- Medical conditions or allergies')
               ->line('- Current medications')
               ->line('- Emergency contact information')
               ->line('- Recent injuries or concerns')
               ->action('Update Health Information', route('frontend.profile.edit'))
               ->line('Please ensure all health information is up to date for the safety of all participants.');

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
            'user_id' => $this->user->id,
            'child_id' => $this->child ? $this->child->id : null,
            'child_name' => $this->child ? $this->child->name : null,
            'due_date' => $this->dueDate,
            'message' => 'Health screening reminder for ' . ($this->child ? $this->child->name : 'your child'),
            'action_url' => route('frontend.profile.edit'),
        ];
    }
} 