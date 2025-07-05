<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Checkin;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Gate;
use Symfony\Component\HttpFoundation\Response;

class CheckinController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->except(['index', 'verify', 'checkin', 'checkout', 'autoCheckout', 'autoCheckoutSuccess']);
    }

    public function index()
    {
        return view('frontend.checkins.index');
    }

    public function verify(Request $request)
    {
        // Initialize variables with default values
        $bookings = collect();
        $unpaidBookings = 0;
        $user = null;
        $activeCheckin = null;
        $siteTimezone = \App\Models\SiteSettings::getTimezone();

        // If it's a GET request, show the form
        if ($request->method() === 'GET') {
            return view('frontend.checkins.verify', compact('bookings', 'unpaidBookings', 'user', 'request', 'activeCheckin', 'siteTimezone'));
        }

        // Handle POST request
        $request->validate([
            'member_id' => 'required|string',
        ]);

        $user = User::where('member_id', $request->member_id)->first();

        if (!$user) {
            return redirect()->route('frontend.checkins.index')
                ->with('error', 'Member ID not found. Please check and try again.');
        }

        // Verify that the user has the 'user' role
        if (!$user->hasRole('User')) {
            return redirect()->route('frontend.checkins.index')
                ->with('error', 'This member ID is not associated with a valid user account.');
        }

        // Store the verified member code in session
        session(['verified_member_id' => $request->member_id]);

        // Check if user has any active check-ins (checked in but not checked out)
        $activeCheckin = Checkin::with(['booking' => function($query) {
                $query->with(['schedule', 'child']);
            }])
            ->whereHas('booking', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->whereDate('created_at', Carbon::today())
            ->whereNull('checkout_time')
            ->first();

        if ($activeCheckin) {
            // Format the check-in time for JavaScript with proper ISO string
            // Times are now stored in UTC, so we can use toISOString() directly
            $activeCheckin->formatted_checkin_time = $activeCheckin->checkin_time->toISOString();
            \Log::info('Active check-in time:', [
                'raw_time' => $activeCheckin->checkin_time,
                'formatted_time' => $activeCheckin->formatted_checkin_time,
                'timezone' => 'UTC'
            ]);
        }

        // Get all paid and confirmed bookings for this user
        $bookings = Booking::with(['schedule.trainer.user', 'child'])
            ->where('user_id', $user->id)
            ->where('is_paid', true)
            ->where('status', 'confirmed')
            ->where(function($query) {
                $query->where('sessions_remaining', '>', 0)
                      ->orWhereHas('schedule', function($scheduleQuery) {
                          $scheduleQuery->where('allow_unlimited_bookings', true);
                      });
            })
            ->whereHas('schedule', function($scheduleQuery) use ($siteTimezone) {
                // Only show classes that haven't ended yet (combine date and time)
                $currentDateTime = Carbon::now($siteTimezone);
                $scheduleQuery->where(function($q) use ($currentDateTime) {
                    $q->whereRaw('CONCAT(DATE(end_date), " ", TIME(end_time)) > ?', [$currentDateTime->format('Y-m-d H:i:s')]);
                });
            })
            ->orderBy('created_at', 'desc')
            ->get();

        // Get unpaid bookings count
        $unpaidBookings = Booking::where('user_id', $user->id)
            ->where('is_paid', false)
            ->where('status', 'confirmed')
            ->count();

        // Get site timezone from settings
        $siteTimezone = \App\Models\SiteSettings::getTimezone();

        return view('frontend.checkins.verify', compact('bookings', 'unpaidBookings', 'user', 'request', 'activeCheckin', 'siteTimezone'));
    }

    public function checkin(Request $request)
    {
        $siteTimezone = \App\Models\SiteSettings::getTimezone();

        $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'user_id' => 'required|exists:users,id'
        ]);

        // Verify the member code from session
        $memberId = session('verified_member_id');
        if (!$memberId) {
            return redirect()->route('frontend.checkins.index')
                ->with('error', 'Please verify your member ID first.');
        }

        $user = User::where('member_id', $memberId)->first();
        if (!$user || $user->id != $request->user_id) {
            return redirect()->route('frontend.checkins.index')
                ->with('error', 'Unauthorized access. Please verify your member ID again.');
        }

        $booking = Booking::with(['schedule', 'child'])->findOrFail($request->booking_id);

        // Verify that the booking belongs to the user
        if ($booking->user_id != $user->id) {
            return redirect()->route('frontend.checkins.index')
                ->with('error', 'Unauthorized access to this booking.');
        }

        // Check if class has started (prevent checkin for future classes)
        $currentTime = Carbon::now($siteTimezone);
        $scheduleStartTime = Carbon::parse($booking->schedule->start_time, $siteTimezone);
        
        if ($currentTime->lt($scheduleStartTime)) {
            $timeUntilStart = $currentTime->diffForHumans($scheduleStartTime, ['parts' => 2]);
            return redirect()->route('frontend.checkins.index')
                ->with('error', 'Cannot check in for future classes. This class starts in ' . $timeUntilStart . '.');
        }

        // Check if sessions_remaining is 0 or less (only for non-unlimited schedules)
        if (!$booking->schedule->allow_unlimited_bookings && $booking->sessions_remaining <= 0) {
            return redirect()->route('frontend.checkins.index')
                ->with('error', 'You have no sessions remaining for this booking.');
        }

        // Check if already checked in today (only for non-unlimited schedules)
        if (!$booking->schedule->allow_unlimited_bookings) {
            $existingCheckin = Checkin::where('booking_id', $request->booking_id)
                ->whereDate('created_at', Carbon::today())
                ->first();

            if ($existingCheckin) {
                if (!$existingCheckin->checkout_time) {
                    return view('frontend.checkins.success', compact('booking', 'existingCheckin'));
                } else {
                    return redirect()->route('frontend.checkins.index')
                        ->with('error', 'You have already checked out for this class today.');
                }
            }
        }

        // Check for late check-in
        $scheduleEndTime = Carbon::parse($booking->schedule->end_time, $siteTimezone);
        
        $isLateCheckin = $currentTime->gt($scheduleStartTime);
        $lateMinutes = $isLateCheckin ? $currentTime->diffInMinutes($scheduleStartTime) : 0;

        // Create check-in record
        $checkin = Checkin::create([
            'booking_id' => $request->booking_id,
            'user_id' => $user->id,
            'checkin_time' => $currentTime->utc(),
            'is_late_checkin' => $isLateCheckin,
            'late_minutes' => $lateMinutes,
        ]);

        // Send admin notification for late check-in
        if ($isLateCheckin) {
            try {
                // Get admin users
                $admins = User::role('Admin')->get();
                foreach ($admins as $admin) {
                    $admin->notify(new \App\Notifications\LateCheckinNotification($booking, $checkin, $lateMinutes));
                }
            } catch (\Exception $e) {
                \Log::error('Failed to send late check-in notification: ' . $e->getMessage());
            }
        }

        return view('frontend.checkins.success', compact('booking', 'checkin', 'isLateCheckin', 'lateMinutes'));
    }

    public function checkout(Request $request)
    {
        $siteTimezone = \App\Models\SiteSettings::getTimezone();

        $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'user_id' => 'required|exists:users,id'
        ]);

        // Verify the member code from session
        $memberId = session('verified_member_id');
        if (!$memberId) {
            return redirect()->route('frontend.checkins.index')
                ->with('error', 'Please verify your member ID first.');
        }

        $user = User::where('member_id', $memberId)->first();
        if (!$user || $user->id != $request->user_id) {
            return redirect()->route('frontend.checkins.index')
                ->with('error', 'Unauthorized access. Please verify your member ID again.');
        }

        $booking = Booking::with(['schedule', 'child'])
            ->where('id', $request->booking_id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        // Find today's check-in
        $checkin = Checkin::where('booking_id', $booking->id)
            ->whereDate('created_at', Carbon::today())
            ->first();

        if (!$checkin) {
            return redirect()->route('frontend.checkins.index')
                ->with('error', 'No check-in record found for today.');
        }

        if ($checkin->checkout_time) {
            return redirect()->route('frontend.checkins.index')
                ->with('error', 'Already checked out for today.');
        }

        // Update check-out time
        $checkin->update([
            'checkout_time' => Carbon::now($siteTimezone)->utc()
        ]);

        // Decrement sessions_remaining by 1 (only for non-unlimited schedules)
        if (!$booking->schedule->allow_unlimited_bookings) {
            $booking->decrement('sessions_remaining');
        }

        // Calculate duration
        $duration = Carbon::parse($checkin->checkout_time)->diffInSeconds($checkin->checkin_time);
        $hours = floor($duration / 3600);
        $minutes = floor(($duration % 3600) / 60);
        $seconds = $duration % 60;

        return view('frontend.checkins.checkout-success', compact('booking', 'checkin', 'hours', 'minutes', 'seconds'));
    }

    public function autoCheckout(Request $request)
    {
        abort_if(Gate::denies('checkin_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $siteTimezone = \App\Models\SiteSettings::getTimezone();

        $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'user_id' => 'required|exists:users,id'
        ]);

        // Verify the member code from session
        $memberId = session('verified_member_id');
        if (!$memberId) {
            return response()->json(['error' => 'Please verify your member ID first.'], 400);
        }

        $user = User::where('member_id', $memberId)->first();
        if (!$user || $user->id != $request->user_id) {
            return response()->json(['error' => 'Unauthorized access.'], 400);
        }

        $booking = Booking::with(['schedule', 'child'])
            ->where('id', $request->booking_id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        // Find today's check-in
        $checkin = Checkin::where('booking_id', $booking->id)
            ->whereDate('created_at', Carbon::today())
            ->whereNull('checkout_time')
            ->first();

        if (!$checkin) {
            return response()->json(['error' => 'No active check-in found.'], 400);
        }

        // Update check-out time
        $checkin->update([
            'checkout_time' => Carbon::now($siteTimezone)->utc()
        ]);

        // Decrement sessions_remaining by 1 (only for non-unlimited schedules)
        if (!$booking->schedule->allow_unlimited_bookings) {
            $booking->decrement('sessions_remaining');
        }

        // Calculate duration
        $duration = Carbon::parse($checkin->checkout_time)->diffInSeconds($checkin->checkin_time);
        $hours = floor($duration / 3600);
        $minutes = floor(($duration % 3600) / 60);
        $seconds = $duration % 60;

        // Send email notification
        try {
            $user->notify(new \App\Notifications\AutoCheckoutNotification($booking, $checkin, $hours, $minutes, $seconds));
        } catch (\Exception $e) {
            \Log::error('Failed to send auto checkout notification: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Auto checkout completed successfully',
            'duration' => [
                'hours' => $hours,
                'minutes' => $minutes,
                'seconds' => $seconds
            ]
        ]);
    }

    public function autoCheckoutSuccess()
    {
        // Get the most recent auto checkout for the current user
        $siteTimezone = \App\Models\SiteSettings::getTimezone();
        $checkin = Checkin::with(['booking.schedule', 'booking.child'])
            ->whereHas('booking', function($query) {
                $query->where('user_id', auth()->id());
            })
            ->whereNotNull('checkout_time')
            ->whereDate('created_at', Carbon::today())
            ->latest()
            ->first();

        if (!$checkin) {
            return redirect()->route('frontend.checkins.index')
                ->with('error', 'No recent checkout found.');
        }

        // Calculate duration
        $duration = Carbon::parse($checkin->checkout_time)->diffInSeconds($checkin->checkin_time);
        $hours = floor($duration / 3600);
        $minutes = floor(($duration % 3600) / 60);
        $seconds = $duration % 60;

        $booking = $checkin->booking;

        return view('frontend.checkins.auto-checkout-success', compact('booking', 'checkin', 'hours', 'minutes', 'seconds'));
    }
} 