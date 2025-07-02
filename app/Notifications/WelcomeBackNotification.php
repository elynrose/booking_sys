<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Route;

class WelcomeBackNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $user;
    protected $inactiveDays;

    /**
     * Create a new notification instance.
     */
    public function __construct(User $user, $inactiveDays = null)
    {
        $this->user = $user;
        $this->inactiveDays = $inactiveDays;
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
        $message = (new MailMessage)
            ->subject('Welcome Back! We Missed You')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('We noticed you haven\'t been to the gym in a while and we wanted to check in.');

        if ($this->inactiveDays) {
            $message->line('It\'s been ' . $this->inactiveDays . ' days since your last session.');
        }

        $message->line('**We\'d love to see you again!**')
               ->line('- New classes are available')
               ->line('- Special welcome back offers')
               ->line('- Your progress is waiting for you');
        if (Route::has('schedules.index')) {
            $message->action('View Available Classes', route('schedules.index'));
        }
        $message->line('Don\'t let your fitness journey pause. Book a session today and get back on track!');

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
            'user_id' => $this->user->id,
            'inactive_days' => $this->inactiveDays,
            'message' => 'Welcome back! We missed you',
            'action_url' => route('frontend.schedules.index'),
        ];
    }
} 