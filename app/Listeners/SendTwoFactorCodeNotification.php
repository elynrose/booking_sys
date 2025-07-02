<?php

namespace App\Listeners;

use App\Events\TwoFactorCodeSent;
use App\Notifications\TwoFactorCodeNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendTwoFactorCodeNotification
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(TwoFactorCodeSent $event): void
    {
        $user = $event->user;

        // Send 2FA code notification to user
        $user->notify(new TwoFactorCodeNotification($user, $event->code));
    }
} 