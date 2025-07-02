<?php

namespace App\Providers;

use App\Events\BookingCancelled;
use App\Events\BookingConfirmed;
use App\Events\BookingCreated;
use App\Events\PaymentReceived;
use App\Events\PaymentRefunded;
use App\Events\ScheduleCancelled;
use App\Events\ScheduleRescheduled;
use App\Events\SessionCompleted;
use App\Listeners\SendBookingCancelledNotification;
use App\Listeners\SendBookingConfirmedNotification;
use App\Listeners\SendBookingCreatedNotification;
use App\Listeners\SendPaymentReceivedNotification;
use App\Listeners\SendPaymentRefundedNotification;
use App\Listeners\SendScheduleCancelledNotification;
use App\Listeners\SendScheduleRescheduledNotification;
use App\Listeners\SendSessionCompletedNotification;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        
        // Booking Events
        BookingCreated::class => [
            SendBookingCreatedNotification::class,
        ],
        BookingCancelled::class => [
            SendBookingCancelledNotification::class,
        ],
        BookingConfirmed::class => [
            SendBookingConfirmedNotification::class,
        ],
        
        // Payment Events
        PaymentReceived::class => [
            SendPaymentReceivedNotification::class,
        ],
        PaymentRefunded::class => [
            SendPaymentRefundedNotification::class,
        ],
        
        // Schedule Events
        ScheduleCancelled::class => [
            SendScheduleCancelledNotification::class,
        ],
        ScheduleRescheduled::class => [
            SendScheduleRescheduledNotification::class,
        ],
        
        // Session Events
        SessionCompleted::class => [
            SendSessionCompletedNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
