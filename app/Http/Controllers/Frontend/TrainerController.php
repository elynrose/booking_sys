<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Schedule;
use App\Models\Checkin;
use App\Models\Payment;
use App\Models\Booking;
use App\Models\Trainer;
use App\Notifications\PaymentConfirmedNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Gate;
use Symfony\Component\HttpFoundation\Response;

class TrainerController extends Controller
{
    public function index()
    {
        abort_if(Gate::denies('trainer_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $user = auth()->user();
        
        // Get the trainer record
        $trainer = Trainer::where('user_id', $user->id)->first();
        
        if (!$trainer) {
            return redirect()->route('frontend.home')
                ->with('error', 'Trainer profile not found.');
        }

        // Get today's schedules for the trainer
        $todaySchedules = Schedule::where('trainer_id', $trainer->id)
            ->whereDate('start_date', '<=', Carbon::today())
            ->whereDate('end_date', '>=', Carbon::today())
            ->where('status', 'active')
            ->with(['bookings' => function($query) {
                $query->where('status', 'confirmed')
                    ->where('is_paid', true);
            }])
            ->get();

        // Get upcoming schedules
        $upcomingSchedules = Schedule::where('trainer_id', $trainer->id)
            ->whereDate('start_date', '>', Carbon::today())
            ->where('status', 'active')
            ->with(['bookings' => function($query) {
                $query->where('status', 'confirmed')
                    ->where('is_paid', true);
            }])
            ->get();

        // Get today's check-ins
        $todayCheckins = Checkin::whereHas('booking.schedule', function($query) use ($trainer) {
                $query->where('trainer_id', $trainer->id);
            })
            ->whereDate('created_at', Carbon::today())
            ->with(['booking.child', 'booking.user'])
            ->get();

        // Get pending payments for trainer's schedules
        $pendingPayments = Payment::whereHas('booking.schedule', function($query) use ($trainer) {
                $query->where('trainer_id', $trainer->id);
            })
            ->where('status', 'pending')
            ->with(['booking.child', 'booking.user', 'booking.schedule'])
            ->get();

        return view('frontend.trainer.index', compact('todaySchedules', 'upcomingSchedules', 'todayCheckins', 'pendingPayments'));
    }

    public function confirmPayment(Request $request, Payment $payment)
    {
        abort_if(Gate::denies('trainer_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $trainer = Trainer::where('user_id', auth()->id())->first();
        
        if (!$trainer) {
            return redirect()->route('frontend.home')
                ->with('error', 'Trainer profile not found.');
        }

        // Verify that the payment belongs to one of the trainer's schedules
        if ($payment->booking->schedule->trainer_id !== $trainer->id) {
            abort(403, 'Unauthorized action.');
        }

        try {
            DB::beginTransaction();

            // Update payment
            $payment->update([
                'status' => 'paid',
                'description' => $request->description,
                'paid_at' => now(),
            ]);

            // Update booking with both status and payment_status
            $payment->booking->update([
                'status' => 'confirmed',
                'is_paid' => true,
                'payment_status' => 'confirmed',
                'payment_method' => 'zelle' // Since this is a manual confirmation
            ]);

            DB::commit();

            // Try to send notification, but don't fail if it doesn't work
            try {
                $payment->booking->user->notify(new PaymentConfirmedNotification($payment));
            } catch (\Exception $e) {
                \Log::warning('Failed to send payment confirmation notification: ' . $e->getMessage());
            }

            return redirect()->route('frontend.trainer.index')
                ->with('success', 'Payment and booking confirmed successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Payment confirmation failed:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->route('frontend.trainer.index')
                ->with('error', 'Failed to confirm payment. Please try again.');
        }
    }
} 