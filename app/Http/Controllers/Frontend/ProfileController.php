<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdatePasswordRequest;
use App\Http\Requests\UpdateProfileRequest;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function index()
    {
        return view('frontend.profile');
    }



    public function edit()
    {
        abort_if(Gate::denies('profile_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $user = auth()->user();
        return view('frontend.profile.edit', compact('user'));
    }

    public function update(Request $request)
    {
        abort_if(Gate::denies('profile_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $user = auth()->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'phone_number' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'timezone' => 'nullable|string|max:100',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'sms_notifications_enabled' => 'boolean',
            'sms_notification_preferences' => 'array',
            'sms_notification_preferences.*' => 'boolean',
            'current_password' => 'nullable|string',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        if ($request->hasFile('photo')) {
            // Delete old photo if exists
            if ($user->photo) {
                Storage::disk('public')->delete($user->photo);
            }
            $validated['photo'] = $request->file('photo')->store('users', 'public');
        }

        // Handle SMS notification preferences
        $validated['sms_notifications_enabled'] = $request->boolean('sms_notifications_enabled');
        $validated['sms_notification_preferences'] = $request->sms_notification_preferences ?? [];

        // Handle password update if provided
        if ($request->filled('password')) {
            if (!Hash::check($request->current_password, $user->password)) {
                return redirect()->back()->withErrors(['current_password' => 'The current password is incorrect.']);
            }
            $validated['password'] = Hash::make($request->password);
        }

        // Remove password fields from validated data before updating
        unset($validated['current_password']);
        unset($validated['password']);

        $user->update($validated);

        return redirect()->back()
            ->with('success', 'Profile updated successfully.');
    }

    public function destroy()
    {
        $user = auth()->user();

        $user->update([
            'email' => time() . '_' . $user->email,
        ]);

        $user->delete();

        return redirect()->route('login')->with('message', __('global.delete_account_success'));
    }

    public function password(UpdatePasswordRequest $request)
    {
        auth()->user()->update($request->validated());

        return redirect()->route('frontend.profile.index')->with('message', __('global.change_password_success'));
    }

    public function toggleTwoFactor(Request $request)
    {
        $user = auth()->user();

        if ($user->two_factor) {
            $message = __('global.two_factor.disabled');
        } else {
            $message = __('global.two_factor.enabled');
        }

        $user->two_factor = ! $user->two_factor;

        $user->save();

        return redirect()->route('frontend.profile.index')->with('message', $message);
    }

    public function updatePassword(Request $request)
    {
        abort_if(Gate::denies('profile_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $validated = $request->validate([
            'current_password' => 'required|current_password',
            'password' => 'required|string|min:8|confirmed',
        ]);

        auth()->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->back()
            ->with('success', 'Password updated successfully.');
    }
}
