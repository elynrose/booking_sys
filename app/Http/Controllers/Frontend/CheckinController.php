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
                    $q->whereRaw("(end_date::date || ' ' || end_time::time)::timestamp > ?", [$currentDateTime->format('Y-m-d H:i:s')]);
                });
            })
            ->orderBy('created_at', 'desc')
            ->get();

        // For unlimited classes, get trainer availability data
        foreach ($bookings as $booking) {
            if ($booking->schedule->allow_unlimited_bookings && $booking->schedule->trainer) {
                $currentTime = Carbon::now($siteTimezone);
                $currentDate = $currentTime->format('Y-m-d');
                $currentTimeStr = $currentTime->format('H:i:s');

                // Get today's availability
                $todayAvailability = \App\Models\TrainerAvailability::where('trainer_id', $booking->schedule->trainer->id)
                    ->where('schedule_id', $booking->schedule->id)
                    ->where('date', $currentDate)
                    ->where('status', 'available')
                    ->where('start_time', '<=', $currentTimeStr)
                    ->where('end_time', '>', $currentTimeStr)
                    ->first();

                // Get next available session
                $nextAvailable = \App\Models\TrainerAvailability::where('trainer_id', $booking->schedule->trainer->id)
                    ->where('schedule_id', $booking->schedule->id)
                    ->where('status', 'available')
                    ->whereRaw('DATE(date) >= ?', [$currentDate])
                    ->orderBy('date')
                    ->orderBy('start_time')
                    ->first();

                // Get this month's availability for display
                $currentMonth = Carbon::now($siteTimezone)->format('Y-m');
                $monthStart = Carbon::createFromFormat('Y-m', $currentMonth, $siteTimezone)->startOfMonth();
                $monthEnd = Carbon::createFromFormat('Y-m', $currentMonth, $siteTimezone)->endOfMonth();
                
                $monthlyAvailability = \App\Models\TrainerAvailability::where('trainer_id', $booking->schedule->trainer->id)
                    ->where('schedule_id', $booking->schedule->id)
                    ->where('status', 'available')
                    ->whereRaw('DATE(date) >= ?', [$monthStart->format('Y-m-d')])
                    ->whereRaw('DATE(date) <= ?', [$monthEnd->format('Y-m-d')])
                    ->orderBy('date')
                    ->orderBy('start_time')
                    ->get();

                $booking->trainer_availability = [
                    'today_available' => $todayAvailability,
                    'next_available' => $nextAvailable,
                    'monthly_availability' => $monthlyAvailability
                ];
            }
        }

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

        // For unlimited classes, check trainer availability
        if ($booking->schedule->allow_unlimited_bookings) {
            $trainerAvailability = $this->checkTrainerAvailability($booking, $currentTime, $siteTimezone);
            
            if (!$trainerAvailability['available']) {
                if ($trainerAvailability['next_available']) {
                    $nextDate = Carbon::parse($trainerAvailability['next_available']->date)->format('l, F d, Y');
                    $nextTime = Carbon::parse($trainerAvailability['next_available']->start_time)->format('g:i A');
                    return redirect()->route('frontend.checkins.index')
                        ->with('error', 'Trainer is not available at this time. Next available session: ' . $nextDate . ' at ' . $nextTime . '.');
                } else {
                    return redirect()->route('frontend.checkins.index')
                        ->with('error', 'Trainer is not available at this time. No upcoming sessions found.');
                }
            }
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

        return view('frontend.checkins.success', compact('booking', 'checkin'));
    }

    /**
     * Check trainer availability for unlimited classes
     */
    private function checkTrainerAvailability($booking, $currentTime, $siteTimezone)
    {
        $schedule = $booking->schedule;
        $trainer = $schedule->trainer;
        
        if (!$trainer) {
            \Log::info('Trainer availability check failed: No trainer assigned', [
                'schedule_id' => $schedule->id,
                'schedule_title' => $schedule->title
            ]);
            return [
                'available' => false,
                'next_available' => null,
                'message' => 'No trainer assigned to this class.'
            ];
        }

        $currentDate = $currentTime->format('Y-m-d');
        $currentTimeStr = $currentTime->format('H:i:s');

        \Log::info('Checking trainer availability', [
            'trainer_id' => $trainer->id,
            'schedule_id' => $schedule->id,
            'current_date' => $currentDate,
            'current_time' => $currentTimeStr
        ]);

        // Check if trainer is available for today
        $todayAvailability = \App\Models\TrainerAvailability::where('trainer_id', $trainer->id)
            ->where('schedule_id', $schedule->id)
            ->where('date', $currentDate)
            ->where('status', 'available')
            ->where('start_time', '<=', $currentTimeStr)
            ->where('end_time', '>', $currentTimeStr)
            ->first();

        if ($todayAvailability) {
            \Log::info('Trainer is available now', [
                'trainer_id' => $trainer->id,
                'schedule_id' => $schedule->id,
                'availability_id' => $todayAvailability->id
            ]);
            return [
                'available' => true,
                'next_available' => null,
                'message' => 'Trainer is available.'
            ];
        }

        // If not available today, find the next available session
        $nextAvailable = \App\Models\TrainerAvailability::where('trainer_id', $trainer->id)
            ->where('schedule_id', $schedule->id)
            ->where('status', 'available')
            ->whereRaw('DATE(date) >= ?', [$currentDate])
            ->orderBy('date')
            ->orderBy('start_time')
            ->first();

        \Log::info('Trainer availability check result', [
            'trainer_id' => $trainer->id,
            'schedule_id' => $schedule->id,
            'available_now' => false,
            'next_available' => $nextAvailable ? $nextAvailable->date . ' ' . $nextAvailable->start_time->format('H:i') : null
        ]);

        return [
            'available' => false,
            'next_available' => $nextAvailable,
            'message' => 'Trainer is not available at this time.'
        ];
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

    public function quickCheckout(Request $request)
    {
        $siteTimezone = \App\Models\SiteSettings::getTimezone();

        $request->validate([
            'booking_id' => 'required|exists:bookings,id',
        ]);

        // For logged-in users, use the authenticated user
        $user = auth()->user();
        if (!$user) {
            return redirect()->route('login')
                ->with('error', 'Please log in to checkout.');
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
            return redirect()->route('frontend.home')
                ->with('error', 'No active check-in found for today.');
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

        return redirect()->route('frontend.home')
            ->with('success', "Successfully checked out! Duration: {$hours}h {$minutes}m {$seconds}s");
    }

    public function autoCheckout(Request $request)
    {
        abort_if(Gate::denies('checkin_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $siteTimezone = \App\Models\SiteSettings::getTimezone();

        $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'user_id' => 'required|exists:users,id'
        ]);

        // For logged-in users, use the authenticated user
        $user = auth()->user();
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