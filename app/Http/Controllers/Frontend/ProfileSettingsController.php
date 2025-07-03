<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProfileSettingsController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        return view('frontend.profile.settings', compact('user'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'phone_number' => 'nullable|string|max:20',
            'sms_notifications_enabled' => 'boolean',
            'sms_notification_preferences' => 'array',
            'sms_notification_preferences.*' => 'boolean',
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone_number' => $request->phone_number,
            'sms_notifications_enabled' => $request->boolean('sms_notifications_enabled'),
            'sms_notification_preferences' => $request->sms_notification_preferences ?? [],
        ]);

        return redirect()->back()->with('success', 'Profile settings updated successfully!');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|current_password',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = Auth::user();
        $user->update([
            'password' => Hash::make($request->password)
        ]);

        return redirect()->back()->with('success', 'Password updated successfully!');
    }
}
