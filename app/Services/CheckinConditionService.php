<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\User;
use App\Models\Checkin;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CheckinConditionService
{
    /**
     * Validate check-in conditions for a booking based on schedule type
     */
    public function validateCheckinConditions(Booking $booking, User $user): array
    {
        $scheduleType = $booking->schedule->scheduleType;
        $errors = [];
        
        // Get current time in site timezone
        $siteTimezone = \App\Models\SiteSettings::getTimezone();
        $currentTime = Carbon::now($siteTimezone);
        
        // 1. Check if already checked in today (based on schedule type rules)
        if (!$scheduleType->canCheckinMultipleTimesPerDay()) {
            if ($this->hasActiveCheckinToday($booking)) {
                $errors[] = 'Already checked in today for this class type.';
            }
        } else {
            // If multiple check-ins are allowed, check against the max limit
            // BUT only if unlimited check-ins are not allowed
            if (!$scheduleType->allowsUnlimitedCheckins()) {
                $maxCheckins = $scheduleType->max_checkins_per_day;
                $todayCheckins = $this->getTodayCheckinsCount($booking);
                
                if ($todayCheckins >= $maxCheckins) {
                    $errors[] = "Maximum {$maxCheckins} check-ins per day reached for this class type.";
                }
            }
            // If unlimited check-ins are allowed, no limit check needed
        }

        // 2. Check trainer availability (if required)
        if ($scheduleType->requiresTrainerAvailability()) {
            if (!$booking->schedule->hasAvailableTrainer()) {
                $errors[] = 'No trainer available for this class type.';
            }
        }

        // 3. Check check-in window
        if ($scheduleType->getCheckinWindowMinutes() > 0) {
            if (!$this->isWithinCheckinWindow($booking, $scheduleType)) {
                $errors[] = 'Check-in window has passed for this class type.';
            }
        }

        // 4. Check late check-in policy
        if (!$scheduleType->isLateCheckinAllowed()) {
            if ($this->isLateCheckin($booking)) {
                $errors[] = 'Late check-ins not allowed for this class type.';
            }
        }

        // 5. Check session limits (for non-unlimited bookings)
        if (!$scheduleType->allowsUnlimitedCheckins() && !$booking->is_unlimited_group_class) {
            if ($booking->sessions_remaining <= 0) {
                $errors[] = 'No sessions remaining for this booking.';
            }
        }

        // 6. Check if class has started
        $scheduleStartTime = Carbon::parse($booking->schedule->start_time, $siteTimezone);
        if ($currentTime->lt($scheduleStartTime)) {
            $timeUntilStart = $currentTime->diffForHumans($scheduleStartTime, ['parts' => 2]);
            $errors[] = "Class hasn't started yet. {$timeUntilStart} until start.";
        }

        // 7. Check if booking is confirmed and paid
        if ($booking->status !== 'confirmed') {
            $errors[] = 'Booking must be confirmed to check in.';
        }

        if (!$booking->is_paid) {
            $errors[] = 'Booking must be paid to check in.';
        }

        return $errors;
    }

    /**
     * Check if booking has an active check-in today
     */
    private function hasActiveCheckinToday(Booking $booking): bool
    {
        return Checkin::where('booking_id', $booking->id)
            ->whereDate('created_at', Carbon::today())
            ->whereNull('checkout_time')
            ->exists();
    }

    /**
     * Get the count of check-ins for today
     */
    private function getTodayCheckinsCount(Booking $booking): int
    {
        return Checkin::where('booking_id', $booking->id)
            ->whereDate('created_at', Carbon::today())
            ->count();
    }

    /**
     * Check if current time is within the check-in window
     */
    private function isWithinCheckinWindow(Booking $booking, $scheduleType): bool
    {
        $siteTimezone = \App\Models\SiteSettings::getTimezone();
        $currentTime = Carbon::now($siteTimezone);
        $scheduleStartTime = Carbon::parse($booking->schedule->start_time, $siteTimezone);
        
        $windowMinutes = $scheduleType->getCheckinWindowMinutes();
        $windowStart = $scheduleStartTime->copy()->subMinutes($windowMinutes);
        $windowEnd = $scheduleStartTime->copy()->addMinutes($windowMinutes);
        
        return $currentTime->between($windowStart, $windowEnd);
    }

    /**
     * Check if this is a late check-in
     */
    private function isLateCheckin(Booking $booking): bool
    {
        $siteTimezone = \App\Models\SiteSettings::getTimezone();
        $currentTime = Carbon::now($siteTimezone);
        $scheduleStartTime = Carbon::parse($booking->schedule->start_time, $siteTimezone);
        
        return $currentTime->gt($scheduleStartTime);
    }

    /**
     * Get check-in rules description for a schedule type
     */
    public function getCheckinRulesDescription($scheduleType): string
    {
        return $scheduleType->getCheckinRulesDescription();
    }

    /**
     * Check if session tracking should be enabled for this booking
     */
    public function shouldTrackSessions(Booking $booking): bool
    {
        $scheduleType = $booking->schedule->scheduleType;
        
        // If schedule type doesn't track sessions, return false
        if (!$scheduleType->isSessionTrackingEnabled()) {
            return false;
        }
        
        // If booking is unlimited, don't track sessions
        if ($booking->is_unlimited_group_class) {
            return false;
        }
        
        // If schedule type allows unlimited check-ins, don't track sessions
        if ($scheduleType->allowsUnlimitedCheckins()) {
            return false;
        }
        
        return true;
    }

    /**
     * Check if auto-checkout should be enabled for this booking
     */
    public function shouldAutoCheckout(Booking $booking): bool
    {
        $scheduleType = $booking->schedule->scheduleType;
        return $scheduleType->isAutoCheckoutEnabled();
    }

    /**
     * Get the maximum number of check-ins allowed per day for this booking
     */
    public function getMaxCheckinsPerDay(Booking $booking): int
    {
        $scheduleType = $booking->schedule->scheduleType;
        return $scheduleType->max_checkins_per_day;
    }
} 