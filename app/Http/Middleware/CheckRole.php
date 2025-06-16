<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckRole
{
    public function handle(Request $request, Closure $next, $role)
    {
        \Log::info('CheckRole middleware:', [
            'user' => $request->user() ? $request->user()->email : 'no user',
            'role' => $role,
            'user_roles' => $request->user() ? $request->user()->roles->pluck('title') : []
        ]);

        if (!$request->user() || !$request->user()->hasRole($role)) {
            \Log::info('Access denied in CheckRole middleware');
            abort(403, 'Unauthorized action.');
        }

        return $next($request);
    }
} 