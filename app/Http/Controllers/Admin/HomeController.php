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


class HomeController
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
        
        // Calculate revenue with discounts - use the actual paid amount from payments
        $totalRevenue = Payment::where('payments.status', 'paid')->sum('amount') ?: 0;
        $realizedRevenue = Payment::where('payments.status', 'paid')->whereDate('created_at', '<=', now())->sum('amount') ?: 0;
        $unrealizedRevenue = Payment::where('payments.status', 'paid')->whereDate('created_at', '>', now())->sum('amount') ?: 0;
        
        // Calculate potential revenue (what would have been earned without discounts)
        $potentialRevenue = Payment::where('payments.status', 'paid')
            ->join('bookings', 'payments.booking_id', '=', 'bookings.id')
            ->join('schedules', 'bookings.schedule_id', '=', 'schedules.id')
            ->sum(DB::raw('schedules.price'));
            
        // Calculate total discounts given (ensure we have valid numbers)
        $potentialRevenue = $potentialRevenue ?: 0;
        $totalDiscounts = $potentialRevenue - $totalRevenue;
        
        $totalUsers = User::count();
        $totalTrainers = Trainer::count();
        $totalSchedules = Schedule::count();
        $totalCategories = Category::count();

        // Get date range statistics
        $dateRangeBookings = Booking::whereBetween('created_at', [$startDate, $endDate])->count();
        $dateRangeRevenue = Payment::where('payments.status', 'paid')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('amount');
            
        // Calculate date range potential revenue and discounts
        $dateRangePotentialRevenue = Payment::where('payments.status', 'paid')
            ->whereBetween('payments.created_at', [$startDate, $endDate])
            ->join('bookings', 'payments.booking_id', '=', 'bookings.id')
            ->join('schedules', 'bookings.schedule_id', '=', 'schedules.id')
            ->sum(DB::raw('schedules.price'));
            
        // Ensure we have valid numbers for calculations
        $dateRangePotentialRevenue = $dateRangePotentialRevenue ?: 0;
        $dateRangeRevenue = $dateRangeRevenue ?: 0;
        $dateRangeDiscounts = $dateRangePotentialRevenue - $dateRangeRevenue;
        
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

        // Get revenue by category with discount information
        $revenueByCategory = Payment::where('payments.status', 'paid')
            ->whereBetween('payments.created_at', [$startDate, $endDate])
            ->join('bookings', 'payments.booking_id', '=', 'bookings.id')
            ->join('schedules', 'bookings.schedule_id', '=', 'schedules.id')
            ->join('categories', 'schedules.category_id', '=', 'categories.id')
            ->select(
                'categories.name',
                DB::raw('SUM(payments.amount) as actual_revenue'),
                DB::raw('SUM(schedules.price) as potential_revenue'),
                DB::raw('SUM(schedules.price - payments.amount) as total_discounts'),
                DB::raw('COUNT(DISTINCT payments.id) as payment_count')
            )
            ->groupBy('categories.id', 'categories.name')
            ->get();

        // Get revenue by trainer with discount information
        $revenueByTrainer = Payment::where('payments.status', 'paid')
            ->whereBetween('payments.created_at', [$startDate, $endDate])
            ->join('bookings', 'payments.booking_id', '=', 'bookings.id')
            ->join('schedules', 'bookings.schedule_id', '=', 'schedules.id')
            ->join('trainers', 'schedules.trainer_id', '=', 'trainers.id')
            ->join('users', 'trainers.user_id', '=', 'users.id')
            ->select(
                'users.name',
                DB::raw('SUM(payments.amount) as actual_revenue'),
                DB::raw('SUM(schedules.price) as potential_revenue'),
                DB::raw('SUM(schedules.price - payments.amount) as total_discounts'),
                DB::raw('COUNT(DISTINCT payments.id) as payment_count')
            )
            ->groupBy('trainers.id', 'users.name')
            ->get();

        // Get daily revenue for chart with discount information
        $dailyRevenue = Payment::where('payments.status', 'paid')
            ->whereBetween('payments.created_at', [$startDate, $endDate])
            ->join('bookings', 'payments.booking_id', '=', 'bookings.id')
            ->join('schedules', 'bookings.schedule_id', '=', 'schedules.id')
            ->select(
                DB::raw('DATE(payments.created_at) as date'),
                DB::raw('SUM(payments.amount) as actual_revenue'),
                DB::raw('SUM(schedules.price) as potential_revenue'),
                DB::raw('SUM(schedules.price - payments.amount) as total_discounts'),
                DB::raw('COUNT(DISTINCT payments.id) as payment_count')
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

        $recentPayments = Payment::with(['booking.user', 'booking.schedule.trainer.user'])
            ->latest()
            ->take(5)
            ->get();

        // Get recent recommendations with responses
        try {
            $recentRecommendations = \App\Models\Recommendation::with(['child', 'trainer', 'responses'])
                ->latest()
                ->take(5)
                ->get();
            \Log::info('Recent recommendations loaded successfully', ['count' => $recentRecommendations->count()]);
        } catch (\Exception $e) {
            \Log::error('Error loading recent recommendations: ' . $e->getMessage());
            $recentRecommendations = collect(); // Fallback to empty collection
        }

        return view('admin.dashboard', compact(
            'startDate',
            'endDate',
            'totalBookings',
            'totalRevenue',
            'potentialRevenue',
            'totalDiscounts',
            'realizedRevenue',
            'unrealizedRevenue',
            'totalUsers',
            'totalTrainers',
            'totalSchedules',
            'totalCategories',
            'dateRangeBookings',
            'dateRangeRevenue',
            'dateRangePotentialRevenue',
            'dateRangeDiscounts',
            'dateRangeUsers',
            'bookingStats',
            'paymentStats',
            'revenueByCategory',
            'revenueByTrainer',
            'dailyRevenue',
            'dailyBookings',
            'recentBookings',
            'recentPayments',
            'recentRecommendations'
        ));
    }
}
