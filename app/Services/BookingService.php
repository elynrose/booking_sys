<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Schedule;
use App\Models\User;
use App\Models\Child;
use App\Notifications\BookingConfirmedNotification;
use App\Notifications\BookingCancelledNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class BookingService
{
    /**
     * Create a new booking
     */
    public function createBooking(array $data, User $user): Booking
    {
        try {
            DB::beginTransaction();
            
            $schedule = Schedule::findOrFail($data['schedule_id']);
            
            // Check if user is already booked
            if ($this->isUserAlreadyBooked($user->id, $schedule->id)) {
                throw new Exception('User is already booked for this class.');
            }
            
            // Check capacity
            if ($schedule->current_participants >= $schedule->max_participants) {
                throw new Exception('Class is at maximum capacity.');
            }
            
            // Create booking
            $booking = Booking::create([
                'user_id' => $user->id,
                'schedule_id' => $schedule->id,
                'status' => 'pending',
                'payment_status' => 'pending',
                'notes' => $data['notes'] ?? null,
            ]);
            
            // Add children if specified
            if (!empty($data['children'])) {
                foreach ($data['children'] as $childId) {
                    $child = Child::where('id', $childId)
                        ->where('user_id', $user->id)
                        ->first();
                    
                    if ($child) {
                        $booking->children()->attach($child->id);
                    }
                }
            }
            
            // Update schedule participant count
            $schedule->increment('current_participants');
            
            // Send notification
            $user->notify(new BookingConfirmedNotification($booking));
            
            DB::commit();
            
            Log::info('Booking created successfully', [
                'booking_id' => $booking->id,
                'user_id' => $user->id,
                'schedule_id' => $schedule->id
            ]);
            
            return $booking;
            
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Booking creation failed', [
                'user_id' => $user->id,
                'schedule_id' => $data['schedule_id'] ?? null,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
    
    /**
     * Cancel a booking
     */
    public function cancelBooking(Booking $booking, User $user): bool
    {
        try {
            DB::beginTransaction();
            
            // Check if user can cancel this booking
            if ($booking->user_id !== $user->id && !$user->hasRole('Admin')) {
                throw new Exception('Unauthorized to cancel this booking.');
            }
            
            // Update booking status
            $booking->update(['status' => 'cancelled']);
            
            // Decrease schedule participant count
            $schedule = $booking->schedule;
            $schedule->decrement('current_participants');
            
            // Send notification
            $booking->user->notify(new BookingCancelledNotification($booking));
            
            DB::commit();
            
            Log::info('Booking cancelled successfully', [
                'booking_id' => $booking->id,
                'user_id' => $user->id
            ]);
            
            return true;
            
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Booking cancellation failed', [
                'booking_id' => $booking->id,
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
    
    /**
     * Check if user is already booked for a schedule
     */
    public function isUserAlreadyBooked(int $userId, int $scheduleId): bool
    {
        return Booking::where('user_id', $userId)
            ->where('schedule_id', $scheduleId)
            ->whereIn('status', ['pending', 'confirmed'])
            ->exists();
    }
    
    /**
     * Get user's active bookings
     */
    public function getUserActiveBookings(User $user): \Illuminate\Database\Eloquent\Collection
    {
        return $user->bookings()
            ->with(['schedule.trainer.user', 'children'])
            ->whereIn('status', ['pending', 'confirmed'])
            ->orderBy('created_at', 'desc')
            ->get();
    }
    
    /**
     * Get booking statistics
     */
    public function getBookingStats(): array
    {
        return [
            'total' => Booking::count(),
            'pending' => Booking::where('status', 'pending')->count(),
            'confirmed' => Booking::where('status', 'confirmed')->count(),
            'cancelled' => Booking::where('status', 'cancelled')->count(),
            'paid' => Booking::where('payment_status', 'paid')->count(),
            'unpaid' => Booking::where('payment_status', 'pending')->count(),
        ];
    }
} 