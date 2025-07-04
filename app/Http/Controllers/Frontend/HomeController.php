<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Schedule;
use App\Models\Payment;
use App\Models\Booking;
use App\Models\Checkin;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\Trainer;
use App\Models\Category;
use Gate;
use Symfony\Component\HttpFoundation\Response;

class HomeController extends Controller
{
    public function index()
    {
        abort_if(Gate::denies('home_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $user = Auth::user();
        $today = Carbon::today();
        $siteTimezone = \App\Models\SiteSettings::getTimezone();

        // Get active schedules (schedules that haven't ended yet and have remaining sessions)
        $activeSchedules = Booking::where('user_id', $user->id)
            ->where('is_paid', true)
            ->where('status', 'confirmed')
            ->whereHas('schedule', function($query) {
                $query->where('end_date', '>=', Carbon::today())
                    ->with('trainer');
            })
            ->with(['schedule' => function($query) {
                $query->where('end_date', '>=', Carbon::today())
                    ->with('trainer');
            }, 'child', 'checkins'])
            ->get()
            ->filter(function($booking) {
                return $booking->sessions_remaining > 0 && $booking->checkins->count() < $booking->sessions_remaining;
            });

        // Get pending check-ins (bookings that need to be checked in today)
        $pendingCheckins = Booking::where('user_id', $user->id)
            ->where('is_paid', true)
            ->where('status', 'confirmed')
            ->whereHas('schedule', function($query) use ($today) {
                $query->where('end_date', '>=', $today);
            })
            ->whereDoesntHave('checkins', function($query) use ($today) {
                $query->whereDate('created_at', $today);
            })
            ->get()
            ->filter(function($booking) {
                return $booking->sessions_remaining > 0 && $booking->checkins->count() < $booking->sessions_remaining;
            });

        // Calculate pending payments
        $pendingPayments = Payment::where('user_id', $user->id)
            ->where('status', 'pending')
            ->sum('amount');

        // Calculate paid bookings total amount (minus refunds and cancellations)
        $paidBookingsTotal = Booking::where('user_id', $user->id)
            ->where('is_paid', true)
            ->where('status', '!=', 'cancelled')
            ->sum('total_cost');
            
        // Subtract refunded payments
        $refundedAmount = Payment::where('user_id', $user->id)
            ->where('status', 'refunded')
            ->sum('amount');
            
        $paidBookingsTotal = $paidBookingsTotal - $refundedAmount;

        // Get payment history with booking relationships
        $paymentHistory = Payment::where('user_id', $user->id)
            ->with(['booking.user', 'booking.child', 'booking.schedule'])
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        // Get featured schedules
        $featuredSchedules = Schedule::with(['trainer.user', 'bookings'])
            ->where('status', '=', 'active')
            ->where('start_date', '>=', Carbon::now($siteTimezone)->toDateTimeString())
            ->latest()
            ->take(6)
            ->get();

        // Get featured trainers
        $featuredTrainers = Trainer::with(['user', 'schedules'])
            ->where('is_active', true)
            ->whereHas('schedules', function ($query) {
                $query->where('status', '=', 'active')
                    ->where('start_date', '>=', Carbon::now($siteTimezone)->toDateTimeString());
            })
            ->take(4)
            ->get();

        // Get categories with active schedules
        $categories = Category::whereHas('schedules', function ($query) {
            $query->where('status', '=', 'active')
                ->where('start_date', '>=', Carbon::now($siteTimezone)->toDateTimeString());
        })
        ->withCount(['schedules' => function ($query) {
            $query->where('status', '=', 'active')
                ->where('start_date', '>=', Carbon::now($siteTimezone)->toDateTimeString());
        }])
        ->take(6)
        ->get();

        return view('frontend.home', compact(
            'activeSchedules',
            'pendingCheckins',
            'pendingPayments',
            'paidBookingsTotal',
            'paymentHistory',
            'featuredSchedules',
            'featuredTrainers',
            'categories'
        ));
    }

    public function about()
    {
        abort_if(Gate::denies('home_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('frontend.about');
    }

    public function contact()
    {
        abort_if(Gate::denies('home_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('frontend.contact');
    }

    public function privacy()
    {
        abort_if(Gate::denies('home_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('frontend.privacy');
    }

    public function terms()
    {
        abort_if(Gate::denies('home_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('frontend.terms');
    }

    /**
     * Get the next session date for a schedule
     */
    private function getNextSessionDate($schedule)
    {
        $today = Carbon::today();
        $siteTimezone = \App\Models\SiteSettings::getTimezone();
        $startDate = Carbon::parse($schedule->start_date, $siteTimezone);
        $endDate = Carbon::parse($schedule->end_date, $siteTimezone);
        
        // If schedule hasn't started yet, return start date
        if ($today->lt($startDate)) {
            return $startDate;
        }

        // If schedule has ended, return null
        if ($today->gt($endDate)) {
            return null;
        }

        // Get the day of week for the schedule
        $dayOfWeek = $startDate->dayOfWeek;
        
        // Find the next occurrence of this day
        $nextDate = $today->copy();
        while ($nextDate->dayOfWeek !== $dayOfWeek) {
            $nextDate->addDay();
        }

        // If the next date is after the end date, return null
        if ($nextDate->gt($endDate)) {
            return null;
        }

        return $nextDate;
    }

    /**
     * Check if user is checked in for a schedule
     */
    private function isCheckedIn($schedule)
    {
        $user = Auth::user();
        $today = Carbon::today();
        $siteTimezone = \App\Models\SiteSettings::getTimezone();
        
        return $schedule->users()
            ->where('users.id', $user->id)
            ->wherePivot('check_in_date', $today)
            ->exists();
    }
}
