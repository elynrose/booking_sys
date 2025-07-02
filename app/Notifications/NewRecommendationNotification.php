<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Recommendation;
use Illuminate\Support\Facades\Route;

class NewRecommendationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $recommendation;

    /**
     * Create a new notification instance.
     */
    public function __construct(Recommendation $recommendation)
    {
        $this->recommendation = $recommendation;
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
        $childName = $this->recommendation->child->name;
        $trainerName = $this->recommendation->trainer->name;
        $recommendationType = ucfirst($this->recommendation->type);
        $priority = ucfirst($this->recommendation->priority);

        $mail = (new MailMessage)
            ->subject("New {$recommendationType} Recommendation for {$childName}")
            ->greeting("Hello {$notifiable->name}!")
            ->line("Your trainer {$trainerName} has posted a new {$recommendationType} recommendation for {$childName}.")
            ->line("Priority: {$priority}")
            ->line("Title: {$this->recommendation->title}")
            ->line("Content: " . substr($this->recommendation->content, 0, 200) . "...");
        if (Route::has('recommendations.show')) {
            $mail->action('View Recommendation', route('recommendations.show', $this->recommendation));
        }
        $mail->line('Please log in to your dashboard to view the full recommendation and any attached files.');
        return $mail;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray($notifiable)
    {
        return [
            'recommendation_id' => $this->recommendation->id,
            'child_name' => $this->recommendation->child->name,
            'trainer_name' => $this->recommendation->trainer->name,
            'title' => $this->recommendation->title,
            'type' => $this->recommendation->type,
            'priority' => $this->recommendation->priority,
            'message' => "New {$this->recommendation->type} recommendation for {$this->recommendation->child->name}",
        ];
    }
}
