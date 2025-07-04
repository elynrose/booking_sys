<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Schedule;
use App\Models\Checkin;
// use App\Models\Payment;
use App\Models\Booking;
use App\Models\Trainer;
// use App\Notifications\PaymentConfirmedNotification;
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
            ->where('status', '=', 'active')
            ->with(['bookings' => function($query) {
                $query->where('status', 'confirmed')
                    ->where('is_paid', true);
            }])
            ->get();

        // Get upcoming schedules
        $upcomingSchedules = Schedule::where('trainer_id', $trainer->id)
            ->whereDate('start_date', '>', Carbon::today())
            ->where('status', '=', 'active')
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

        // COMMENTED OUT: Get pending payments for trainer's schedules
        // $pendingPayments = Payment::whereHas('booking.schedule', function($query) use ($trainer) {
        //         $query->where('trainer_id', $trainer->id);
        //     })
        //     ->where('status', 'pending')
        //     ->with(['booking.child', 'booking.user', 'booking.schedule'])
        //     ->get();

        // Set empty collection for pending payments
        $pendingPayments = collect();

        // Get total classes assigned to this trainer
        $totalClasses = Schedule::where('trainer_id', $trainer->id)->count();
        
        // Get active classes (schedules that haven't ended yet)
        $activeClasses = Schedule::where('trainer_id', $trainer->id)
            ->where('status', '=', 'active')
            ->whereDate('end_date', '>=', Carbon::today())
            ->count();

        return view('frontend.trainer.index', compact('trainer', 'todaySchedules', 'upcomingSchedules', 'todayCheckins', 'pendingPayments', 'totalClasses', 'activeClasses'));
    }

    public function showClassDetails(Schedule $schedule)
    {
        abort_if(Gate::denies('trainer_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $user = auth()->user();
        $trainer = Trainer::where('user_id', $user->id)->first();
        
        if (!$trainer) {
            return redirect()->route('frontend.home')
                ->with('error', 'Trainer profile not found.');
        }

        // Verify the schedule belongs to this trainer
        if ($schedule->trainer_id !== $trainer->id) {
            abort(403, 'Unauthorized action.');
        }

        // Get all bookings for this schedule with related data
        $bookings = Booking::where('schedule_id', $schedule->id)
            ->where('status', 'confirmed')
            ->with([
                'user', 
                'child', 
                'checkins' => function($query) {
                    $query->orderBy('created_at', 'desc');
                }
            ])
            ->get();

        // Calculate check-in/check-out statistics for each booking
        $bookingsWithStats = $bookings->map(function($booking) {
            $checkins = $booking->checkins;
            $totalCheckins = $checkins->count();
            $totalCheckouts = $checkins->whereNotNull('checkout_time')->count();
            $currentlyCheckedIn = $checkins->whereNull('checkout_time')->count();

            $booking->checkin_stats = [
                'total_checkins' => $totalCheckins,
                'total_checkouts' => $totalCheckouts,
                'currently_checked_in' => $currentlyCheckedIn,
                'last_checkin' => $checkins->first() ? $checkins->first()->created_at : null,
                'last_checkout' => $checkins->whereNotNull('checkout_time')->first() ? 
                    $checkins->whereNotNull('checkout_time')->first()->checkout_time : null
            ];

            return $booking;
        });

        return view('frontend.trainer.class-details', compact('schedule', 'bookingsWithStats'));
    }

    public function showStudentDetails(Request $request)
    {
        abort_if(Gate::denies('trainer_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $user = auth()->user();
        $trainer = Trainer::where('user_id', $user->id)->first();
        
        if (!$trainer) {
            return redirect()->route('frontend.home')
                ->with('error', 'Trainer profile not found.');
        }

        $student = null;
        $schedule = null;
        $recommendations = collect();

        // Check if we're looking at a child or user
        if ($request->has('child')) {
            $student = \App\Models\Child::with('user')->find($request->child);
            if ($student) {
                // Get recommendations for this child
                $recommendations = \App\Models\Recommendation::where('child_id', $student->id)
                    ->where('trainer_id', $trainer->id)
                    ->with(['attachments'])
                    ->latest()
                    ->get();
            }
        } elseif ($request->has('user')) {
            $student = \App\Models\User::find($request->user);
            if ($student) {
                // Get recommendations for this user (if any)
                $recommendations = \App\Models\Recommendation::where('child_id', null)
                    ->where('trainer_id', $trainer->id)
                    ->latest()
                    ->get();
            }
        }

        if ($request->has('schedule')) {
            $schedule = Schedule::find($request->schedule);
        }

        if (!$student) {
            return redirect()->route('frontend.trainer.index')
                ->with('error', 'Student not found.');
        }

        // Get booking information
        $booking = null;
        if ($schedule) {
            if ($student instanceof \App\Models\Child) {
                $booking = Booking::where('schedule_id', $schedule->id)
                    ->where('child_id', $student->id)
                    ->where('status', 'confirmed')
                    ->first();
            } else {
                $booking = Booking::where('schedule_id', $schedule->id)
                    ->where('user_id', $student->id)
                    ->where('status', 'confirmed')
                    ->first();
            }
        }

        return view('frontend.trainer.student-details', compact('student', 'schedule', 'booking', 'recommendations', 'trainer'));
    }

    // COMMENTED OUT: Payment confirmation method
    /*
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
    */
} 