<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Route;

class TwoFactorCodeNotification extends Notification
{
    use Queueable;

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $mail = (new MailMessage)
            ->line(__('global.two_factor.your_code_is', ['code' => $notifiable->two_factor_code]));
        if (Route::has('twoFactor.show')) {
            $mail->action(__('global.two_factor.verify_here'), route('twoFactor.show'));
        }
        $mail->line(__('global.two_factor.will_expire_in', ['minutes' => 15]))
            ->line(__('global.two_factor.ignore_this'));
        return $mail;
    }
}
