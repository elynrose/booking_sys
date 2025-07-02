<?php

namespace App\Services;

use App\Models\Checkin;
use App\Models\Booking;
use App\Models\User;
use App\Notifications\CheckinNotification;
use App\Notifications\CheckoutNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;
use Carbon\Carbon;

class CheckinService
{
    /**
     * Process check-in
     */
    public function checkin(Booking $booking, User $user): Checkin
    {
        try {
            DB::beginTransaction();
            
            // Verify booking belongs to user or user is admin/trainer
            if ($booking->user_id !== $user->id && !$user->hasAnyRole(['Admin', 'Trainer'])) {
                throw new Exception('Unauthorized to check in for this booking.');
            }
            
            // Check if already checked in
            if ($this->isCheckedIn($booking)) {
                throw new Exception('Already checked in for this booking.');
            }
            
            // Verify booking is confirmed
            if ($booking->status !== 'confirmed') {
                throw new Exception('Booking must be confirmed to check in.');
            }
            
            // Create check-in record
            $checkin = Checkin::create([
                'booking_id' => $booking->id,
                'user_id' => $user->id,
                'checkin_time' => now(),
                'checkout_time' => null,
                'status' => 'checked_in',
            ]);
            
            // Update booking status
            $booking->update(['status' => 'in_progress']);
            
            // Send notification
            $booking->user->notify(new CheckinNotification($checkin));
            
            DB::commit();
            
            Log::info('Check-in successful', [
                'checkin_id' => $checkin->id,
                'booking_id' => $booking->id,
                'user_id' => $user->id
            ]);
            
            return $checkin;
            
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Check-in failed', [
                'booking_id' => $booking->id,
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
    
    /**
     * Process check-out
     */
    public function checkout(Booking $booking, User $user): bool
    {
        try {
            DB::beginTransaction();
            
            // Verify booking belongs to user or user is admin/trainer
            if ($booking->user_id !== $user->id && !$user->hasAnyRole(['Admin', 'Trainer'])) {
                throw new Exception('Unauthorized to check out for this booking.');
            }
            
            // Get active check-in
            $checkin = $this->getActiveCheckin($booking);
            if (!$checkin) {
                throw new Exception('No active check-in found for this booking.');
            }
            
            // Update check-in record
            $checkin->update([
                'checkout_time' => now(),
                'status' => 'checked_out',
            ]);
            
            // Update booking status
            $booking->update(['status' => 'completed']);
            
            // Send notification
            $booking->user->notify(new CheckoutNotification($checkin));
            
            DB::commit();
            
            Log::info('Check-out successful', [
                'checkin_id' => $checkin->id,
                'booking_id' => $booking->id,
                'user_id' => $user->id
            ]);
            
            return true;
            
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Check-out failed', [
                'booking_id' => $booking->id,
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
    
    /**
     * Auto check-out based on schedule end time
     */
    public function autoCheckout(Booking $booking): bool
    {
        try {
            $checkin = $this->getActiveCheckin($booking);
            if (!$checkin) {
                return false;
            }
            
            $schedule = $booking->schedule;
            $endTime = Carbon::parse($schedule->end_time);
            
            // Check if class has ended
            if (now()->gt($endTime)) {
                return $this->checkout($booking, $booking->user);
            }
            
            return false;
            
        } catch (Exception $e) {
            Log::error('Auto check-out failed', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * Check if user is checked in for a booking
     */
    public function isCheckedIn(Booking $booking): bool
    {
        return Checkin::where('booking_id', $booking->id)
            ->where('status', 'checked_in')
            ->exists();
    }
    
    /**
     * Get active check-in for a booking
     */
    public function getActiveCheckin(Booking $booking): ?Checkin
    {
        return Checkin::where('booking_id', $booking->id)
            ->where('status', 'checked_in')
            ->first();
    }
    
    /**
     * Get check-in statistics
     */
    public function getCheckinStats(): array
    {
        return [
            'total_checkins' => Checkin::count(),
            'active_checkins' => Checkin::where('status', 'checked_in')->count(),
            'completed_checkins' => Checkin::where('status', 'checked_out')->count(),
            'today_checkins' => Checkin::whereDate('checkin_time', today())->count(),
        ];
    }
    
    /**
     * Get session duration for a check-in
     */
    public function getSessionDuration(Checkin $checkin): ?int
    {
        if (!$checkin->checkout_time) {
            return null;
        }
        
        return Carbon::parse($checkin->checkin_time)
            ->diffInMinutes($checkin->checkout_time);
    }
    
    /**
     * Process auto check-outs for all active sessions
     */
    public function processAutoCheckouts(): int
    {
        $activeCheckins = Checkin::where('status', 'checked_in')
            ->with(['booking.schedule'])
            ->get();
        
        $processedCount = 0;
        
        foreach ($activeCheckins as $checkin) {
            if ($this->autoCheckout($checkin->booking)) {
                $processedCount++;
            }
        }
        
        Log::info('Auto check-out processing completed', [
            'processed_count' => $processedCount,
            'total_active' => $activeCheckins->count()
        ]);
        
        return $processedCount;
    }
} 