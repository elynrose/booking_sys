<?php

namespace App\Observers;

use App\Events\BookingCancelled;
use App\Events\BookingConfirmed;
use App\Events\BookingCreated;
use App\Models\Booking;

class BookingObserver
{
    /**
     * Handle the Booking "created" event.
     */
    public function created(Booking $booking): void
    {
        // Fire booking created event
        event(new BookingCreated($booking));
    }

    /**
     * Handle the Booking "updated" event.
     */
    public function updated(Booking $booking): void
    {
        // Check if status changed to confirmed
        if ($booking->wasChanged('status') && $booking->status === 'confirmed') {
            event(new BookingConfirmed($booking));
        }
        
        // Check if status changed to cancelled
        if ($booking->wasChanged('status') && $booking->status === 'cancelled') {
            event(new BookingCancelled($booking));
        }
    }

    /**
     * Handle the Booking "deleted" event.
     */
    public function deleted(Booking $booking): void
    {
        // Handle booking deletion if needed
    }

    /**
     * Handle the Booking "restored" event.
     */
    public function restored(Booking $booking): void
    {
        // Handle booking restoration if needed
    }

    /**
     * Handle the Booking "force deleted" event.
     */
    public function forceDeleted(Booking $booking): void
    {
        // Handle force deletion if needed
    }
} 