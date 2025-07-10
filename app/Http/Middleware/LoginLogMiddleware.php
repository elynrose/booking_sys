<?php

namespace App\Http\Middleware;

use App\Models\LoginLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginLogMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Log successful login
        if (Auth::check() && $request->is('login') && $request->isMethod('post')) {
            $user = Auth::user();
            
            // Check if login was successful (no validation errors)
            if (!$request->session()->has('errors')) {
                LoginLog::create([
                    'user_id' => $user->id,
                    'login_time' => now(),
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'status' => 'login',
                ]);
            }
        }

        // Log logout
        if ($request->is('logout') && $request->isMethod('post')) {
            $user = Auth::user();
            
            if ($user) {
                // Find the most recent active login session for this user
                $activeSession = LoginLog::where('user_id', $user->id)
                    ->where('status', 'login')
                    ->whereNull('logout_time')
                    ->latest('login_time')
                    ->first();

                if ($activeSession) {
                    $activeSession->update([
                        'logout_time' => now(),
                        'status' => 'logout',
                    ]);
                }
            }
        }

        return $response;
    }
}
