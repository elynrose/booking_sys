<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Payment;

class AdminPaymentReceivedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $payment;

    /**
     * Create a new notification instance.
     */
    public function __construct(Payment $payment)
    {
        $this->payment = $payment;
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
            ->subject('New Payment Received - ' . $this->payment->booking->schedule->title)
            ->greeting('Hello Admin!')
            ->line('A new payment has been received.')
            ->line('Amount: $' . number_format($this->payment->amount, 2))
            ->line('Payment Method: ' . ucfirst($this->payment->payment_method))
            ->line('Class: ' . $this->payment->booking->schedule->title)
            ->line('Student: ' . ($this->payment->booking->child ? $this->payment->booking->child->name : 'Unknown Child'))
            ->line('Parent: ' . $this->payment->booking->user->name)
            ->line('Booking ID: #' . $this->payment->booking_id)
            ->action('View Payment Details', route('admin.payments.show', $this->payment))
            ->line('Payment has been automatically confirmed.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray($notifiable)
    {
        return [
            'payment_id' => $this->payment->id,
            'amount' => $this->payment->amount,
            'payment_method' => $this->payment->payment_method,
            'booking_id' => $this->payment->booking_id,
            'schedule_title' => $this->payment->booking->schedule->title,
            'child_name' => $this->payment->booking->child ? $this->payment->booking->child->name : 'Unknown Child',
            'user_name' => $this->payment->booking->user->name,
            'message' => 'New payment received for ' . $this->payment->booking->schedule->title
        ];
    }
}
