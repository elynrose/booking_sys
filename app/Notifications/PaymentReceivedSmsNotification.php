<?php

namespace App\Notifications;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use App\Notifications\Channels\TwilioSmsChannel;

class PaymentReceivedSmsNotification extends Notification implements ShouldQueue
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
        return [TwilioSmsChannel::class];
    }

    /**
     * Get the SMS representation of the notification.
     */
    public function toTwilioSms($notifiable)
    {
        $amount = number_format($this->payment->amount, 2);
        $className = $this->payment->booking->schedule->title;
        $date = $this->payment->booking->schedule->start_time->format('M j, Y');
        $time = $this->payment->booking->schedule->start_time->format('g:i A');

        return "Hi {$notifiable->name}! Payment of \${$amount} received for {$className} on {$date} at {$time}. Your booking is now confirmed. Thank you!";
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray($notifiable)
    {
        return [
            'message' => 'Payment received for ' . $this->payment->booking->schedule->title,
            'payment_id' => $this->payment->id,
            'amount' => $this->payment->amount,
            'schedule_title' => $this->payment->booking->schedule->title,
            'action_url' => '/payments/' . $this->payment->id,
        ];
    }
} 