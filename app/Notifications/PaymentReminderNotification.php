<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentReminderNotification extends Notification implements ShouldQueue
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
            ->subject('Payment Reminder for Your Booking')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('This is a reminder that payment is pending for your booking.')
            ->line('Child: ' . $this->booking->child_name)
            ->line('Schedule: ' . $this->booking->schedule->title)
            ->line('Amount: $' . $this->booking->schedule->price)
            ->action('Make Payment', url('/payments/' . $this->booking->id))
            ->line('Please complete the payment to confirm your booking.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray($notifiable)
    {
        return [
            'message' => 'Payment reminder for booking #' . $this->booking->id,
            'booking_id' => $this->booking->id,
            'amount' => $this->booking->schedule->price,
            'action_url' => '/payments/' . $this->booking->id,
        ];
    }
}
