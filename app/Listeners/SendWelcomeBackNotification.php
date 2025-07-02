<?php

namespace App\Listeners;

use App\Events\UserReturned;
use App\Notifications\WelcomeBackNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendWelcomeBackNotification
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
    public function handle(UserReturned $event): void
    {
        $user = $event->user;

        // Send welcome back notification to user
        $user->notify(new WelcomeBackNotification($user, $event->lastLoginDate));
    }
} 