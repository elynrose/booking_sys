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

        // Check if schedule is available
        if (!$schedule->isAvailable()) {
            return redirect()->route('frontend.schedules.show', $schedule)
                ->with('error', 'This schedule is not available for booking.');
        }

        // Check if user already has a booking for this schedule
        $existingBooking = Booking::where('user_id', auth()->id())
            ->where('schedule_id', $schedule->id)
            ->first();

        if ($existingBooking) {
            return redirect()->route('frontend.schedules.show', $schedule)
                ->with('error', 'You already have a booking for this schedule.');
        }

        return view('frontend.bookings.create', compact('schedule'));
    }

    public function store(Request $request, Schedule $schedule)
    {
        abort_if(Gate::denies('booking_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        // Check if schedule is available
        if (!$schedule->isAvailable()) {
            return redirect()->route('frontend.schedules.show', $schedule)
                ->with('error', 'This schedule is not available for booking.');
        }

        // Check if user already has a booking for this schedule
        $existingBooking = Booking::where('user_id', auth()->id())
            ->where('schedule_id', $schedule->id)
            ->first();

        if ($existingBooking) {
            return redirect()->route('frontend.schedules.show', $schedule)
                ->with('error', 'You already have a booking for this schedule.');
        }

        $booking = Booking::create([
            'user_id' => auth()->id(),
            'schedule_id' => $schedule->id,
            'status' => 'pending',
            'payment_status' => 'pending',
        ]);

        return redirect()->route('frontend.bookings.show', $booking)
            ->with('success', 'Booking created successfully. Please complete the payment to confirm your booking.');
    }

    public function show(Booking $booking)
    {
        abort_if(Gate::denies('booking_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        // Check if user owns this booking
        if ($booking->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        $booking->load(['schedule.trainer.user', 'payments']);

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
            return redirect()->route('frontend.bookings.show', $booking)
                ->with('error', 'This booking cannot be cancelled.');
        }

        $booking->update([
            'status' => 'cancelled',
        ]);

        return redirect()->route('frontend.bookings.show', $booking)
            ->with('success', 'Booking cancelled successfully.');
    }
} 