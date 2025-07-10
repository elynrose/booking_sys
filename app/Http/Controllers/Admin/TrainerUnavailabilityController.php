<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TrainerUnavailability;
use App\Models\Trainer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Gate;
use Symfony\Component\HttpFoundation\Response;

class TrainerUnavailabilityController extends Controller
{
    public function index()
    {
        abort_if(Gate::denies('trainer_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $unavailabilities = TrainerUnavailability::with(['trainer.user'])
            ->orderBy('date', 'desc')
            ->paginate(20);

        return view('admin.trainer-unavailability.index', compact('unavailabilities'));
    }

    public function create()
    {
        abort_if(Gate::denies('trainer_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $trainers = Trainer::with('user')->get();

        return view('admin.trainer-unavailability.create', compact('trainers'));
    }

    public function store(Request $request)
    {
        abort_if(Gate::denies('trainer_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $request->validate([
            'trainer_id' => 'required|exists:trainers,id',
            'date' => 'required|date',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i',
            'notes' => 'nullable|string|max:500'
        ]);

        // Check if unavailability already exists for this trainer and date
        $existing = TrainerUnavailability::where('trainer_id', $request->trainer_id)
            ->where('date', $request->date)
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'Unavailability already exists for this date'
            ], 400);
        }

        $unavailability = TrainerUnavailability::create([
            'trainer_id' => $request->trainer_id,
            'date' => $request->date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'notes' => $request->notes
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Unavailability created successfully'
        ]);
    }

    public function show(TrainerUnavailability $unavailability)
    {
        abort_if(Gate::denies('trainer_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.trainer-unavailability.show', compact('unavailability'));
    }

    public function edit(TrainerUnavailability $unavailability)
    {
        abort_if(Gate::denies('trainer_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $trainers = Trainer::with('user')->get();

        return view('admin.trainer-unavailability.edit', compact('unavailability', 'trainers'));
    }

    public function update(Request $request, TrainerUnavailability $unavailability)
    {
        abort_if(Gate::denies('trainer_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $request->validate([
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i',
            'notes' => 'nullable|string|max:500'
        ]);

        $unavailability->update([
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'notes' => $request->notes
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Unavailability updated successfully'
        ]);
    }

    public function destroy($id)
    {
        try {
            $unavailability = TrainerUnavailability::findOrFail($id);
            
            abort_if(Gate::denies('trainer_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

            $unavailability->delete();

            return response()->json([
                'success' => true,
                'message' => 'Unavailability deleted successfully'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error deleting unavailability: ' . $e->getMessage());
            throw $e;
        }
    }

    public function bulkCreate(Request $request)
    {
        abort_if(Gate::denies('trainer_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $request->validate([
            'trainer_id' => 'required|exists:trainers,id',
            'dates' => 'required|array|min:1',
            'dates.*' => 'date',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i',
            'notes' => 'nullable|string|max:500'
        ]);

        $created = 0;
        $skipped = 0;

        foreach ($request->dates as $date) {
            // Check if unavailability already exists
            $existing = TrainerUnavailability::where('trainer_id', $request->trainer_id)
                ->where('date', $date)
                ->first();

            if (!$existing) {
                TrainerUnavailability::create([
                    'trainer_id' => $request->trainer_id,
                    'date' => $date,
                    'start_time' => $request->start_time,
                    'end_time' => $request->end_time,
                    'notes' => $request->notes
                ]);
                $created++;
            } else {
                $skipped++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Created {$created} unavailabilities, skipped {$skipped} existing ones"
        ]);
    }
} 