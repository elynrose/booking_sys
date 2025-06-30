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

        $query = Booking::with(['user', 'schedule' => function($q) {
            $q->withTrashed()->with('trainer.user');
        }]);

        // Only show deleted bookings if specifically requested
        if ($request->boolean('show_deleted')) {
            $query->whereHas('schedule', function($q) {
                $q->withTrashed();
            });
        } else {
            $query->whereHas('schedule', function($q) {
                $q->whereNull('deleted_at'); // Only show bookings with non-deleted schedules
            });
        }

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

        $schedules = Schedule::with(['trainer.user'])->where('status', '=', 'active')->get();
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

        $schedules = Schedule::with(['trainer.user'])->where('status', '=', 'active')->get();
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

        $oldPaymentStatus = $booking->payment_status;
        $newPaymentStatus = $validated['payment_status'];

        $booking->update($validated);

        // Check if payment record exists
        $payment = Payment::where('booking_id', $booking->id)->first();
        if ($payment) {
            $payment->update([
                'status' => $newPaymentStatus === 'paid' ? 'paid' : $newPaymentStatus,
            ]);
        } else {
            $payment = Payment::create([
                'booking_id' => $booking->id,
                'amount' => $booking->total_cost,
                'payment_method' => 'card',
                'description' => 'Payment for booking',
                'status' => $newPaymentStatus === 'paid' ? 'paid' : $newPaymentStatus,
                'payment_intent_id' => $booking->payment_intent_id,
                'user_id' => $booking->user_id,
            ]);
        }

        // If payment status changed to 'paid', trigger the same logic as Stripe payment confirmation
        if ($oldPaymentStatus !== 'paid' && $newPaymentStatus === 'paid') {
            try {
                \DB::beginTransaction();

                // Update booking payment status to completed
                $booking->update([
                    'payment_status' => 'paid',
                    'is_paid' => true
                ]);

                \Log::info('Admin updated booking payment status to paid', [
                    'booking_id' => $booking->id,
                    'admin_user_id' => auth()->id()
                ]);

                // Update payment record with additional info
                $payment->update([
                    'status' => 'paid',
                    'paid_at' => now()
                ]);

                // Send payment confirmation email
                try {
                    \Log::info('Attempting to send payment confirmation email for admin-updated booking payment');
                    $booking->user->notify(new \App\Notifications\PaymentConfirmedNotification($payment));
                    \Log::info('Payment confirmation email sent successfully for admin-updated booking payment');
                    
                    // Notify other admins about the payment (excluding the current admin)
                    \Log::info('Attempting to send admin notifications for admin-updated booking payment');
                    $admins = \App\Models\User::whereHas('roles', function($query) {
                        $query->where('title', 'Admin');
                    })->where('id', '!=', auth()->id())->get();
                    
                    \Log::info('Found admins to notify for admin-updated booking payment', ['admin_count' => $admins->count()]);
                    
                    foreach ($admins as $admin) {
                        $admin->notify(new \App\Notifications\AdminPaymentReceivedNotification($payment));
                    }
                    \Log::info('Admin notifications sent successfully for admin-updated booking payment');
                } catch (\Exception $e) {
                    // Log email error but don't fail the payment
                    \Log::error('Failed to send payment confirmation email for admin-updated booking payment', ['error' => $e->getMessage()]);
                }

                \DB::commit();

                \Log::info('Admin booking payment status update completed successfully', [
                    'booking_id' => $booking->id,
                    'payment_id' => $payment->id
                ]);

            } catch (\Exception $e) {
                \DB::rollBack();
                \Log::error('Error updating booking payment status to paid by admin', [
                    'booking_id' => $booking->id,
                    'error' => $e->getMessage()
                ]);
            }
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

        try {
            \DB::beginTransaction();

            // Update booking payment status
            $booking->update([
                'status' => 'confirmed',
                'payment_status' => 'paid',
                'is_paid' => true
            ]);

            \Log::info('Admin marked booking as paid', [
                'booking_id' => $booking->id,
                'admin_user_id' => auth()->id()
            ]);

            // Update or create payment record
            $payment = Payment::where('booking_id', $booking->id)
                ->where('status', 'pending')
                ->latest()
                ->first();

            if (!$payment) {
                // Create payment record if it doesn't exist
                $payment = Payment::create([
                    'user_id' => $booking->user_id,
                    'booking_id' => $booking->id,
                    'schedule_id' => $booking->schedule_id,
                    'amount' => $booking->schedule->price,
                    'payment_method' => 'admin_marked',
                    'status' => 'paid',
                    'description' => 'Payment for ' . $booking->schedule->title . ' - ' . $booking->child->name,
                    'paid_at' => now()
                ]);

                \Log::info('New payment record created by admin', ['payment_id' => $payment->id]);
            } else {
                $payment->update([
                    'status' => 'paid',
                    'payment_method' => 'admin_marked',
                    'paid_at' => now()
                ]);

                \Log::info('Existing payment record updated by admin', ['payment_id' => $payment->id]);
            }

            // Send payment confirmation email
            try {
                \Log::info('Attempting to send payment confirmation email for admin-marked payment');
                $booking->user->notify(new \App\Notifications\PaymentConfirmedNotification($payment));
                \Log::info('Payment confirmation email sent successfully for admin-marked payment');
                
                // Notify admins about the payment (excluding the current admin to avoid duplicate notifications)
                \Log::info('Attempting to send admin notifications for admin-marked payment');
                $admins = \App\Models\User::whereHas('roles', function($query) {
                    $query->where('title', 'Admin');
                })->where('id', '!=', auth()->id())->get();
                
                \Log::info('Found admins to notify for admin-marked payment', ['admin_count' => $admins->count()]);
                
                foreach ($admins as $admin) {
                    $admin->notify(new \App\Notifications\AdminPaymentReceivedNotification($payment));
                }
                \Log::info('Admin notifications sent successfully for admin-marked payment');
            } catch (\Exception $e) {
                // Log email error but don't fail the payment
                \Log::error('Failed to send payment confirmation email for admin-marked payment', ['error' => $e->getMessage()]);
            }

            \DB::commit();

            \Log::info('Admin payment marking completed successfully', [
                'booking_id' => $booking->id,
                'payment_id' => $payment->id
            ]);

            return redirect()->route('admin.bookings.index')
                ->with('success', 'Booking marked as paid successfully. Payment confirmation emails have been sent.');

        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Error marking booking as paid by admin', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->route('admin.bookings.index')
                ->with('error', 'Failed to mark booking as paid. Please try again.');
        }
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