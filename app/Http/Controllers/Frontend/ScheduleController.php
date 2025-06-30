<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Models\Category;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    /**
     * Display a listing of the schedules.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = Schedule::with(['category', 'trainer.user'])
            ->where('status', '=', 'active');

        // Filter by category if provided
        if ($request->has('category')) {
            $query->where('category_id', $request->category);
        }

        // Filter by type if provided
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        // Filter by trainer if provided
        if ($request->has('trainer')) {
            $query->where('trainer_id', $request->trainer);
        }

        // Filter by date range if provided
        if ($request->has('start_date')) {
            $query->where('start_date', '>=', $request->start_date);
        }
        if ($request->has('end_date')) {
            $query->where('end_date', '<=', $request->end_date);
        }

        // Get all categories for filter
        $categories = Category::all();

        // Get schedules with pagination
        $schedules = $query->latest()->paginate(12);

        foreach ($schedules as $schedule) {
            $schedule->bookings_count = $schedule->bookings->where(['status', 'confirmed', 'is_paid' => true, 'payment_status' => 'paid'])->count();
        }

        return view('frontend.schedules.index', compact('schedules', 'categories'));
    }

    /**
     * Display the specified schedule.
     *
     * @param Schedule $schedule
     * @return \Illuminate\View\View
     */
    public function show(Schedule $schedule)
    {
        // Check if user is admin, if not, only allow access to active schedules
        if (!auth()->user()->hasRole('Admin') && $schedule->status !== 'active') {
            abort(404, 'Schedule not found.');
        }

        // Load relationships
        $schedule->load(['category', 'trainer.user', 'sessions']);

        // Get related schedules
        $relatedSchedules = Schedule::where('category_id', $schedule->category_id)
            ->where('id', '!=', $schedule->id)
            ->where('status', '=', 'active')
            ->take(3)
            ->get();

        return view('frontend.schedules.show', compact('schedule', 'relatedSchedules'));
    }
} 