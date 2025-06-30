<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Models\User;
use App\Models\Trainer;
use App\Models\Category;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ScheduleController extends Controller
{
    public function index(Request $request)
    {
        $query = Schedule::with(['trainer.user']);

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

        // Apply status filter if provided
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Apply type filter if provided
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $schedules = $query->latest()->paginate(10);

        // Stat cards
        $totalSchedules = Schedule::count();
        $activeSchedules = Schedule::where('status', '=', 'active')->count();
        $inactiveSchedules = Schedule::where('status', 'inactive')->count();
        $upcomingSchedules = Schedule::whereDate('start_date', '>', now())->count();

        // Get trainers for filter
        $trainers = Trainer::with('user')->where('is_active', true)->get();

        return view('admin.schedules.index', compact(
            'schedules',
            'totalSchedules',
            'activeSchedules',
            'inactiveSchedules',
            'upcomingSchedules',
            'trainers'
        ));
    }

    public function create()
    {
        $trainers = Trainer::with('user')->where('is_active', true)->get();
        $categories = Category::all();
        return view('admin.schedules.create', compact('trainers', 'categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'trainer_id' => 'required|exists:trainers,id',
            'category_id' => 'required|exists:categories,id',
            'type' => 'required|string|in:group,private',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
            'price' => 'required|numeric|min:0',
            'max_participants' => 'required|integer|min:1',
            'is_featured' => 'boolean',
            'status' => 'required|string',
            'allow_unlimited_bookings' => 'boolean',
        ]);

        // Handle checkbox value
        $validated['allow_unlimited_bookings'] = $request->has('allow_unlimited_bookings');

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('schedules', 'public');
        }

        Schedule::create($validated);

        return redirect()->route('admin.schedules.index')
            ->with('success', 'Schedule created successfully.');
    }

    public function edit(Schedule $schedule)
    {
        $trainers = Trainer::with('user')->where('is_active', true)->get();
        $categories = Category::all();
        return view('admin.schedules.edit', compact('schedule', 'trainers', 'categories'));
    }

    public function update(Request $request, Schedule $schedule)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'trainer_id' => 'required|exists:trainers,id',
            'category_id' => 'required|exists:categories,id',
            'type' => 'required|string|in:group,private',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
            'price' => 'required|numeric|min:0',
            'max_participants' => 'required|integer|min:1',
            'is_featured' => 'boolean',
            'status' => 'required|string',
            'allow_unlimited_bookings' => 'boolean',
        ]);

        // Handle checkbox value
        $validated['allow_unlimited_bookings'] = $request->has('allow_unlimited_bookings');

        if ($request->hasFile('photo')) {
            // Delete old photo if exists
            if ($schedule->photo) {
                Storage::disk('public')->delete($schedule->photo);
            }
            $validated['photo'] = $request->file('photo')->store('schedules', 'public');
        }

        $schedule->update($validated);

        return redirect()->route('admin.schedules.index')
            ->with('success', 'Schedule updated successfully.');
    }

    public function destroy(Schedule $schedule)
    {
        $schedule->delete();

        return redirect()
            ->route('admin.schedules.index')
            ->with('success', 'Schedule deleted successfully.');
    }
} 