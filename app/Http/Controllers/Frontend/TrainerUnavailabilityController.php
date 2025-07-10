<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TrainerUnavailability;
use App\Models\Schedule;
use App\Models\Trainer;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Gate;
use Symfony\Component\HttpFoundation\Response;

class TrainerUnavailabilityController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:Trainer');
    }

    /**
     * Display a listing of the trainer's unavailability periods.
     */
    public function index()
    {
        $user = Auth::user();
        $trainer = Trainer::where('user_id', $user->id)->first();
        
        if (!$trainer) {
            return redirect()->route('frontend.home')
                ->with('error', 'Trainer profile not found.');
        }

        $unavailabilities = TrainerUnavailability::where('trainer_id', $user->id)
            ->orderBy('date', 'desc')
            ->orderBy('start_time', 'asc')
            ->paginate(15);

        return view('frontend.trainer.unavailability.index', compact('unavailabilities', 'trainer'));
    }

    /**
     * Show the form for creating a new unavailability period.
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

        return view('frontend.trainer.unavailability.create', compact('trainer', 'schedules'));
    }

    /**
     * Store a newly created unavailability period in storage.
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
            'date' => 'required|date',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i|after:start_time',
            'reason' => 'required|in:personal,sick,vacation,other',
            'notes' => 'nullable|string|max:500',
            'schedule_id' => 'nullable|exists:schedules,id',
            'is_all_day' => 'boolean'
        ]);

        // If it's all day, set times to null
        if ($request->is_all_day) {
            $startTime = null;
            $endTime = null;
        } else {
            $startTime = $request->start_time;
            $endTime = $request->end_time;
        }

        TrainerUnavailability::create([
            'trainer_id' => $user->id,
            'schedule_id' => $request->schedule_id,
            'date' => $request->date,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'reason' => $request->reason,
            'notes' => $request->notes
        ]);

        return redirect()->route('frontend.trainer.unavailability.index')
            ->with('success', 'Unavailability period created successfully.');
    }

    /**
     * Show the form for editing the specified unavailability period.
     */
    public function edit(TrainerUnavailability $unavailability)
    {
        $user = Auth::user();
        $trainer = Trainer::where('user_id', $user->id)->first();
        
        if (!$trainer || $unavailability->trainer_id !== $user->id) {
            abort(403, 'Unauthorized action.');
        }

        $schedules = Schedule::where('trainer_id', $trainer->id)
            ->where('status', 'active')
            ->get();

        return view('frontend.trainer.unavailability.edit', compact('unavailability', 'trainer', 'schedules'));
    }

    /**
     * Update the specified unavailability period in storage.
     */
    public function update(Request $request, TrainerUnavailability $unavailability)
    {
        $user = Auth::user();
        $trainer = Trainer::where('user_id', $user->id)->first();
        
        if (!$trainer || $unavailability->trainer_id !== $user->id) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'date' => 'required|date',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i|after:start_time',
            'reason' => 'required|in:personal,sick,vacation,other',
            'notes' => 'nullable|string|max:500',
            'schedule_id' => 'nullable|exists:schedules,id',
            'is_all_day' => 'boolean'
        ]);

        // If it's all day, set times to null
        if ($request->is_all_day) {
            $startTime = null;
            $endTime = null;
        } else {
            $startTime = $request->start_time;
            $endTime = $request->end_time;
        }

        $unavailability->update([
            'schedule_id' => $request->schedule_id,
            'date' => $request->date,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'reason' => $request->reason,
            'notes' => $request->notes
        ]);

        return redirect()->route('frontend.trainer.unavailability.index')
            ->with('success', 'Unavailability period updated successfully.');
    }

    /**
     * Remove the specified unavailability period from storage.
     */
    public function destroy(TrainerUnavailability $unavailability)
    {
        $user = Auth::user();
        
        if ($unavailability->trainer_id !== $user->id) {
            abort(403, 'Unauthorized action.');
        }

        $unavailability->delete();

        return redirect()->route('frontend.trainer.unavailability.index')
            ->with('success', 'Unavailability period deleted successfully.');
    }

    /**
     * Bulk create unavailability periods.
     */
    public function bulkCreate(Request $request)
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
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i|after:start_time',
            'reason' => 'required|in:personal,sick,vacation,other',
            'notes' => 'nullable|string|max:500',
            'schedule_id' => 'nullable|exists:schedules,id',
            'is_all_day' => 'boolean'
        ]);

        // If it's all day, set times to null
        if ($request->is_all_day) {
            $startTime = null;
            $endTime = null;
        } else {
            $startTime = $request->start_time;
            $endTime = $request->end_time;
        }

        foreach ($request->dates as $date) {
            TrainerUnavailability::updateOrCreate(
                [
                    'trainer_id' => $user->id,
                    'schedule_id' => $request->schedule_id,
                    'date' => $date
                ],
                [
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'reason' => $request->reason,
                    'notes' => $request->notes
                ]
            );
        }

        return redirect()->route('frontend.trainer.unavailability.index')
            ->with('success', 'Bulk unavailability periods created successfully.');
    }

    /**
     * Show trainer's default availability settings.
     */
    public function settings()
    {
        $user = Auth::user();
        $trainer = Trainer::where('user_id', $user->id)->first();
        
        if (!$trainer) {
            return redirect()->route('frontend.home')
                ->with('error', 'Trainer profile not found.');
        }

        return view('frontend.trainer.unavailability.settings', compact('trainer'));
    }

    /**
     * Update trainer's default availability settings.
     */
    public function updateSettings(Request $request)
    {
        $user = Auth::user();
        $trainer = Trainer::where('user_id', $user->id)->first();
        
        if (!$trainer) {
            return redirect()->route('frontend.home')
                ->with('error', 'Trainer profile not found.');
        }

        $request->validate([
            'is_available_by_default' => 'boolean',
            'default_start_time' => 'nullable|date_format:H:i',
            'default_end_time' => 'nullable|date_format:H:i|after:default_start_time',
            'default_available_days' => 'array',
            'default_available_days.*' => 'integer|between:0,6'
        ]);

        $trainer->update([
            'is_available_by_default' => $request->is_available_by_default ?? true,
            'default_start_time' => $request->default_start_time,
            'default_end_time' => $request->default_end_time,
            'default_available_days' => $request->default_available_days ?? [0, 1, 2, 3, 4, 5, 6]
        ]);

        return redirect()->route('frontend.trainer.unavailability.settings')
            ->with('success', 'Default availability settings updated successfully.');
    }
} 