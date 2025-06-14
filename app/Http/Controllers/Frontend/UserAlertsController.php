<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\MassDestroyUserAlertRequest;
use App\Http\Requests\StoreUserAlertRequest;
use App\Models\User;
use App\Models\UserAlert;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UserAlertsController extends Controller
{
    public function index()
    {
        abort_if(Gate::denies('user_alert_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $userAlerts = UserAlert::with(['users'])
            ->whereHas('users', function($query) {
                $query->where('users.id', auth()->id());
            })
            ->get();

        return view('frontend.userAlerts.index', compact('userAlerts'));
    }

    public function create()
    {
        abort_if(Gate::denies('user_alert_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $users = User::pluck('name', 'id');

        return view('frontend.userAlerts.create', compact('users'));
    }

    public function store(StoreUserAlertRequest $request)
    {
        abort_if(Gate::denies('user_alert_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $userAlert = UserAlert::create($request->all());
        $userAlert->users()->sync($request->input('users', []));

        return redirect()->route('frontend.user-alerts.index')
            ->with('success', 'Alert created successfully.');
    }

    public function show(UserAlert $userAlert)
    {
        abort_if(Gate::denies('user_alert_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        // Check if user has access to this alert
        if (!$userAlert->users()->where('users.id', auth()->id())->exists()) {
            abort(403, 'Unauthorized action.');
        }

        $userAlert->load('users');

        return view('frontend.userAlerts.show', compact('userAlert'));
    }

    public function destroy(UserAlert $userAlert)
    {
        abort_if(Gate::denies('user_alert_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        // Check if user has access to this alert
        if (!$userAlert->users()->where('users.id', auth()->id())->exists()) {
            abort(403, 'Unauthorized action.');
        }

        $userAlert->delete();

        return back()->with('success', 'Alert deleted successfully.');
    }

    public function massDestroy(MassDestroyUserAlertRequest $request)
    {
        abort_if(Gate::denies('user_alert_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $userAlerts = UserAlert::whereHas('users', function($query) {
            $query->where('users.id', auth()->id());
        })->whereIn('id', request('ids'))->get();

        foreach ($userAlerts as $userAlert) {
            $userAlert->delete();
        }

        return response(null, Response::HTTP_NO_CONTENT);
    }

    public function read(Request $request)
    {
        abort_if(Gate::denies('user_alert_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        // Alerts functionality has been removed
        return response()->json(['message' => 'Alerts functionality has been removed']);
    }
}
