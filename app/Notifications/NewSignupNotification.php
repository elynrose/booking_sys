<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Route;

class NewSignupNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        //
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
        $mail = (new MailMessage)
            ->subject('Welcome to Our Gym App!')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Thank you for signing up with our gym app.')
            ->line('You can now start booking sessions for your child.');
        if (Route::has('schedules.index')) {
            $mail->action('View Classes', route('schedules.index'));
        }
        $mail->line('If you have any questions, please don\'t hesitate to contact us.');
        return $mail;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray($notifiable)
    {
        return [
            'message' => 'Welcome to our gym app! You can now start booking sessions.',
            'action_url' => '/bookings/create',
        ];
    }
}
