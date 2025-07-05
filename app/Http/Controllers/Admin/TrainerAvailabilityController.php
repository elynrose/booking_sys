<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TrainerAvailability;
use App\Models\Schedule;
use App\Models\Trainer;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TrainerAvailabilityController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $schedules = Schedule::with(['trainer.user', 'category'])
            ->where('status', 'active')
            ->orderBy('title')
            ->get();

        return view('admin.trainer-availability.index', compact('schedules'));
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
    public function store(Request $request, Schedule $schedule)
    {
        // Debug: Log the incoming request
        \Log::info('Store availability request', [
            'request_data' => $request->all(),
            'schedule_id' => $schedule->id,
            'method' => $request->method()
        ]);

        try {
            $request->validate([
                'dates' => 'required|array',
                'dates.*' => 'date',
                'start_time' => 'required',
                'end_time' => 'required',
                'status' => 'required|in:available,unavailable,booked,cancelled',
                'notes' => 'nullable|string'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation failed', [
                'errors' => $e->errors(),
                'request_data' => $request->all()
            ]);
            throw $e;
        }

        $trainer = $schedule->trainer;
        
        \Log::info('Creating availabilities', [
            'trainer_id' => $trainer->id,
            'schedule_id' => $schedule->id,
            'dates' => $request->dates
        ]);
        
        foreach ($request->dates as $date) {
            // Convert time values to proper datetime format
            $startTime = Carbon::parse($date . ' ' . $request->start_time)->format('Y-m-d H:i:s');
            $endTime = Carbon::parse($date . ' ' . $request->end_time)->format('Y-m-d H:i:s');
            
            \Log::info('Creating availability for date', [
                'date' => $date,
                'start_time' => $startTime,
                'end_time' => $endTime
            ]);
            
            TrainerAvailability::updateOrCreate(
                [
                    'trainer_id' => $trainer->id,
                    'schedule_id' => $schedule->id,
                    'date' => $date
                ],
                [
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'status' => $request->status,
                    'notes' => $request->notes
                ]
            );
        }

        \Log::info('Availabilities created successfully');

        return response()->json(['success' => true]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Schedule $schedule)
    {
        $trainer = $schedule->trainer;
        $availabilities = $schedule->availabilities()
            ->where('trainer_id', $trainer->id)
            ->orderBy('date')
            ->get();

        return view('admin.trainer-availability.show', compact('schedule', 'trainer', 'availabilities'));
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
    public function update(Request $request, TrainerAvailability $availability)
    {
        // Debug: Log the incoming request
        \Log::info('Update availability request', [
            'request_data' => $request->all(),
            'availability_id' => $availability->id,
            'method' => $request->method()
        ]);

        $request->validate([
            'status' => 'required|in:available,unavailable,booked,cancelled',
            'start_time' => 'required',
            'end_time' => 'required',
            'notes' => 'nullable|string'
        ]);

        // Convert time values to proper datetime format
        $startTime = Carbon::parse($request->start_time)->format('Y-m-d H:i:s');
        $endTime = Carbon::parse($request->end_time)->format('Y-m-d H:i:s');

        \Log::info('Updating availability', [
            'availability_id' => $availability->id,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'status' => $request->status
        ]);

        $availability->update([
            'status' => $request->status,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'notes' => $request->notes
        ]);

        \Log::info('Availability updated successfully', [
            'availability_id' => $availability->id
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TrainerAvailability $availability)
    {
        // Debug: Log the destroy request
        \Log::info('Destroy availability request', [
            'availability_id' => $availability->id,
            'availability_date' => $availability->date,
            'method' => request()->method()
        ]);
        
        $availability->delete();
        
        \Log::info('Availability destroyed successfully', [
            'availability_id' => $availability->id
        ]);
        
        return response()->json(['success' => true]);
    }

    public function calendar(Schedule $schedule)
    {
        $trainer = $schedule->trainer;
        $month = request('month', Carbon::now()->format('Y-m'));
        
        $startOfMonth = Carbon::parse($month . '-01');
        $endOfMonth = $startOfMonth->copy()->endOfMonth();
        
        $availabilities = $schedule->availabilities()
            ->where('trainer_id', $trainer->id)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->get()
            ->keyBy(function($availability) {
                return $availability->date->format('Y-m-d');
            });
            
        // Debug: Log the availabilities being loaded
        \Log::info('Calendar availabilities loaded', [
            'schedule_id' => $schedule->id,
            'trainer_id' => $trainer->id,
            'month' => $month,
            'start_of_month' => $startOfMonth->format('Y-m-d'),
            'end_of_month' => $endOfMonth->format('Y-m-d'),
            'total_availabilities' => $availabilities->count(),
            'availability_keys' => $availabilities->keys()->toArray(),
            'availability_details' => $availabilities->map(function($a) {
                return [
                    'id' => $a->id,
                    'date' => $a->date->format('Y-m-d'),
                    'status' => $a->status,
                    'notes' => $a->notes
                ];
            })->toArray()
        ]);

        return view('admin.trainer-availability.calendar', compact('schedule', 'trainer', 'availabilities', 'month', 'startOfMonth'));
    }

    public function bulkUpdate(Request $request, Schedule $schedule)
    {
        $request->validate([
            'dates' => 'required|array',
            'dates.*' => 'date',
            'status' => 'required|in:available,unavailable,cancelled'
        ]);

        $trainer = $schedule->trainer;
        
        TrainerAvailability::bulkUpdateStatus(
            $schedule->id,
            $trainer->id,
            $request->dates,
            $request->status
        );

        return response()->json(['success' => true]);
    }

    public function createRecurring(Request $request, Schedule $schedule)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'days_of_week' => 'required|array',
            'days_of_week.*' => 'integer|between:0,6',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time'
        ]);

        $trainer = $schedule->trainer;
        
        TrainerAvailability::createRecurringAvailability(
            $schedule->id,
            $trainer->id,
            $request->start_date,
            $request->end_date,
            $request->days_of_week,
            $request->start_time,
            $request->end_time
        );

        return redirect()->back()->with('success', 'Recurring availability created successfully!');
    }

    public function export(Schedule $schedule)
    {
        $trainer = $schedule->trainer;
        $availabilities = $schedule->availabilities()
            ->where('trainer_id', $trainer->id)
            ->orderBy('date')
            ->get();

        $filename = "availability_{$schedule->slug}_{$trainer->user->name}_" . Carbon::now()->format('Y-m-d') . ".csv";
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($availabilities) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Date', 'Start Time', 'End Time', 'Status', 'Notes']);
            
            foreach ($availabilities as $availability) {
                fputcsv($file, [
                    $availability->date->format('Y-m-d'),
                    $availability->start_time->format('H:i'),
                    $availability->end_time->format('H:i'),
                    $availability->status,
                    $availability->notes
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Return all availabilities for a given trainer as JSON (for AJAX)
     */
    public function getTrainerAvailabilities(Request $request)
    {
        $trainerId = $request->input('trainer_id');
        if (!$trainerId) {
            return response()->json(['error' => 'No trainer_id provided'], 400);
        }
        $availabilities = TrainerAvailability::where('trainer_id', $trainerId)
            ->orderBy('date')
            ->orderBy('start_time')
            ->get()
            ->map(function($a) {
                return [
                    'id' => $a->id,
                    'date' => $a->date,
                    'start_time' => $a->start_time,
                    'end_time' => $a->end_time,
                    'status' => $a->status,
                    'notes' => $a->notes
                ];
            });
        return response()->json(['availabilities' => $availabilities]);
    }
}
