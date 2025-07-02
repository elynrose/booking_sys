<?php

namespace App\Listeners;

use App\Events\UserVerified;
use App\Notifications\VerifyUserNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendVerifyUserNotification
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
    public function handle(UserVerified $event): void
    {
        $user = $event->user;

        // Send verification notification to user
        $user->notify(new VerifyUserNotification($user));
    }
} 