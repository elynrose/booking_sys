<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Booking;
use App\Models\Checkin;
use Illuminate\Support\Facades\Route;

class SessionCompletedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $booking;
    protected $checkin;
    protected $duration;

    /**
     * Create a new notification instance.
     */
    public function __construct(Booking $booking, Checkin $checkin, $duration = null)
    {
        $this->booking = $booking;
        $this->checkin = $checkin;
        $this->duration = $duration;
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
        $checkin = $this->booking->checkins()->latest()->first();
        
        $message = (new MailMessage)
            ->subject('Session Completed - ' . $schedule->title)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Great job! Your session has been completed successfully.')
            ->line('**Session Details:**')
            ->line('- Class: ' . $schedule->title)
            ->line('- Child: ' . ($child ? $child->name : 'N/A'))
            ->line('- Date: ' . $schedule->start_date->format('M d, Y'))
            ->line('- Time: ' . $schedule->start_time->format('h:i A') . ' - ' . $schedule->end_time->format('h:i A'))
            ->line('- Trainer: ' . ($schedule->trainer ? $schedule->trainer->user->name : 'TBD'));

        if ($checkin) {
            $duration = $checkin->checkout_time->diffInMinutes($checkin->checkin_time);
            $message->line('- Duration: ' . $duration . ' minutes');
        }

        $message->line('- Sessions Remaining: ' . $this->booking->sessions_remaining);
        if (Route::has('schedules.index')) {
            $message->action('Book Next Session', route('schedules.index'));
        }
        $message->line('Keep up the great work! We look forward to seeing you at your next session.');

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
            'booking_id' => $this->booking->id,
            'schedule_title' => $this->booking->schedule->title,
            'child_name' => $this->booking->child ? $this->booking->child->name : null,
            'schedule_date' => $this->booking->schedule->start_date,
            'trainer_name' => $this->booking->schedule->trainer ? $this->booking->schedule->trainer->user->name : null,
            'sessions_remaining' => $this->booking->sessions_remaining,
            'message' => 'Session completed for ' . $this->booking->schedule->title,
        ];
        if (Route::has('schedules.index')) {
            $data['action_url'] = route('schedules.index');
        }
        return $data;
    }
} 