<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Payment;
use Illuminate\Support\Facades\Route;

class PaymentRefundedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $payment;
    protected $refundAmount;
    protected $reason;

    /**
     * Create a new notification instance.
     */
    public function __construct(Payment $payment, $refundAmount = null, $reason = null)
    {
        $this->payment = $payment;
        $this->refundAmount = $refundAmount ?: $payment->amount;
        $this->reason = $reason;
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
            ->subject('Payment Refunded - ' . $schedule->title)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Your payment has been refunded.')
            ->line('**Refund Details:**')
            ->line('- Amount: $' . number_format($this->refundAmount, 2))
            ->line('- Payment Method: ' . ucfirst($this->payment->payment_method))
            ->line('- Transaction ID: ' . $this->payment->transaction_id)
            ->line('- Class: ' . $schedule->title)
            ->line('- Child: ' . ($booking->child ? $booking->child->name : 'N/A'))
            ->line('- Refund Date: ' . now()->format('M d, Y h:i A'));

        if ($this->reason) {
            $message->line('**Reason for Refund:**')
                   ->line($this->reason);
        }

        if (Route::has('payments.index')) {
            $message->action('View Payment Details', route('payments.index'));
        }
        $message->line('The refund will be processed according to your payment method\'s timeline. If you have any questions, please contact us.');

        return $message;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray($notifiable)
    {
        $data = [
            'payment_id' => $this->payment->id,
            'amount' => $this->payment->amount,
            'payment_method' => $this->payment->payment_method,
            'transaction_id' => $this->payment->transaction_id,
            'booking_id' => $this->payment->booking_id,
            'schedule_title' => $this->payment->booking->schedule->title,
            'child_name' => $this->payment->booking->child ? $this->payment->booking->child->name : null,
            'reason' => $this->reason,
            'message' => 'Payment refunded for ' . $this->payment->booking->schedule->title,
        ];
        if (Route::has('payments.index')) {
            $data['action_url'] = route('payments.index');
        }
        return $data;
    }
} 