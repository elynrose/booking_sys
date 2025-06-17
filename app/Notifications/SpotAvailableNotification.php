<?php

namespace App\Notifications;

use App\Models\Waitlist;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SpotAvailableNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $waitlist;

    /**
     * Create a new notification instance.
     */
    public function __construct(Waitlist $waitlist)
    {
        $this->waitlist = $waitlist;
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
        return (new MailMessage)
            ->subject('Spot Available in Class')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('A spot has become available in the class you were waitlisted for.')
            ->line('Class: ' . $this->waitlist->schedule->title)
            ->line('Child: ' . $this->waitlist->child->name)
            ->line('You have 24 hours to book this spot before it\'s offered to the next person on the waitlist.')
            ->action('Book Now', route('bookings.create', $this->waitlist->schedule))
            ->line('Thank you for your patience!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray($notifiable)
    {
        return [
            'message' => 'A spot is available in ' . $this->waitlist->schedule->title,
            'waitlist_id' => $this->waitlist->id,
            'schedule_id' => $this->waitlist->schedule_id,
            'action_url' => route('bookings.create', $this->waitlist->schedule)
        ];
    }
}
