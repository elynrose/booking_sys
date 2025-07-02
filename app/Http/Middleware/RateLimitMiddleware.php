<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class RateLimitMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $type = 'default'): Response
    {
        $key = $this->resolveRequestSignature($request, $type);
        
        if (RateLimiter::tooManyAttempts($key, $this->getMaxAttempts($type))) {
            $retryAfter = RateLimiter::availableIn($key);
            
            Log::warning('Rate limit exceeded', [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'type' => $type,
                'retry_after' => $retryAfter
            ]);
            
            return response()->json([
                'error' => 'Too many requests. Please try again later.',
                'retry_after' => $retryAfter
            ], 429)->header('Retry-After', $retryAfter);
        }
        
        RateLimiter::hit($key, $this->getDecayMinutes($type) * 60);
        
        $response = $next($request);
        
        return $response->header('X-RateLimit-Limit', $this->getMaxAttempts($type))
                       ->header('X-RateLimit-Remaining', RateLimiter::remaining($key, $this->getMaxAttempts($type)));
    }
    
    /**
     * Resolve the request signature for rate limiting
     */
    protected function resolveRequestSignature(Request $request, string $type): string
    {
        $identifier = $request->ip();
        
        if ($request->user()) {
            $identifier = $request->user()->id;
        }
        
        return sha1($identifier . '|' . $type);
    }
    
    /**
     * Get maximum attempts based on type
     */
    protected function getMaxAttempts(string $type): int
    {
        return match($type) {
            'login' => 5,
            'register' => 3,
            'checkin' => 10,
            'payment' => 5,
            'api' => 60,
            default => 30
        };
    }
    
    /**
     * Get decay minutes based on type
     */
    protected function getDecayMinutes(string $type): int
    {
        return match($type) {
            'login' => 15,
            'register' => 60,
            'checkin' => 1,
            'payment' => 5,
            'api' => 1,
            default => 1
        };
    }
}
