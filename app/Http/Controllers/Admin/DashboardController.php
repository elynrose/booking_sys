<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Category;
use App\Models\Payment;
use App\Models\Schedule;
use App\Models\Trainer;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Gate;
use Symfony\Component\HttpFoundation\Response;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        abort_if(Gate::denies('dashboard_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        // Get date range from request or default to last 30 days
        $startDate = $request->input('start_date', Carbon::now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));

        // Convert to Carbon instances
        $startDate = Carbon::parse($startDate)->startOfDay();
        $endDate = Carbon::parse($endDate)->endOfDay();

        // Get total statistics
        $totalBookings = Booking::count();
        $totalRevenue = Payment::where('payments.status', 'paid')->sum('amount');
        $realizedRevenue = Payment::where('payments.status', 'paid')->whereDate('created_at', '<=', now())->sum('amount');
        $unrealizedRevenue = Payment::where('payments.status', 'paid')->whereDate('created_at', '>', now())->sum('amount');
        $totalUsers = User::count();
        $totalTrainers = Trainer::count();
        $totalSchedules = Schedule::count();
        $totalCategories = Category::count();

        // Get date range statistics
        $dateRangeBookings = Booking::whereBetween('created_at', [$startDate, $endDate])->count();
        $dateRangeRevenue = Payment::where('payments.status', 'paid')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('amount');
        $dateRangeUsers = User::whereBetween('created_at', [$startDate, $endDate])->count();

        // Get booking statistics
        $bookingStats = [
            'confirmed' => Booking::where('bookings.status', 'confirmed')->count(),
            'pending' => Booking::where('bookings.status', 'pending')->count(),
            'cancelled' => Booking::where('bookings.status', 'cancelled')->count(),
            'completed' => Booking::where('bookings.status', 'completed')->count(),
        ];

        // Get payment statistics
        $paymentStats = [
            'completed' => Payment::where('payments.status', 'completed')->count(),
            'pending' => Payment::where('payments.status', 'pending')->count(),
            'failed' => Payment::where('payments.status', 'failed')->count(),
            'refunded' => Payment::where('payments.status', 'refunded')->count(),
        ];

        // Get revenue by category
        $revenueByCategory = Payment::where('payments.status', 'paid')
            ->whereBetween('payments.created_at', [$startDate, $endDate])
            ->join('bookings', 'payments.booking_id', '=', 'bookings.id')
            ->join('schedules', 'bookings.schedule_id', '=', 'schedules.id')
            ->join('categories', 'schedules.category_id', '=', 'categories.id')
            ->select('categories.name', DB::raw('SUM(payments.amount) as total'))
            ->groupBy('categories.id', 'categories.name')
            ->get();

        // Debug revenue by category
        \Log::info('Revenue by Category Query Result:', [
            'count' => $revenueByCategory->count(),
            'data' => $revenueByCategory->toArray(),
            'startDate' => $startDate->format('Y-m-d H:i:s'),
            'endDate' => $endDate->format('Y-m-d H:i:s')
        ]);

        // Get revenue by trainer
        $revenueByTrainer = Payment::where('payments.status', 'paid')
            ->whereBetween('payments.created_at', [$startDate, $endDate])
            ->join('bookings', 'payments.booking_id', '=', 'bookings.id')
            ->join('schedules', 'bookings.schedule_id', '=', 'schedules.id')
            ->join('trainers', 'schedules.trainer_id', '=', 'trainers.id')
            ->join('users', 'trainers.user_id', '=', 'users.id')
            ->select('users.name', DB::raw('SUM(payments.amount) as total'))
            ->groupBy('trainers.id', 'users.name')
            ->get();

        // Debug revenue by trainer
        \Log::info('Revenue by Trainer Query Result:', [
            'count' => $revenueByTrainer->count(),
            'data' => $revenueByTrainer->toArray(),
            'startDate' => $startDate->format('Y-m-d H:i:s'),
            'endDate' => $endDate->format('Y-m-d H:i:s')
        ]);

        // Get daily revenue for chart
        $dailyRevenue = Payment::where('payments.status', 'paid')
            ->whereBetween('payments.created_at', [$startDate, $endDate])
            ->select(
                DB::raw('DATE(payments.created_at) as date'),
                DB::raw('SUM(payments.amount) as total')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Get daily bookings for chart
        $dailyBookings = Booking::whereBetween('bookings.created_at', [$startDate, $endDate])
            ->select(
                DB::raw('DATE(bookings.created_at) as date'),
                DB::raw('COUNT(*) as total')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Get recent activities
        $recentBookings = Booking::with(['user', 'schedule.trainer.user', 'schedule.category'])
            ->latest()
            ->take(5)
            ->get();

        $recentPaymentsQuery = Payment::with(['booking.user', 'booking.schedule.trainer.user']);
        
        // Apply role-based filtering
        if (auth()->user()->hasRole('Trainer')) {
            $trainerSchedules = auth()->user()->trainer->schedules->pluck('id');
            $recentPaymentsQuery->whereHas('booking.schedule', function($q) use ($trainerSchedules) {
                $q->whereIn('id', $trainerSchedules);
            });
        } elseif (!auth()->user()->hasRole('Admin')) {
            $recentPaymentsQuery->whereHas('booking', function($q) {
                $q->where('user_id', auth()->id());
            });
        }

        $recentPayments = $recentPaymentsQuery->latest()->take(5)->get();

        return view('admin.dashboard', compact(
            'startDate',
            'endDate',
            'totalBookings',
            'totalRevenue',
            'realizedRevenue',
            'unrealizedRevenue',
            'totalUsers',
            'totalTrainers',
            'totalSchedules',
            'totalCategories',
            'dateRangeBookings',
            'dateRangeRevenue',
            'dateRangeUsers',
            'bookingStats',
            'paymentStats',
            'revenueByCategory',
            'revenueByTrainer',
            'dailyRevenue',
            'dailyBookings',
            'recentBookings',
            'recentPayments',
            
        ));
    }

    public function liveDashboard()
    {
        abort_if(Gate::denies('dashboard_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.live-dashboard');
    }

    public function liveDashboardData(Request $request)
    {
        abort_if(Gate::denies('dashboard_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        // If only filter options are requested
        if ($request->has('options_only')) {
            $classOptions = Schedule::select('id', 'title')->orderBy('title')->get();
            $trainerOptions = \App\Models\Trainer::with('user')->get()->map(function($t) {
                return ['id' => $t->id, 'name' => $t->user->name];
            });
            return response()->json([
                'classOptions' => $classOptions,
                'trainerOptions' => $trainerOptions,
            ]);
        }

        $date = $request->input('date', Carbon::today()->format('Y-m-d'));
        $classId = $request->input('class_id');
        $trainerId = $request->input('trainer_id');
        $status = $request->input('status');
        $now = Carbon::now();
        $targetDate = Carbon::parse($date);

        // Filtered current classes
        $currentClassesQuery = Schedule::with(['trainer.user', 'category', 'bookings.user', 'bookings.checkin'])
            ->whereDate('start_time', $targetDate);
        if ($classId) {
            $currentClassesQuery->where('id', $classId);
        }
        if ($trainerId) {
            $currentClassesQuery->where('trainer_id', $trainerId);
        }
        $currentClasses = $currentClassesQuery->get()->map(function ($schedule) use ($now, $status) {
            $sStatus = 'upcoming';
            if ($schedule->start_time <= $now && $schedule->end_time >= $now) {
                $sStatus = 'active';
            } elseif ($schedule->end_time < $now) {
                $sStatus = 'ended';
            }
            if ($status && $sStatus !== $status) return null;
            $checkedInCount = $schedule->bookings()->whereHas('checkin', function($q) {
                $q->whereNotNull('checkin_time')->whereNull('checkout_time');
            })->count();
            return [
                'id' => $schedule->id,
                'title' => $schedule->title,
                'trainer' => $schedule->trainer->user->name ?? 'Unassigned',
                'start_time' => $schedule->start_time->format('g:i A'),
                'end_time' => $schedule->end_time->format('g:i A'),
                'status' => $sStatus,
                'current_participants' => $schedule->bookings()->where('status', 'confirmed')->count(),
                'max_participants' => $schedule->max_participants ?? 20,
                'checked_in_count' => $checkedInCount,
            ];
        })->filter()->values();

        // Filtered trainer assignments
        $trainerAssignmentsQuery = Trainer::with(['user', 'schedules.bookings.user', 'schedules.bookings.checkin']);
        if ($trainerId) {
            $trainerAssignmentsQuery->where('id', $trainerId);
        }
        $trainerAssignments = $trainerAssignmentsQuery->get()->map(function ($trainer) use ($targetDate, $now, $classId) {
            $currentClass = $trainer->schedules()
                ->whereDate('start_time', $targetDate->toDateString())
                ->where('start_time', '<=', $now)
                ->where('end_time', '>=', $now);
            if ($classId) $currentClass->where('id', $classId);
            $currentClass = $currentClass->first();
            $students = $trainer->schedules()
                ->with(['bookings.user'])
                ->whereDate('start_time', $targetDate->toDateString());
            if ($classId) $students->where('id', $classId);
            $students = $students->get()->flatMap(function ($schedule) {
                return $schedule->bookings()->where('status', 'confirmed')->with('user')->get()->map(function ($booking) {
                    return [
                        'id' => $booking->user->id,
                        'name' => $booking->user->name,
                    ];
                });
            })->unique('id')->values();
            return [
                'id' => $trainer->id,
                'name' => $trainer->user->name,
                'status' => $currentClass ? 'active' : 'available',
                'current_class' => $currentClass ? $currentClass->title : null,
                'student_count' => $students->count(),
                'students' => $students->take(10)->toArray(),
            ];
        });

        // Filtered recent check-ins
        $recentCheckinsQuery = \App\Models\Checkin::with(['booking.user', 'booking.schedule'])
            ->whereDate('created_at', $targetDate);
        if ($classId) {
            $recentCheckinsQuery->whereHas('booking', function($q) use ($classId) {
                $q->where('schedule_id', $classId);
            });
        }
        if ($trainerId) {
            $recentCheckinsQuery->whereHas('booking.schedule', function($q) use ($trainerId) {
                $q->where('trainer_id', $trainerId);
            });
        }
        $recentCheckins = $recentCheckinsQuery->orderBy('created_at', 'desc')->take(20)->get()->map(function ($checkin) {
            return [
                'id' => $checkin->id,
                'student_name' => $checkin->booking->user->name ?? 'Unknown User',
                'class_name' => $checkin->booking->schedule->title ?? 'Unknown Class',
                'type' => $checkin->checkout_time ? 'out' : 'in',
                'time' => $checkin->created_at->format('g:i A'),
            ];
        });

        // Filtered activity feed
        $activityFeed = collect();
        $recentBookings = Booking::with(['user', 'schedule'])
            ->whereDate('created_at', $targetDate);
        if ($classId) $recentBookings->where('schedule_id', $classId);
        $recentBookings = $recentBookings->orderBy('created_at', 'desc')->take(10)->get();
        foreach ($recentBookings as $booking) {
            $activityFeed->push([
                'id' => $booking->id,
                'user' => $booking->user->name,
                'action' => 'booked',
                'details' => $booking->schedule->title,
                'time' => $booking->created_at->format('g:i A'),
            ]);
        }
        foreach ($recentCheckins as $checkin) {
            $activityFeed->push([
                'id' => 'checkin_' . $checkin['id'],
                'user' => $checkin['student_name'],
                'action' => 'checked ' . $checkin['type'],
                'details' => $checkin['class_name'],
                'time' => $checkin['time'],
            ]);
        }
        $recentPayments = Payment::with(['booking.user', 'booking.schedule'])
            ->whereDate('created_at', $targetDate)
            ->where('status', 'paid');
        if ($classId) $recentPayments->whereHas('booking', function($q) use ($classId) { $q->where('schedule_id', $classId); });
        $recentPayments = $recentPayments->orderBy('created_at', 'desc')->take(10)->get();
        foreach ($recentPayments as $payment) {
            $activityFeed->push([
                'id' => 'payment_' . $payment->id,
                'user' => $payment->booking->user->name,
                'action' => 'paid',
                'details' => '$' . number_format($payment->amount, 2) . ' for ' . $payment->booking->schedule->title,
                'time' => $payment->created_at->format('g:i A'),
            ]);
        }
        $activityFeed = $activityFeed->sortByDesc('time')->take(50)->values();

        // Statistics
        $statistics = [
            'activeClasses' => $currentClasses->where('status', 'active')->count(),
            'checkedInStudents' => \App\Models\Checkin::whereDate('created_at', $targetDate)
                ->whereNotNull('checkin_time')
                ->whereNull('checkout_time')
                ->count(),
            'activeTrainers' => $trainerAssignments->where('status', 'active')->count(),
            'pendingCheckouts' => \App\Models\Checkin::whereDate('created_at', $targetDate)
                ->whereNotNull('checkin_time')
                ->whereNull('checkout_time')
                ->count(),
        ];

        return response()->json([
            'statistics' => $statistics,
            'currentClasses' => $currentClasses->values(),
            'trainerAssignments' => $trainerAssignments->values(),
            'recentCheckins' => $recentCheckins->values(),
            'activityFeed' => $activityFeed->values(),
        ]);
    }
} 