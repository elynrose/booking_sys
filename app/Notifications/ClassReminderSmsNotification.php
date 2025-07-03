<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use App\Notifications\Channels\TwilioSmsChannel;

class ClassReminderSmsNotification extends Notification implements ShouldQueue
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
        $date = $this->booking->schedule->start_time->format('M j, Y');
        $time = $this->booking->schedule->start_time->format('g:i A');
        $trainerName = $this->booking->schedule->trainer ? $this->booking->schedule->trainer->user->name : 'TBD';

        return "Hi {$notifiable->name}! Reminder: {$childName} has {$className} tomorrow ({$date}) at {$time} with {$trainerName}. Don't forget to bring water and comfortable clothes!";
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray($notifiable)
    {
        return [
            'message' => 'Class reminder for ' . $this->booking->schedule->title,
            'booking_id' => $this->booking->id,
            'schedule_title' => $this->booking->schedule->title,
            'child_name' => $this->booking->child ? $this->booking->child->name : 'N/A',
            'action_url' => '/bookings/' . $this->booking->id,
        ];
    }
} 