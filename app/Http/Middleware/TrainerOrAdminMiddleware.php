<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class TrainerOrAdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        \Log::info('TrainerOrAdminMiddleware:', [
            'user' => $request->user() ? $request->user()->email : 'no user',
            'user_roles' => $request->user() ? $request->user()->roles->pluck('name') : []
        ]);

        if (!$request->user() || (!$request->user()->hasRole('Trainer') && !$request->user()->hasRole('Admin'))) {
            \Log::info('Access denied in TrainerOrAdminMiddleware');
            abort(403, 'Unauthorized action.');
        }

        return $next($request);
    }
} 