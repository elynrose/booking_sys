<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LoginLog;
use App\Models\User;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LoginLogsController extends Controller
{
    public function index(Request $request)
    {
        abort_if(Gate::denies('user_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $query = LoginLog::with(['user']);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%');
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by user
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by date range
        if ($request->filled('start_date')) {
            $query->whereDate('login_time', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('login_time', '<=', $request->end_date);
        }

        // Filter by IP address
        if ($request->filled('ip_address')) {
            $query->where('ip_address', 'like', '%' . $request->ip_address . '%');
        }

        $loginLogs = $query->latest('login_time')->paginate(20)->withQueryString();

        // Get users for filter dropdown
        $users = User::orderBy('name')->pluck('name', 'id');

        // Statistics
        $totalLogins = LoginLog::count();
        $todayLogins = LoginLog::whereDate('login_time', today())->count();
        $thisWeekLogins = LoginLog::where('login_time', '>=', now()->startOfWeek())->count();
        $thisMonthLogins = LoginLog::where('login_time', '>=', now()->startOfMonth())->count();
        $activeSessions = LoginLog::where('status', 'login')->whereNull('logout_time')->count();

        return view('admin.login-logs.index', compact(
            'loginLogs', 
            'users', 
            'totalLogins', 
            'todayLogins', 
            'thisWeekLogins', 
            'thisMonthLogins', 
            'activeSessions'
        ));
    }

    public function show(LoginLog $loginLog)
    {
        abort_if(Gate::denies('user_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $loginLog->load('user');

        return view('admin.login-logs.show', compact('loginLog'));
    }

    public function destroy(LoginLog $loginLog)
    {
        abort_if(Gate::denies('user_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $loginLog->delete();

        return redirect()->route('admin.login-logs.index')
            ->with('success', 'Login log deleted successfully.');
    }
}
