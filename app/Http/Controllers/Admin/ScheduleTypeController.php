<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ScheduleType;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ScheduleTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $scheduleTypes = ScheduleType::ordered()->paginate(10);
        return view('admin.schedule-types.index', compact('scheduleTypes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.schedule-types.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:schedule_types',
            'description' => 'nullable|string|max:1000',
            'icon' => 'nullable|string|max:100',
            'color' => 'nullable|string|max:7',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0'
        ]);

        ScheduleType::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'icon' => $request->icon,
            'color' => $request->color ?? '#007bff',
            'is_active' => $request->has('is_active'),
            'sort_order' => $request->sort_order ?? 0
        ]);

        return redirect()->route('admin.schedule-types.index')
            ->with('success', 'Schedule type created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(ScheduleType $scheduleType)
    {
        $schedules = $scheduleType->schedules()->with(['trainer.user', 'category'])->paginate(10);
        return view('admin.schedule-types.show', compact('scheduleType', 'schedules'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ScheduleType $scheduleType)
    {
        return view('admin.schedule-types.edit', compact('scheduleType'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ScheduleType $scheduleType)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:schedule_types,name,' . $scheduleType->id,
            'description' => 'nullable|string|max:1000',
            'icon' => 'nullable|string|max:100',
            'color' => 'nullable|string|max:7',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0'
        ]);

        $scheduleType->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'icon' => $request->icon,
            'color' => $request->color ?? '#007bff',
            'is_active' => $request->has('is_active'),
            'sort_order' => $request->sort_order ?? 0
        ]);

        return redirect()->route('admin.schedule-types.index')
            ->with('success', 'Schedule type updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ScheduleType $scheduleType)
    {
        // Check if there are schedules using this type
        if ($scheduleType->schedules()->count() > 0) {
            return redirect()->route('admin.schedule-types.index')
                ->with('error', 'Cannot delete schedule type. There are schedules using this type.');
        }

        $scheduleType->delete();

        return redirect()->route('admin.schedule-types.index')
            ->with('success', 'Schedule type deleted successfully.');
    }

    /**
     * Toggle the active status of a schedule type
     */
    public function toggleStatus(ScheduleType $scheduleType)
    {
        $scheduleType->update(['is_active' => !$scheduleType->is_active]);

        return redirect()->route('admin.schedule-types.index')
            ->with('success', 'Schedule type status updated successfully.');
    }
}
