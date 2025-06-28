<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AutoCheckoutNotification extends Notification
{
    use Queueable;

    public $booking;
    public $checkin;
    public $hours;
    public $minutes;
    public $seconds;

    /**
     * Create a new notification instance.
     */
    public function __construct($booking, $checkin, $hours, $minutes, $seconds)
    {
        $this->booking = $booking;
        $this->checkin = $checkin;
        $this->hours = $hours;
        $this->minutes = $minutes;
        $this->seconds = $seconds;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $duration = sprintf('%02d:%02d:%02d', $this->hours, $this->minutes, $this->seconds);
        
        return (new MailMessage)
            ->subject('Session Completed - Auto Checkout')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Your session has ended and you have been automatically checked out.')
            ->line('**Session Details:**')
            ->line('• Class: ' . $this->booking->schedule->title)
            ->line('• Child: ' . $this->booking->child->name)
            ->line('• Duration: ' . $duration)
            ->line('• Check-in: ' . $this->checkin->checkin_time->format('M d, Y h:i A'))
            ->line('• Check-out: ' . $this->checkin->checkout_time->format('M d, Y h:i A'))
            ->line('Thank you for using our gym services!')
            ->action('View Your Bookings', url('/bookings'))
            ->line('If you have any questions, please contact us.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'booking_id' => $this->booking->id,
            'checkin_id' => $this->checkin->id,
            'duration' => sprintf('%02d:%02d:%02d', $this->hours, $this->minutes, $this->seconds),
            'message' => 'Session time expired - auto checkout completed'
        ];
    }
} 