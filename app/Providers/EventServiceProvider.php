<?php

namespace App\Providers;

use App\Events\BookingCancelled;
use App\Events\BookingConfirmed;
use App\Events\BookingCreated;
use App\Events\BookingReminder;
use App\Events\DataChanged;
use App\Events\NewSignup;
use App\Events\PaymentFailed;
use App\Events\PaymentReceived;
use App\Events\PaymentRefunded;
use App\Events\PaymentReminder;
use App\Events\ScheduleCancelled;
use App\Events\ScheduleRescheduled;
use App\Events\SessionCompleted;
use App\Events\SpotAvailable;
use App\Events\TwoFactorCodeSent;
use App\Events\UserReturned;
use App\Events\UserVerified;
use App\Listeners\SendBookingCancelledNotification;
use App\Listeners\SendBookingConfirmedNotification;
use App\Listeners\SendBookingCreatedNotification;
use App\Listeners\SendBookingReminderNotification;
use App\Listeners\SendDataChangeEmailNotification;
use App\Listeners\SendNewSignupNotification;
use App\Listeners\SendPaymentFailedNotification;
use App\Listeners\SendPaymentReceivedNotification;
use App\Listeners\SendPaymentRefundedNotification;
use App\Listeners\SendPaymentReminderNotification;
use App\Listeners\SendScheduleCancelledNotification;
use App\Listeners\SendScheduleRescheduledNotification;
use App\Listeners\SendSessionCompletedNotification;
use App\Listeners\SendSpotAvailableNotification;
use App\Listeners\SendTwoFactorCodeNotification;
use App\Listeners\SendVerifyUserNotification;
use App\Listeners\SendWelcomeBackNotification;
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
        // BookingCreated::class => [
        //     SendBookingCreatedNotification::class,
        // ],
        BookingCancelled::class => [
            SendBookingCancelledNotification::class,
        ],
        BookingConfirmed::class => [
            SendBookingConfirmedNotification::class,
        ],
        BookingReminder::class => [
            SendBookingReminderNotification::class,
        ],
        
        // Payment Events
        PaymentReceived::class => [
            SendPaymentReceivedNotification::class,
        ],
        PaymentRefunded::class => [
            SendPaymentRefundedNotification::class,
        ],
        PaymentFailed::class => [
            SendPaymentFailedNotification::class,
        ],
        PaymentReminder::class => [
            SendPaymentReminderNotification::class,
        ],
        
        // Schedule Events
        ScheduleCancelled::class => [
            SendScheduleCancelledNotification::class,
        ],
        ScheduleRescheduled::class => [
            SendScheduleRescheduledNotification::class,
        ],
        SpotAvailable::class => [
            SendSpotAvailableNotification::class,
        ],
        
        // Session Events
        SessionCompleted::class => [
            SendSessionCompletedNotification::class,
        ],
        
        // User Events
        // 'App\\Events\\NewSignup' => [
        //     'App\\Listeners\\SendNewSignupNotification',
        // ],
        UserVerified::class => [
            SendVerifyUserNotification::class,
        ],
        UserReturned::class => [
            SendWelcomeBackNotification::class,
        ],
        DataChanged::class => [
            SendDataChangeEmailNotification::class,
        ],
        TwoFactorCodeSent::class => [
            SendTwoFactorCodeNotification::class,
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
