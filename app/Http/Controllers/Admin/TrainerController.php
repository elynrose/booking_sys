<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Trainer;
use App\Models\User;
use App\Models\Schedule;
use App\Models\Role;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Child;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Gate;
use Symfony\Component\HttpFoundation\Response;

class TrainerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        abort_if(Gate::denies('trainer_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $trainers = Trainer::with(['user', 'schedules'])->get();
        return view('admin.trainers.index', compact('trainers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        abort_if(Gate::denies('trainer_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        // Get users who don't already have a trainer profile
        $users = User::whereDoesntHave('trainer')->get();
        
        // If the current user is admin, they should be able to see all users
        // If they're not admin, filter out users who already have trainer role
        if (!auth()->user()->hasRole('Admin')) {
            $users = $users->whereDoesntHave('roles', function($query) {
                $query->where('title', 'Trainer');
            });
        }
        
        $schedules = Schedule::all();
        return view('admin.trainers.create', compact('users', 'schedules'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        abort_if(Gate::denies('trainer_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        \Log::info('Trainer creation started', ['request_data' => $request->except('profile_picture')]);

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'bio' => 'nullable|string',
            'payment_method' => 'required|in:check,paypal,venmo,cashapp',
            'payment_details' => 'required|string',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:1024',
            'schedules' => 'nullable|array',
            'schedules.*' => 'exists:schedules,id'
        ]);

        try {
            \Log::info('Validation passed, creating trainer data');
            
            $data = $request->except('profile_picture', 'schedules');
            
            // Set default is_active to true
            $data['is_active'] = true;

            \Log::info('Trainer data prepared', ['data' => $data]);

            // Handle profile picture upload more safely
            if ($request->hasFile('profile_picture') && $request->file('profile_picture')->isValid()) {
                try {
                    $data['profile_picture'] = $request->file('profile_picture')->store('trainers', 'public');
                    \Log::info('Profile picture uploaded successfully', ['path' => $data['profile_picture']]);
                } catch (\Exception $e) {
                    \Log::error('Profile picture upload error: ' . $e->getMessage());
                    // Continue without the profile picture if upload fails
                    $data['profile_picture'] = null;
                }
            }

            \Log::info('Creating trainer record');
            $trainer = Trainer::create($data);
            \Log::info('Trainer created successfully', ['trainer_id' => $trainer->id]);

            // Assign trainer role
            \Log::info('Assigning trainer role');
            $user = User::find($request->user_id);
            $trainerRole = Role::where('name', 'Trainer')->first();
            if ($trainerRole) {
                $user->roles()->syncWithoutDetaching([$trainerRole->id]);
                \Log::info('Trainer role assigned successfully');
            } else {
                \Log::warning('Trainer role not found');
            }

            // Handle schedule assignments
            if ($request->has('schedules') && is_array($request->schedules)) {
                try {
                    $trainer->schedules()->attach($request->schedules);
                    \Log::info('Schedules attached successfully', ['schedules' => $request->schedules]);
                } catch (\Exception $e) {
                    \Log::error('Schedule assignment error: ' . $e->getMessage());
                    // Continue even if schedule assignment fails
                }
            }

            \Log::info('Trainer creation completed successfully');
            return redirect()->route('admin.trainers.index')
                ->with('success', 'Trainer created successfully.');
                
        } catch (\Exception $e) {
            \Log::error('Trainer creation error: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return redirect()->back()
                ->withErrors(['error' => 'Error creating trainer: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Trainer $trainer)
    {
        abort_if(Gate::denies('trainer_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.trainers.show', compact('trainer'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Trainer $trainer)
    {
        abort_if(Gate::denies('trainer_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $schedules = Schedule::all();
        return view('admin.trainers.edit', compact('trainer', 'schedules'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Trainer $trainer)
    {
        abort_if(Gate::denies('trainer_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $request->validate([
            'bio' => 'nullable|string',
            'payment_method' => 'required|in:check,paypal,venmo,cashapp',
            'payment_details' => 'required|string',
            'profile_picture' => 'nullable|image|max:5120',
            'schedules' => 'nullable|array',
            'schedules.*' => 'exists:schedules,id',
            'is_active' => 'boolean'
        ]);

        $data = $request->except('profile_picture', 'schedules');

        if ($request->hasFile('profile_picture')) {
            if ($trainer->profile_picture) {
                Storage::disk('public')->delete($trainer->profile_picture);
            }
            $data['profile_picture'] = $request->file('profile_picture')->store('trainers', 'public');
        }

        $trainer->update($data);

        if ($request->has('schedules')) {
            $trainer->schedules()->sync($request->schedules);
        }

        return redirect()->route('admin.trainers.index')
            ->with('success', 'Trainer updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Trainer $trainer)
    {
        abort_if(Gate::denies('trainer_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        if ($trainer->profile_picture) {
            Storage::disk('public')->delete($trainer->profile_picture);
        }
        
        $trainer->delete();

        return redirect()->route('admin.trainers.index')
            ->with('success', 'Trainer deleted successfully.');
    }

    public function showAssignStudentForm(Trainer $trainer)
    {
        abort_if(Gate::denies('trainer_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        // Get users who have paid for this trainer's sessions
        $paidUsers = User::whereHas('bookings', function($query) use ($trainer) {
            $query->whereHas('schedule', function($scheduleQuery) use ($trainer) {
                $scheduleQuery->where('trainer_id', $trainer->id);
            })->where('is_paid', true);
        })->with(['children', 'bookings.schedule'])->get();

        $schedules = Schedule::where('trainer_id', $trainer->id)->get();

        return view('admin.trainers.assign-student', compact('trainer', 'paidUsers', 'schedules'));
    }

    public function assignStudent(Request $request, Trainer $trainer)
    {
        abort_if(Gate::denies('trainer_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'schedule_id' => 'required|exists:schedules,id',
            'child_ids' => 'required|array|min:1',
            'child_ids.*' => 'exists:children,id',
            'payment_status' => 'required|in:paid,pending'
        ]);

        // Verify the schedule belongs to the trainer
        $schedule = Schedule::where('id', $request->schedule_id)
            ->where('trainer_id', $trainer->id)
            ->firstOrFail();

        // Verify the user has paid for this trainer's sessions
        $hasPaidBooking = Booking::where('user_id', $request->user_id)
            ->whereHas('schedule', function($query) use ($trainer) {
                $query->where('trainer_id', $trainer->id);
            })
            ->where('is_paid', true)
            ->exists();

        if (!$hasPaidBooking) {
            return redirect()->back()
                ->withErrors(['user_id' => 'This user has not paid for any sessions with this trainer'])
                ->withInput();
        }

        $assignedCount = 0;
        $errors = [];

        foreach ($request->child_ids as $childId) {
            // Verify the child belongs to the user
            $child = Child::where('id', $childId)
                ->where('user_id', $request->user_id)
                ->first();

            if (!$child) {
                $errors[] = "Child ID $childId does not belong to the selected user";
                continue;
            }

            // Check if booking already exists
            $existingBooking = Booking::where('user_id', $request->user_id)
                ->where('schedule_id', $request->schedule_id)
                ->where('child_id', $childId)
                ->first();

            if ($existingBooking) {
                $errors[] = "Child $child->name is already assigned to this schedule";
                continue;
            }

            // Create the booking
            $booking = Booking::create([
                'user_id' => $request->user_id,
                'schedule_id' => $request->schedule_id,
                'child_id' => $childId,
                'status' => 'confirmed',
                'is_paid' => $request->payment_status === 'paid',
                'sessions_remaining' => 4, // Default to 4 sessions
                'check_in_code' => strtoupper(substr(md5(uniqid()), 0, 8)), // Generate unique check-in code
                'total_cost' => $schedule->price,
            ]);

            // If payment status is paid, create a payment record
            if ($request->payment_status === 'paid') {
                Payment::create([
                    'user_id' => $request->user_id,
                    'booking_id' => $booking->id,
                    'schedule_id' => $request->schedule_id,
                    'amount' => $schedule->price,
                    'description' => 'Admin assigned payment',
                    'status' => 'paid',
                    'payment_date' => now(),
                    'paid_at' => now(),
                ]);
            }

            $assignedCount++;
        }

        if (!empty($errors)) {
            return redirect()->back()
                ->withErrors($errors)
                ->withInput();
        }

        $message = $assignedCount === 1 
            ? 'Child has been successfully assigned to the schedule.'
            : "$assignedCount children have been successfully assigned to the schedule.";

        return redirect()->route('admin.trainers.index')
            ->with('success', $message);
    }
}
