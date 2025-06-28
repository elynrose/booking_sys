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
        if (auth()->user()->hasRole('trainer')) {
            $trainerSchedules = auth()->user()->trainer->schedules->pluck('id');
            $recentPaymentsQuery->whereHas('booking.schedule', function($q) use ($trainerSchedules) {
                $q->whereIn('id', $trainerSchedules);
            });
        } elseif (!auth()->user()->hasRole('admin')) {
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
} 