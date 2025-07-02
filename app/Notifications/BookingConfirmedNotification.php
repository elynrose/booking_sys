<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Booking;
use Illuminate\Support\Facades\Route;

class BookingConfirmedNotification extends Notification implements ShouldQueue
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
        $schedule = $this->booking->schedule;
        $child = $this->booking->child;
        
        $mail = (new MailMessage)
            ->subject('Booking Confirmed - ' . $schedule->title)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Your booking has been confirmed!')
            ->line('**Booking Details:**')
            ->line('- Class: ' . $schedule->title)
            ->line('- Child: ' . ($child ? $child->name : 'N/A'))
            ->line('- Date: ' . $schedule->start_date->format('M d, Y'))
            ->line('- Time: ' . $schedule->start_time->format('h:i A') . ' - ' . $schedule->end_time->format('h:i A'))
            ->line('- Trainer: ' . ($schedule->trainer ? $schedule->trainer->user->name : 'TBD'))
            ->line('- Sessions Remaining: ' . $this->booking->sessions_remaining);
        if (Route::has('bookings.show')) {
            $mail->action('View Booking Details', route('bookings.show', $this->booking));
        }
        $mail->line('Please arrive 10 minutes before the scheduled time. We look forward to seeing you!');
        return $mail;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray($notifiable)
    {
        $data = [
            'booking_id' => $this->booking->id,
            'schedule_title' => $this->booking->schedule->title,
            'child_name' => $this->booking->child ? $this->booking->child->name : null,
            'schedule_date' => $this->booking->schedule->start_date,
            'trainer_name' => $this->booking->schedule->trainer ? $this->booking->schedule->trainer->user->name : null,
            'sessions_remaining' => $this->booking->sessions_remaining,
            'message' => 'Booking confirmed for ' . $this->booking->schedule->title,
        ];
        if (\Illuminate\Support\Facades\Route::has('bookings.show')) {
            $data['action_url'] = route('bookings.show', $this->booking);
        }
        return $data;
    }
} 