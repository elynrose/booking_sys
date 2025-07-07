<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Child;
use Illuminate\Http\Request;
use Gate;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Storage;

class ChildController extends Controller
{
    public function index()
    {
        abort_if(Gate::denies('child_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $children = Child::where('user_id', auth()->id())
            ->latest()
            ->paginate(10);

        return view('frontend.children.index', compact('children'));
    }

    public function create()
    {
        abort_if(Gate::denies('child_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('frontend.children.create');
    }

    public function store(Request $request)
    {
        abort_if(Gate::denies('child_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'date_of_birth' => 'required|date',
            'gender' => 'required|in:male,female,other',
            'notes' => 'nullable|string',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'address' => 'nullable|string|max:1000',
            'parent_consent' => 'boolean',
        ]);

        // Handle photo upload
        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $path = $file->store('children/photos', 's3'); // Store on cloud disk
            \Storage::disk('s3')->setVisibility($path, 'public');
            $validated['photo'] = $path;
        }

        $child = Child::create([
            'user_id' => auth()->id(),
            'name' => $validated['name'],
            'date_of_birth' => $validated['date_of_birth'],
            'gender' => $validated['gender'],
            'notes' => $validated['notes'],
            'photo' => $validated['photo'] ?? null,
            'address' => $validated['address'] ?? null,
            'parent_consent' => $validated['parent_consent'] ?? false,
        ]);

        return redirect()->route('frontend.children.index')
            ->with('success', 'Child added successfully.');
    }

    public function show(Child $child)
    {
        abort_if(Gate::denies('child_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        // Check if user owns this child
        if ($child->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        return view('frontend.children.show', compact('child'));
    }

    public function edit(Child $child)
    {
        abort_if(Gate::denies('child_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        // Check if user owns this child
        if ($child->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        return view('frontend.children.edit', compact('child'));
    }

    public function update(Request $request, Child $child)
    {
        abort_if(Gate::denies('child_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        // Check if user owns this child
        if ($child->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'date_of_birth' => 'required|date',
            'gender' => 'required|in:male,female,other',
            'notes' => 'nullable|string',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'address' => 'nullable|string|max:1000',
            'parent_consent' => 'boolean',
        ]);

        // Handle photo upload
        if ($request->hasFile('photo')) {
            // Delete old photo if exists
            if ($child->photo) {
                Storage::disk('s3')->delete($child->photo);
            }
            $file = $request->file('photo');
            $path = $file->store('children/photos', 's3'); // Store on cloud disk
            \Storage::disk('s3')->setVisibility($path, 'public');
            $validated['photo'] = $path;
        }

        $child->update($validated);

        return redirect()->route('frontend.children.index')
            ->with('success', 'Child updated successfully.');
    }

    public function destroy(Child $child)
    {
        abort_if(Gate::denies('child_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        // Check if user owns this child
        if ($child->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        // Delete photo if exists
        if ($child->photo) {
            Storage::disk('s3')->delete($child->photo);
        }

        $child->delete();

        return redirect()->route('frontend.children.index')
            ->with('success', 'Child deleted successfully.');
    }
} 