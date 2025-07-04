<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\Checkin;
use App\Models\Waitlist;
use Gate;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{
    public function index()
    {
        abort_if(Gate::denies('booking_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $bookings = Booking::with(['schedule.trainer.user', 'child'])
            ->where('user_id', auth()->id())
            ->latest()
            ->paginate(10);

        return view('frontend.bookings.index', compact('bookings'));
    }

    public function create(Schedule $schedule)
    {
        abort_if(Gate::denies('booking_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        // Debug information
        \Log::info('Attempting to create booking for Schedule #' . $schedule->id, [
            'schedule' => $schedule->toArray(),
            'availability_status' => $schedule->getAvailabilityStatus()
        ]);

        // Check if schedule is available
        if (!$schedule->isAvailable()) {
            $status = $schedule->getAvailabilityStatus();
            $errorMessage = 'This schedule is not available for booking because: ';
            
            if (!$status['is_active']) {
                $errorMessage .= 'The class is not active (Status: ' . $schedule->status . '). ';
            }
            if (!$status['is_available_for_booking']) {
                $errorMessage .= 'The class has ended (End date: ' . $status['end_date'] . '). ';
            }
            if (!$status['has_spots_available']) {
                $errorMessage .= 'The class is full (Max participants: ' . $status['max_participants'] . 
                                ', Active bookings: ' . $status['active_bookings'] . '). ';
            }
            
            \Log::info('Schedule #' . $schedule->id . ' is not available', [
                'error_message' => $errorMessage,
                'status' => $status
            ]);
            
            return redirect()->route('bookings.index')
                ->with('error', $errorMessage);
        }

        // Check if user already has a booking for this schedule
        $existingBooking = Booking::where('user_id', auth()->id())
            ->where('schedule_id', $schedule->id)
            ->first();

        if ($existingBooking) {
            return redirect()->route('bookings.index')
                ->with('error', 'You already have a booking for this schedule (Booking ID: ' . $existingBooking->id . ').');
        }

        // Get the authenticated user's children
        $children = auth()->user()->children;
        $totalDays = $schedule->start_date->diffInDays($schedule->end_date) + 1;

        return view('frontend.bookings.create', compact('schedule', 'children', 'totalDays'));
    }

    public function store(Request $request, Schedule $schedule)
    {
        \Log::info('Attempting to create booking for Schedule #' . $schedule->id, [
            'user_id' => auth()->id(),
            'schedule_id' => $schedule->id,
            'schedule_details' => [
                'title' => $schedule->title,
                'start_date' => $schedule->start_date,
                'end_date' => $schedule->end_date,
                'max_participants' => $schedule->max_participants,
                'active_bookings' => $schedule->bookings()->where('status', '!=', 'cancelled')->count()
            ]
        ]);

        // Check if schedule is available
        if (!$schedule->isAvailable()) {
            $availabilityStatus = $schedule->getAvailabilityStatus();
            $errorMessage = 'This schedule is not available for booking because: ';
            
            if (!$availabilityStatus['is_active']) {
                $errorMessage .= 'The class is not active (Status: ' . $schedule->status . '). ';
            }
            if (!$availabilityStatus['is_available_for_booking']) {
                $errorMessage .= 'The class has ended (End date: ' . $availabilityStatus['end_date'] . '). ';
            }
            if (!$availabilityStatus['has_spots_available']) {
                $errorMessage .= 'The class is full (Max participants: ' . $availabilityStatus['max_participants'] . 
                                ', Active bookings: ' . $availabilityStatus['active_bookings'] . '). ';
            }
            
            \Log::info('Schedule #' . $schedule->id . ' is not available:', $availabilityStatus);
            
            return redirect()->route('bookings.index')
                ->with('error', $errorMessage);
        }

        // Check if user already has a booking
        $existingBooking = auth()->user()->bookings()
            ->where('schedule_id', $schedule->id)
            ->where('status', '!=', 'cancelled')
            ->first();

        if ($existingBooking) {
            \Log::info('User already has an active booking for Schedule #' . $schedule->id, [
                'user_id' => auth()->id(),
                'booking_id' => $existingBooking->id,
                'booking_status' => $existingBooking->status
            ]);
            
            return redirect()->route('bookings.index')
                ->with('error', 'You already have a booking for this schedule.');
        }

        try {
            DB::beginTransaction();

            $booking = auth()->user()->bookings()->create([
                'schedule_id' => $schedule->id,
                'child_id' => $request->child_id,
                'status' => 'pending',
                'sessions_remaining' => $request->sessions,
                'total_cost' => $schedule->current_price * $request->sessions
            ]);
         
            DB::commit();

            return redirect()->route('bookings.show', $booking)
                ->with('success', 'Booking created successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Failed to create booking:', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'schedule_id' => $schedule->id
            ]);
            
            return redirect()->route('bookings.index')
                ->with('error', 'Failed to create booking. Please try again.');
        }
    }

    public function show(Booking $booking)
    {
        abort_if(Gate::denies('booking_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        // Check if user owns this booking
        if ($booking->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        $booking->load([
            'schedule.trainer.user', 
            'payments',
            'child',
            'checkins' => function($query) {
                $query->orderBy('created_at', 'desc');
            }
        ]);

        return view('frontend.bookings.show', compact('booking'));
    }

    public function cancel(Booking $booking)
    {
        abort_if(Gate::denies('booking_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        // Check if user owns this booking
        if ($booking->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        // Check if booking can be cancelled
        if (!$booking->canBeCancelled()) {
            return redirect()->route('bookings.show', $booking)
                ->with('error', 'This booking cannot be cancelled.');
        }

        $booking->update([
            'status' => 'cancelled',
        ]);

        return redirect()->route('bookings.show', $booking)
            ->with('success', 'Booking cancelled successfully.');
    }

    public function destroy(Booking $booking)
    {
        abort_if(Gate::denies('booking_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        // Check if user owns this booking
        if ($booking->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        // Check if booking can be deleted
        if (!$booking->canBeDeleted()) {
            return redirect()->route('bookings.show', $booking)
                ->with('error', 'This booking cannot be deleted.');
        }

        try {
            DB::beginTransaction();

            // Delete any associated check-ins
            $booking->checkins()->delete();

            // Delete the booking
            $booking->delete();

            DB::commit();

            return redirect()->route('bookings.index')
                ->with('success', 'Booking deleted successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Failed to delete booking:', [
                'error' => $e->getMessage(),
                'booking_id' => $booking->id,
                'user_id' => auth()->id()
            ]);

            return redirect()->route('bookings.show', $booking)
                ->with('error', 'Failed to delete booking. Please try again.');
        }
    }
} 