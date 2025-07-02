<?php

namespace App\Listeners;

use App\Events\DataChanged;
use App\Notifications\DataChangeEmailNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendDataChangeEmailNotification
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
    public function handle(DataChanged $event): void
    {
        $user = $event->user;

        // Send data change notification to user
        $user->notify(new DataChangeEmailNotification($user, $event->changedFields, $event->oldData));
    }
} 