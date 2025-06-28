<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Booking;
use App\Models\Checkin;

class LateCheckinNotification extends Notification
{
    use Queueable;

    public $booking;
    public $checkin;
    public $lateMinutes;

    /**
     * Create a new notification instance.
     */
    public function __construct(Booking $booking, Checkin $checkin, int $lateMinutes)
    {
        $this->booking = $booking;
        $this->checkin = $checkin;
        $this->lateMinutes = $lateMinutes;
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
        $user = $this->booking->user;
        $child = $this->booking->child;
        $schedule = $this->booking->schedule;
        
        return (new MailMessage)
            ->subject('Late Check-in Alert - ' . $schedule->title)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('A member has checked in late for their scheduled class.')
            ->line('**Member Details:**')
            ->line('- Member: ' . $user->name . ' (ID: ' . $user->member_id . ')')
            ->line('- Child: ' . ($child ? $child->name : 'N/A'))
            ->line('- Class: ' . $schedule->title)
            ->line('- Scheduled Time: ' . $schedule->start_time . ' - ' . $schedule->end_time)
            ->line('- Check-in Time: ' . $this->checkin->checkin_time->format('h:i A'))
            ->line('- **Late by: ' . $this->lateMinutes . ' minutes**')
            ->action('View Check-in Details', url('/admin/checkins'))
            ->line('Please review this late check-in and take appropriate action if necessary.');
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
            'user_name' => $this->booking->user->name,
            'member_id' => $this->booking->user->member_id,
            'child_name' => $this->booking->child ? $this->booking->child->name : null,
            'schedule_title' => $this->booking->schedule->title,
            'late_minutes' => $this->lateMinutes,
            'checkin_time' => $this->checkin->checkin_time,
        ];
    }
}
