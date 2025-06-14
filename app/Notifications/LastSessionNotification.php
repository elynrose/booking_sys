<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LastSessionNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $booking;

    /**
     * Create a new notification instance.
     */
    public function __construct(Booking $booking)
    {
        $this->booking = $booking;
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
        return (new MailMessage)
            ->subject('Last Session Reminder')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('This is a reminder that your child has only one session remaining.')
            ->line('Child: ' . $this->booking->child_name)
            ->line('Schedule: ' . $this->booking->schedule->title)
            ->action('Book More Sessions', url('/bookings/create'))
            ->line('Don\'t forget to book more sessions to continue your child\'s training.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray($notifiable)
    {
        return [
            'message' => 'Last session reminder for ' . $this->booking->child_name,
            'booking_id' => $this->booking->id,
            'action_url' => '/bookings/create',
        ];
    }
}
