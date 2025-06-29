<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Booking;
use Illuminate\Http\Request;
use Gate;
use Symfony\Component\HttpFoundation\Response;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        abort_if(Gate::denies('payment_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $query = Payment::with(['booking.user', 'booking.schedule.trainer.user']);

        // Apply date filters if provided
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        // Apply status filter if provided
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $payments = $query->latest()->paginate(10);

        // Get statistics
        $statQuery = Payment::query();
        $totalPayments = $statQuery->count();
        $totalAmount = $statQuery->sum('amount');
        $pendingPayments = $statQuery->where('status', 'pending')->count();
        $pendingAmount = $statQuery->where('status', 'pending')->sum('amount');
        $completedPayments = $statQuery->where('status', 'completed')->count();
        $refundedAmount = $statQuery->where('status', 'refunded')->sum('amount');
        $failedPayments = $statQuery->where('status', 'failed')->count();

        return view('admin.payments.index', compact(
            'payments',
            'totalPayments',
            'totalAmount',
            'pendingPayments',
            'pendingAmount',
            'completedPayments',
            'refundedAmount',
            'failedPayments'
        ));
    }

    public function create()
    {
        abort_if(Gate::denies('payment_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $bookings = Booking::with(['user', 'schedule.trainer.user'])
            ->where('status', 'confirmed')
            ->where('payment_status', 'pending')
            ->get();
        return view('admin.payments.create', compact('bookings'));
    }

    public function store(Request $request)
    {
        abort_if(Gate::denies('payment_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $validated = $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|in:zelle,cash,credit_card',
            'status' => 'required|in:pending,paid,refunded',
            'notes' => 'nullable|string',
        ]);

        Payment::create($validated);

        return redirect()->route('admin.payments.index')
            ->with('success', 'Payment created successfully.');
    }

    public function edit(Payment $payment)
    {
        abort_if(Gate::denies('payment_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $bookings = Booking::with(['user', 'schedule.trainer.user'])
            ->where('status', 'confirmed')
            ->where('payment_status', 'pending')
            ->get();
        return view('admin.payments.edit', compact('payment', 'bookings'));
    }

    public function update(Request $request, Payment $payment)
    {
        abort_if(Gate::denies('payment_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $validated = $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|in:zelle,cash,credit_card',
            'status' => 'required|in:pending,paid,refunded',
            'notes' => 'nullable|string',
        ]);

        $oldStatus = $payment->status;
        $newStatus = $validated['status'];

        $payment->update($validated);

        // If status changed to 'paid', trigger the same logic as Stripe payment confirmation
        if ($oldStatus !== 'paid' && $newStatus === 'paid') {
            try {
                \DB::beginTransaction();

                $booking = Booking::find($validated['booking_id']);
                
                // Update booking payment status
                $booking->update([
                    'payment_status' => 'paid',
                    'is_paid' => true
                ]);

                \Log::info('Admin updated payment status to paid', [
                    'payment_id' => $payment->id,
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
                    \Log::info('Attempting to send payment confirmation email for admin-updated payment');
                    $booking->user->notify(new \App\Notifications\PaymentConfirmedNotification($payment));
                    \Log::info('Payment confirmation email sent successfully for admin-updated payment');
                    
                    // Notify other admins about the payment (excluding the current admin)
                    \Log::info('Attempting to send admin notifications for admin-updated payment');
                    $admins = \App\Models\User::whereHas('roles', function($query) {
                        $query->where('title', 'Admin');
                    })->where('id', '!=', auth()->id())->get();
                    
                    \Log::info('Found admins to notify for admin-updated payment', ['admin_count' => $admins->count()]);
                    
                    foreach ($admins as $admin) {
                        $admin->notify(new \App\Notifications\AdminPaymentReceivedNotification($payment));
                    }
                    \Log::info('Admin notifications sent successfully for admin-updated payment');
                } catch (\Exception $e) {
                    // Log email error but don't fail the payment
                    \Log::error('Failed to send payment confirmation email for admin-updated payment', ['error' => $e->getMessage()]);
                }

                \DB::commit();

                \Log::info('Admin payment status update completed successfully', [
                    'payment_id' => $payment->id,
                    'booking_id' => $booking->id
                ]);

            } catch (\Exception $e) {
                \DB::rollBack();
                \Log::error('Error updating payment status to paid by admin', [
                    'payment_id' => $payment->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return redirect()->route('admin.payments.index')
            ->with('success', 'Payment updated successfully.');
    }

    public function show(Payment $payment)
    {
        abort_if(Gate::denies('payment_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.payments.show', compact('payment'));
    }

    public function destroy(Payment $payment)
    {
        abort_if(Gate::denies('payment_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $payment->delete();

        return redirect()->route('admin.payments.index')
            ->with('success', 'Payment deleted successfully.');
    }
} 