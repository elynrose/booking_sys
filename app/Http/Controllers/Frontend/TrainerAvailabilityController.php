<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TrainerAvailability;
use App\Models\Schedule;
use App\Models\Trainer;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Gate;
use Symfony\Component\HttpFoundation\Response;

class TrainerAvailabilityController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:Trainer');
    }

    /**
     * Display a listing of the trainer's availability.
     */
    public function index()
    {
        $user = Auth::user();
        $trainer = Trainer::where('user_id', $user->id)->first();
        
        if (!$trainer) {
            return redirect()->route('frontend.home')
                ->with('error', 'Trainer profile not found.');
        }

        $availabilities = TrainerAvailability::where('trainer_id', $trainer->id)
            ->orderBy('date', 'asc')
            ->orderBy('start_time', 'asc')
            ->paginate(15);

        return view('frontend.trainer.availability.index', compact('availabilities', 'trainer'));
    }

    /**
     * Show the form for creating a new availability.
     */
    public function create()
    {
        $user = Auth::user();
        $trainer = Trainer::where('user_id', $user->id)->first();
        
        if (!$trainer) {
            return redirect()->route('frontend.home')
                ->with('error', 'Trainer profile not found.');
        }

        $schedules = Schedule::where('trainer_id', $trainer->id)
            ->where('status', 'active')
            ->get();

        return view('frontend.trainer.availability.create', compact('trainer', 'schedules'));
    }

    /**
     * Store a newly created availability in storage.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $trainer = Trainer::where('user_id', $user->id)->first();
        
        if (!$trainer) {
            return redirect()->route('frontend.home')
                ->with('error', 'Trainer profile not found.');
        }

        $request->validate([
            'date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'status' => 'required|in:available,unavailable,busy',
            'notes' => 'nullable|string|max:500',
            'schedule_id' => 'nullable|exists:schedules,id'
        ]);

        TrainerAvailability::create([
            'trainer_id' => $trainer->id,
            'date' => $request->date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'status' => $request->status,
            'notes' => $request->notes,
            'schedule_id' => $request->schedule_id
        ]);

        return redirect()->route('frontend.trainer.availability.index')
            ->with('success', 'Availability created successfully.');
    }

    /**
     * Display the specified availability.
     */
    public function show(TrainerAvailability $availability)
    {
        $user = Auth::user();
        $trainer = Trainer::where('user_id', $user->id)->first();
        
        if (!$trainer || $availability->trainer_id !== $trainer->id) {
            abort(403, 'Unauthorized action.');
        }

        return view('frontend.trainer.availability.show', compact('availability'));
    }

    /**
     * Show the form for editing the specified availability.
     */
    public function edit(TrainerAvailability $availability)
    {
        $user = Auth::user();
        $trainer = Trainer::where('user_id', $user->id)->first();
        
        if (!$trainer || $availability->trainer_id !== $trainer->id) {
            abort(403, 'Unauthorized action.');
        }

        $schedules = Schedule::where('trainer_id', $trainer->id)
            ->where('status', 'active')
            ->get();

        return view('frontend.trainer.availability.edit', compact('availability', 'trainer', 'schedules'));
    }

    /**
     * Update the specified availability in storage.
     */
    public function update(Request $request, TrainerAvailability $availability)
    {
        $user = Auth::user();
        $trainer = Trainer::where('user_id', $user->id)->first();
        
        if (!$trainer || $availability->trainer_id !== $trainer->id) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'status' => 'required|in:available,unavailable,busy',
            'notes' => 'nullable|string|max:500',
            'schedule_id' => 'nullable|exists:schedules,id'
        ]);

        $availability->update($request->all());

        return redirect()->route('frontend.trainer.availability.index')
            ->with('success', 'Availability updated successfully.');
    }

    /**
     * Remove the specified availability from storage.
     */
    public function destroy(TrainerAvailability $availability)
    {
        $user = Auth::user();
        $trainer = Trainer::where('user_id', $user->id)->first();
        
        if (!$trainer || $availability->trainer_id !== $trainer->id) {
            abort(403, 'Unauthorized action.');
        }

        $availability->delete();

        return redirect()->route('frontend.trainer.availability.index')
            ->with('success', 'Availability deleted successfully.');
    }

    /**
     * Show calendar view of availability.
     */
    public function calendar()
    {
        $user = Auth::user();
        $trainer = Trainer::where('user_id', $user->id)->first();
        
        if (!$trainer) {
            return redirect()->route('frontend.home')
                ->with('error', 'Trainer profile not found.');
        }

        $month = request('month', Carbon::now()->format('Y-m'));
        $date = Carbon::parse($month . '-01');
        
        $availabilities = TrainerAvailability::where('trainer_id', $trainer->id)
            ->whereYear('date', $date->year)
            ->whereMonth('date', $date->month)
            ->get()
            ->keyBy(function($item) { return \Carbon\Carbon::parse($item->date)->format('Y-m-d'); });

        return view('frontend.trainer.availability.calendar', compact('trainer', 'availabilities', 'date'));
    }

    /**
     * Bulk update availability for multiple dates.
     */
    public function bulkUpdate(Request $request)
    {
        $user = Auth::user();
        $trainer = Trainer::where('user_id', $user->id)->first();
        
        if (!$trainer) {
            return redirect()->route('frontend.home')
                ->with('error', 'Trainer profile not found.');
        }

        $request->validate([
            'dates' => 'required|array',
            'dates.*' => 'date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'status' => 'required|in:available,unavailable,busy',
            'notes' => 'nullable|string|max:500',
            'schedule_id' => 'nullable|exists:schedules,id'
        ]);

        foreach ($request->dates as $date) {
            TrainerAvailability::updateOrCreate(
                [
                    'trainer_id' => $trainer->id,
                    'date' => $date
                ],
                [
                    'start_time' => $request->start_time,
                    'end_time' => $request->end_time,
                    'status' => $request->status,
                    'notes' => $request->notes,
                    'schedule_id' => $request->schedule_id
                ]
            );
        }

        return redirect()->route('frontend.trainer.availability.index')
            ->with('success', 'Bulk availability updated successfully.');
    }

    /**
     * Create recurring availability.
     */
    public function createRecurring(Request $request)
    {
        $user = Auth::user();
        $trainer = Trainer::where('user_id', $user->id)->first();
        
        if (!$trainer) {
            return redirect()->route('frontend.home')
                ->with('error', 'Trainer profile not found.');
        }

        $request->validate([
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'days_of_week' => 'required|array',
            'days_of_week.*' => 'integer|between:0,6',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'status' => 'required|in:available,unavailable,busy',
            'notes' => 'nullable|string|max:500',
            'schedule_id' => 'nullable|exists:schedules,id'
        ]);

        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);
        $currentDate = $startDate->copy();

        while ($currentDate <= $endDate) {
            if (in_array($currentDate->dayOfWeek, $request->days_of_week)) {
                TrainerAvailability::updateOrCreate(
                    [
                        'trainer_id' => $trainer->id,
                        'date' => $currentDate->format('Y-m-d')
                    ],
                    [
                        'start_time' => $request->start_time,
                        'end_time' => $request->end_time,
                        'status' => $request->status,
                        'notes' => $request->notes,
                        'schedule_id' => $request->schedule_id
                    ]
                );
            }
            $currentDate->addDay();
        }

        return redirect()->route('frontend.trainer.availability.index')
            ->with('success', 'Recurring availability created successfully.');
    }
} 