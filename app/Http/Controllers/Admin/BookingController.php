<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Schedule;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Gate;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Payment;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        abort_if(Gate::denies('booking_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $query = Booking::with(['user', 'schedule.trainer.user']);

        // Apply date filters if provided
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        // Apply trainer filter if provided
        if ($request->filled('trainer_id')) {
            $query->whereHas('schedule.trainer', function ($q) use ($request) {
                $q->where('id', $request->trainer_id);
            });
        }

        // Apply status filter if provided
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $bookings = $query->latest()->paginate(10);

        // Stat cards
        $totalBookings = Booking::count();
        $confirmedBookings = Booking::where('status', 'confirmed')->count();
        $unpaidBookings = Booking::where('status', 'unpaid')->count();
        $cancelledBookings = Booking::where('status', 'cancelled')->count();

        // Get trainers for filter
        $trainers = \App\Models\Trainer::with('user')->where('is_active', true)->get();

        return view('admin.bookings.index', compact(
            'bookings',
            'totalBookings',
            'confirmedBookings',
            'unpaidBookings',
            'cancelledBookings',
            'trainers'
        ));
    }

    public function create()
    {
        abort_if(Gate::denies('booking_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $schedules = Schedule::with(['trainer.user'])->where('status', 'active')->get();
        $users = User::all();
        return view('admin.bookings.create', compact('schedules', 'users'));
    }

    public function store(Request $request)
    {
        abort_if(Gate::denies('booking_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'schedule_id' => 'required|exists:schedules,id',
            'status' => 'required|in:pending,confirmed,cancelled,unpaid',
            'payment_status' => 'required|in:pending,paid,refunded',
            'notes' => 'nullable|string',
        ]);

        $validated['is_paid'] = $request->has('is_paid');

        $booking = Booking::create($validated);

        // Increment current_participants for the schedule
        $schedule = Schedule::find($validated['schedule_id']);
        $schedule->incrementParticipants();

        return redirect()->route('admin.bookings.index')
            ->with('success', 'Booking created successfully.');
    }

    public function edit(Booking $booking)
    {
        abort_if(Gate::denies('booking_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $schedules = Schedule::with(['trainer.user'])->where('status', 'active')->get();
        $users = User::all();
        return view('admin.bookings.edit', compact('booking', 'schedules', 'users'));
    }

    public function update(Request $request, Booking $booking)
    {
        abort_if(Gate::denies('booking_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'schedule_id' => 'required|exists:schedules,id',
            'status' => 'required|in:pending,confirmed,cancelled,unpaid',
            'payment_status' => 'required|in:pending,paid,refunded',
            'notes' => 'nullable|string',
        ]);

        $validated['is_paid'] = $request->has('is_paid');

        $booking->update($validated);

        // Check if payment record exists
        $payment = Payment::where('booking_id', $booking->id)->first();
        if ($payment) {
            $payment->update([
                'status' => 'paid',
            ]);
        } else {
            Payment::create([
                'booking_id' => $booking->id,
                'amount' => $booking->total_cost,
                'payment_method' => 'card',
                'description' => 'Payment for booking',
                'status' => 'paid',
                'payment_intent_id' => $booking->payment_intent_id,
                'user_id' => $booking->user_id,
            ]);
        }

        // If refunded, update the payment status to refunded
        if ($request->payment_status == 'refunded') {
            $payment->update([
                'status' => 'refunded',
            ]);
        } else if ($request->payment_status == 'pending') {
            $payment->update([
                'status' => 'pending',
            ]);
        }

        // Decrement current_participants for the schedule if the booking is cancelled or refunded
        if ($request->status == 'cancelled' || $request->payment_status == 'refunded') {
            $schedule = Schedule::find($validated['schedule_id']);
            $schedule->decrementParticipants();
        }

        return redirect()->route('admin.bookings.index')
            ->with('success', 'Booking updated successfully.');
    }

    public function show(Booking $booking)
    {
        abort_if(Gate::denies('booking_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.bookings.show', compact('booking'));
    }

    public function destroy(Booking $booking)
    {
        abort_if(Gate::denies('booking_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $booking->delete();

        return redirect()->route('admin.bookings.index')
            ->with('success', 'Booking deleted successfully.');
    }

    public function markAsPaid(Booking $booking)
    {
        abort_if(Gate::denies('booking_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $booking->update([
            'status' => 'confirmed',
            'payment_status' => 'paid',
            'is_paid' => true
        ]);

        //Check if payment record exists
        $payment = Payment::where('booking_id', $booking->id)->first();
        if ($payment) {
            $payment->update([
                'status' => 'paid',
            ]);
        } else {
            Payment::create([
                'booking_id' => $booking->id,
                'amount' => $booking->total_cost,
                'payment_method' => 'card',
                'description' => 'Payment for booking',
                'status' => 'paid',
                'payment_intent_id' => $booking->payment_intent_id,
                'user_id' => $booking->user_id,
            ]);
        }

        return redirect()->route('admin.bookings.index')
            ->with('success', 'Booking marked as paid successfully.');
    }

    public function incrementParticipants()
    {
        $this->increment('current_participants');
    }

    public function decrementParticipants()
    {
        $this->decrement('current_participants');
    }
} 