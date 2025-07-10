<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use App\Notifications\Channels\TwilioSmsChannel;

class BookingConfirmedSmsNotification extends Notification implements ShouldQueue
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
        return [TwilioSmsChannel::class];
    }

    /**
     * Get the SMS representation of the notification.
     */
    public function toTwilioSms($notifiable)
    {
        $childName = $this->booking->child ? $this->booking->child->name : 'N/A';
        $className = $this->booking->schedule->title;
        $date = $this->booking->schedule->start_date->format('M j, Y');
        $time = $this->booking->schedule->start_time->format('g:i A');
        $trainerName = $this->booking->schedule->trainer ? $this->booking->schedule->trainer->user->name : 'TBD';
        $sessionsRemaining = $this->booking->sessions_remaining;

        return "Hi {$notifiable->name}! Your booking for {$childName} has been confirmed! Class: {$className} on {$date} at {$time} with {$trainerName}. Sessions remaining: {$sessionsRemaining}. Please arrive 10 minutes early.";
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray($notifiable)
    {
        return [
            'message' => 'Booking confirmed for ' . $this->booking->schedule->title,
            'booking_id' => $this->booking->id,
            'schedule_title' => $this->booking->schedule->title,
            'child_name' => $this->booking->child ? $this->booking->child->name : 'N/A',
            'action_url' => '/bookings/' . $this->booking->id,
        ];
    }
} 