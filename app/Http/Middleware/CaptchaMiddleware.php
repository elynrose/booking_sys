<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class CaptchaMiddleware
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
        // Check if CAPTCHA is required
        if ($this->isCaptchaRequired($request)) {
            // Validate CAPTCHA
            $request->validate([
                'captcha' => 'required|captcha'
            ], [
                'captcha.required' => 'Please enter the security code.',
                'captcha.captcha' => 'Invalid security code. Please try again.'
            ]);
        }

        return $next($request);
    }

    /**
     * Determine if CAPTCHA is required based on the request
     */
    private function isCaptchaRequired(Request $request): bool
    {
        // For login attempts - check if 3 or more failed attempts
        if ($request->is('login') && $request->isMethod('post')) {
            return Session::get('login_attempts', 0) >= 3;
        }

        // For password reset - always require CAPTCHA
        if ($request->is('password/email') && $request->isMethod('post')) {
            return true;
        }

        // For trainer reviews by guests - always require CAPTCHA
        if ($request->is('trainer-reviews/*') && $request->isMethod('post') && !auth()->check()) {
            return true;
        }

        return false;
    }
} 