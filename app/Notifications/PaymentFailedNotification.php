<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Payment;
use Illuminate\Support\Facades\Route;

class PaymentFailedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $payment;
    protected $errorMessage;

    /**
     * Create a new notification instance.
     */
    public function __construct(Payment $payment, $errorMessage = null)
    {
        $this->payment = $payment;
        $this->errorMessage = $errorMessage;
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
        $booking = $this->payment->booking;
        $schedule = $booking->schedule;
        
        $message = (new MailMessage)
            ->subject('Payment Failed - ' . $schedule->title)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('We were unable to process your payment.')
            ->line('**Payment Details:**')
            ->line('- Amount: $' . number_format($this->payment->amount, 2))
            ->line('- Payment Method: ' . ucfirst($this->payment->payment_method))
            ->line('- Class: ' . $schedule->title)
            ->line('- Child: ' . ($booking->child ? $booking->child->name : 'N/A'));

        if ($this->errorMessage) {
            $message->line('**Error:**')
                   ->line($this->errorMessage);
        }

        if (Route::has('payments.index')) {
            $message->action('Retry Payment', route('payments.index'));
        }
        $message->line('Please check your payment method and try again. If the problem persists, please contact us for assistance.');

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
            'payment_id' => $this->payment->id,
            'amount' => $this->payment->amount,
            'payment_method' => $this->payment->payment_method,
            'booking_id' => $this->payment->booking_id,
            'schedule_title' => $this->payment->booking->schedule->title,
            'child_name' => $this->payment->booking->child ? $this->payment->booking->child->name : null,
            'error_message' => $this->errorMessage,
            'message' => 'Payment failed for ' . $this->payment->booking->schedule->title,
            'action_url' => route('frontend.payments.index'),
        ];
    }
} 