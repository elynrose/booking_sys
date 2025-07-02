<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingCreatedNotification extends Notification implements ShouldQueue
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
            ->subject('Booking Created Successfully')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Your booking has been created successfully.')
            ->line('Child: ' . $this->booking->child->name)
            ->line('Class: ' . $this->booking->schedule->title)
            ->line('Date: ' . $this->booking->schedule->start_time->format('F j, Y'))
            ->line('Time: ' . $this->booking->schedule->start_time->format('g:i A'))
            ->line('Price: $' . $this->booking->schedule->price)
            ->action('View Booking', url('/bookings/' . $this->booking->id))
            ->line('Please complete payment to confirm your booking.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray($notifiable)
    {
        return [
            'message' => 'Booking created for ' . $this->booking->schedule->title,
            'booking_id' => $this->booking->id,
            'schedule_title' => $this->booking->schedule->title,
            'child_name' => $this->booking->child->name,
            'action_url' => '/bookings/' . $this->booking->id,
        ];
    }
} 