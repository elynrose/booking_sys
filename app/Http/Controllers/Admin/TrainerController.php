<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Trainer;
use App\Models\User;
use App\Models\Schedule;
use App\Models\Role;
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

        $users = User::whereDoesntHave('trainer')
            ->whereDoesntHave('roles', function($query) {
                $query->where('title', 'trainer');
            })
            ->get();
        $schedules = Schedule::all();
        return view('admin.trainers.create', compact('users', 'schedules'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        abort_if(Gate::denies('trainer_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'bio' => 'nullable|string',
            'payment_method' => 'required|in:check,paypal,venmo,cashapp',
            'payment_details' => 'required|string',
            'profile_picture' => 'nullable|image|max:2048',
            'schedules' => 'nullable|array',
            'schedules.*' => 'exists:schedules,id'
        ]);

        $data = $request->except('profile_picture', 'schedules');

        if ($request->hasFile('profile_picture')) {
            $data['profile_picture'] = $request->file('profile_picture')->store('trainers', 'public');
        }

        $trainer = Trainer::create($data);

        // Assign trainer role
        $user = User::find($request->user_id);
        $trainerRole = Role::where('title', 'trainer')->first();
        if ($trainerRole) {
            $user->roles()->syncWithoutDetaching([$trainerRole->id]);
        }

        if ($request->has('schedules')) {
            $trainer->schedules()->attach($request->schedules);
        }

        return redirect()->route('admin.trainers.index')
            ->with('success', 'Trainer created successfully.');
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
            'profile_picture' => 'nullable|image|max:2048',
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
}
