<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Route;

class VerifyUserNotification extends Notification
{
    use Queueable;

    private $user = null;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $mail = (new MailMessage)
            ->line(trans('global.verifyYourUser'));
        if (Route::has('userVerification')) {
            $mail->action(trans('global.clickHereToVerify'), route('userVerification', $this->user->verification_token));
        }
        $mail->line(trans('global.thankYouForUsingOurApplication'));
        return $mail;
    }

    public function toArray($notifiable)
    {
        return [];
    }
}
