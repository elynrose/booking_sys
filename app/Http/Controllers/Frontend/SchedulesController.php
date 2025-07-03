<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Models\Trainer;
use App\Models\Category;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Gate;
use Symfony\Component\HttpFoundation\Response;

class SchedulesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        abort_if(Gate::denies('schedule_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $query = Schedule::with(['trainer.user', 'bookings', 'category'])
            ->where('status', '=', 'active');

        // Apply date filters if provided
        if ($request->filled('start_date')) {
            $query->whereDate('start_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('end_date', '<=', $request->end_date);
        }

        // Apply trainer filter if provided
        if ($request->filled('trainer_id')) {
            $query->where('trainer_id', $request->trainer_id);
        }

        // Apply category filter if provided
        if ($request->filled('category')) {
            $query->whereHas('category', function($q) use ($request) {
                $q->where('slug', '=', $request->category);
            });
        }

        // Apply type filter if provided
        if ($request->filled('type')) {
            $query->where('type', '=', $request->type);
        }

        // Apply age group filter if provided
        if ($request->filled('age_group')) {
            $query->where('age_group', '=', $request->age_group);
        }

        // Apply day filter if provided
        if ($request->filled('day')) {
            $query->whereRaw('LOWER(DAYNAME(start_date)) = ?', [strtolower($request->day)]);
        }

        $schedules = $query->latest()->paginate(9)->withQueryString();

        // Get trainers for filter
        $trainers = Trainer::with('user')
            ->where('is_active', true)
            ->get();

        // Get categories for filter
        $categories = Category::all();

        return view('frontend.schedules.index', compact('schedules', 'trainers', 'categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Schedule $schedule)
    {
        abort_if(Gate::denies('schedule_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        // Check if user is admin, if not, only allow access to active schedules
        if (!auth()->user()->hasRole('Admin') && $schedule->status !== 'active') {
            abort(404, 'Schedule not found.');
        }

        $schedule->load(['trainer.user', 'bookings.user']);

        return view('frontend.schedules.show', compact('schedule'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function featured()
    {
        abort_if(Gate::denies('schedule_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $schedules = Schedule::with(['trainer.user', 'bookings'])
            ->where('status', '=', 'active')
            ->where('is_featured', true)
            ->where('start_date', '>=', Carbon::now()->toDateTimeString())
            ->latest()
            ->take(6)
            ->get();

        return view('frontend.schedules.featured', compact('schedules'));
    }

    public function trainerSchedules(Trainer $trainer)
    {
        abort_if(Gate::denies('schedule_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $schedules = Schedule::with(['trainer.user', 'bookings'])
            ->where('trainer_id', $trainer->id)
            ->where('status', '=', 'active')
            ->where('start_date', '>=', Carbon::now()->toDateTimeString())
            ->latest()
            ->paginate(10);

        return view('frontend.schedules.trainer', compact('schedules', 'trainer'));
    }
}
