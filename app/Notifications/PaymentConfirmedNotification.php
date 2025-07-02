<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Payment;
use Illuminate\Support\Facades\Route;

class PaymentConfirmedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $payment;

    public function __construct(Payment $payment)
    {
        $this->payment = $payment;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        $mail = (new MailMessage)
            ->subject('Payment Confirmed')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Your payment of $' . number_format($this->payment->amount, 2) . ' has been confirmed.')
            ->line('Class: ' . $this->payment->booking->schedule->title)
            ->line('Child: ' . ($this->payment->booking->child ? $this->payment->booking->child->name : 'Unknown Child'))
            ->line('Description: ' . $this->payment->description);
        if (Route::has('bookings.show')) {
            $mail->action('View Booking', route('bookings.show', $this->payment->booking));
        }
        $mail->line('Thank you for your payment!');
        return $mail;
    }

    public function toArray($notifiable)
    {
        return [
            'payment_id' => $this->payment->id,
            'amount' => $this->payment->amount,
            'booking_id' => $this->payment->booking_id,
            'schedule_title' => $this->payment->booking->schedule->title,
            'child_name' => $this->payment->booking->child ? $this->payment->booking->child->name : 'Unknown Child',
            'description' => $this->payment->description,
        ];
    }
} 