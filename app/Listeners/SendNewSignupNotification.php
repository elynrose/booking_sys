<?php

namespace App\Listeners;

use App\Events\NewSignup;
use App\Notifications\NewSignupNotification;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendNewSignupNotification
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
    public function handle(NewSignup $event): void
    {
        $user = $event->user;

        // Send welcome notification to new user
        $user->notify(new NewSignupNotification($user));

        // Notify admins about new signup
        $admins = User::role('Admin')->get();
        foreach ($admins as $admin) {
            $admin->notify(new NewSignupNotification($user));
        }
    }
} 