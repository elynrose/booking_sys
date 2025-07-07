<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\RecommendationResponse;

class RecommendationResponseNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $response;

    /**
     * Create a new notification instance.
     */
    public function __construct(RecommendationResponse $response)
    {
        $this->response = $response;
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
        $recommendation = $this->response->recommendation;
        $responderName = $this->response->user->name;
        $childName = $recommendation->child->name;
        $trainerName = $recommendation->trainer->name;

        $mail = (new MailMessage)
            ->subject("New Response to Recommendation for {$childName}")
            ->greeting("Hello {$notifiable->name}!")
            ->line("{$responderName} has responded to a recommendation for {$childName}.")
            ->line("Recommendation: {$recommendation->title}")
            ->line("Response: " . substr($this->response->content, 0, 200) . "...");

        if (route('frontend.recommendations.show', $recommendation)) {
            $mail->action('View Recommendation', route('frontend.recommendations.show', $recommendation));
        }

        $mail->line('Please log in to your dashboard to view the full response.');

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
            'recommendation_id' => $this->response->recommendation_id,
            'response_id' => $this->response->id,
            'responder_name' => $this->response->user->name,
            'child_name' => $this->response->recommendation->child->name,
            'recommendation_title' => $this->response->recommendation->title,
            'response_preview' => substr($this->response->content, 0, 100),
        ];
    }
}
