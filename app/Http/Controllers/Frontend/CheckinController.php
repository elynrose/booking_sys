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
        $this->middleware('auth')->except(['index', 'verify']);
        $this->middleware('role:user')->only(['showCheckin', 'checkin', 'checkout']);
    }

    public function index()
    {
        abort_if(Gate::denies('checkin_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $checkins = Checkin::with(['booking.schedule.trainer.user'])
            ->whereHas('booking', function ($query) {
                $query->where('user_id', auth()->id());
            })
            ->latest()
            ->paginate(10);

        return view('frontend.checkins.index', compact('checkins'));
    }

    public function verify(Request $request)
    {
        abort_if(Gate::denies('checkin_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        // If it's a GET request, show the form
        if ($request->isMethod('get')) {
            return view('frontend.checkins.index');
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
        if (!$user->hasRole('user')) {
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
            // Format the check-in time for JavaScript with timezone offset
            $activeCheckin->formatted_checkin_time = $activeCheckin->checkin_time->toIso8601String();
        }

        // Get all paid and confirmed bookings for this user
        $bookings = Booking::with(['schedule', 'child'])
            ->where('user_id', $user->id)
            ->where('is_paid', true)
            ->where('status', 'confirmed')
            ->orderBy('created_at', 'desc')
            ->get();

        // Get unpaid bookings count
        $unpaidBookings = Booking::where('user_id', $user->id)
            ->where('is_paid', false)
            ->where('status', 'confirmed')
            ->count();

        return view('frontend.checkins.verify', compact('bookings', 'unpaidBookings', 'user', 'request', 'activeCheckin'));
    }

    public function checkin(Request $request)
    {
        abort_if(Gate::denies('checkin_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

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

        // Check if already checked in today
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

        $checkin = Checkin::create([
            'booking_id' => $request->booking_id,
            'user_id' => $user->id,
            'checkin_time' => now(),
        ]);

        return view('frontend.checkins.success', compact('booking', 'checkin'));
    }

    public function checkout(Request $request)
    {
        abort_if(Gate::denies('checkin_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

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
            'checkout_time' => Carbon::now()
        ]);

        // Calculate duration
        $duration = Carbon::parse($checkin->checkout_time)->diffInSeconds($checkin->checkin_time);
        $hours = floor($duration / 3600);
        $minutes = floor(($duration % 3600) / 60);
        $seconds = $duration % 60;

        return view('frontend.checkins.checkout-success', compact('booking', 'checkin', 'hours', 'minutes', 'seconds'));
    }

    public function showCheckin(Booking $booking)
    {
        abort_if(Gate::denies('checkin_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        // Verify that the current user has the 'user' role
        if (!auth()->user()->hasRole('user')) {
            return redirect()->route('frontend.checkins.index')
                ->with('error', 'Only users can access this page.');
        }

        // Load all necessary relationships
        $booking->load(['schedule.class', 'child', 'user']);

        // Check if already checked in today
        $existingCheckin = Checkin::where('booking_id', $booking->id)
            ->whereDate('created_at', Carbon::today())
            ->first();

        if ($existingCheckin) {
            // Check if there's an existing check-in without checkout time
            if (!$existingCheckin->checkout_time) {
                // If already checked in without checkout, show the timer page
                return view('frontend.checkins.success', compact('booking', 'existingCheckin'));
            } else {
                // If already checked out, show error
                return redirect()->route('frontend.checkins.index')
                    ->with('error', 'You have already checked out for this class today.');
            }
        }

        // If not checked in, redirect to the check-in form
        return redirect()->route('frontend.checkins.index')
            ->with('error', 'Please check in through the check-in form.');
    }
} 