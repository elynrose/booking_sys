<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AntiSpamMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // Only apply to registration and login routes
        if (!$this->shouldApplyAntiSpam($request)) {
            return $next($request);
        }

        $ip = $request->ip();
        $userAgent = $request->userAgent();

        // 1. Rate Limiting
        if ($this->isRateLimited($ip, $request)) {
            Log::warning('Rate limit exceeded for registration', [
                'ip' => $ip,
                'user_agent' => $userAgent,
                'url' => $request->url()
            ]);
            return response()->json(['error' => 'Too many registration attempts. Please try again later.'], 429);
        }

        // 2. Honeypot Detection
        if ($this->isHoneypotTriggered($request)) {
            Log::warning('Honeypot triggered', [
                'ip' => $ip,
                'user_agent' => $userAgent,
                'url' => $request->url()
            ]);
            return response()->json(['error' => 'Invalid request.'], 400);
        }

        // 3. Suspicious Pattern Detection
        if ($this->hasSuspiciousPatterns($request)) {
            Log::warning('Suspicious patterns detected', [
                'ip' => $ip,
                'user_agent' => $userAgent,
                'url' => $request->url(),
                'data' => $request->all()
            ]);
            return response()->json(['error' => 'Invalid request.'], 400);
        }

        // 4. Bot Detection
        if ($this->isLikelyBot($request)) {
            Log::warning('Bot-like behavior detected', [
                'ip' => $ip,
                'user_agent' => $userAgent,
                'url' => $request->url()
            ]);
            return response()->json(['error' => 'Please complete the verification.'], 400);
        }

        // 5. Email Domain Validation
        if ($request->has('email') && $this->isSuspiciousEmail($request->email)) {
            Log::warning('Suspicious email domain', [
                'ip' => $ip,
                'email' => $request->email,
                'user_agent' => $userAgent
            ]);
            return response()->json(['error' => 'Please use a valid email address.'], 400);
        }

        // 6. Time-based Protection (minimum time between requests)
        if ($this->isTooFast($ip)) {
            Log::warning('Request too fast', [
                'ip' => $ip,
                'user_agent' => $userAgent
            ]);
            return response()->json(['error' => 'Please wait before trying again.'], 429);
        }

        return $next($request);
    }

    /**
     * Check if anti-spam should be applied to this request
     */
    private function shouldApplyAntiSpam(Request $request): bool
    {
        $spamRoutes = ['register', 'login', 'password.email'];
        return $request->routeIs($spamRoutes);
    }

    /**
     * Rate limiting check
     */
    private function isRateLimited(string $ip, Request $request): bool
    {
        $key = 'registration_rate_limit:' . $ip;
        $maxAttempts = 5; // 5 attempts
        $decayMinutes = 60; // per hour

        return RateLimiter::tooManyAttempts($key, $maxAttempts);
    }

    /**
     * Honeypot detection
     */
    private function isHoneypotTriggered(Request $request): bool
    {
        // Check for honeypot fields that should be empty
        $honeypotFields = ['website', 'phone_number', 'company'];
        
        foreach ($honeypotFields as $field) {
            if ($request->filled($field)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Detect suspicious patterns
     */
    private function hasSuspiciousPatterns(Request $request): bool
    {
        $data = $request->all();
        
        // Check for suspicious patterns in name
        if (isset($data['name'])) {
            $name = $data['name'];
            
            // Too many numbers in name
            if (preg_match('/\d{3,}/', $name)) {
                return true;
            }
            
            // Repeated characters
            if (preg_match('/(.)\1{4,}/', $name)) {
                return true;
            }
            
            // Too short or too long
            if (strlen($name) < 2 || strlen($name) > 50) {
                return true;
            }
        }

        // Check for suspicious email patterns
        if (isset($data['email'])) {
            $email = $data['email'];
            
            // Too many dots or special characters
            if (substr_count($email, '.') > 3 || substr_count($email, '@') > 1) {
                return true;
            }
            
            // Suspicious patterns
            if (preg_match('/[0-9]{6,}/', $email)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Bot detection
     */
    private function isLikelyBot(Request $request): bool
    {
        $userAgent = $request->userAgent();
        
        // Empty or suspicious user agent
        if (empty($userAgent) || strlen($userAgent) < 20) {
            return true;
        }
        
        // Common bot user agents
        $botPatterns = [
            'bot', 'crawler', 'spider', 'scraper', 'curl', 'wget',
            'python', 'java', 'perl', 'ruby', 'php', 'go-http'
        ];
        
        foreach ($botPatterns as $pattern) {
            if (stripos($userAgent, $pattern) !== false) {
                return true;
            }
        }

        // Missing common headers
        if (!$request->header('Accept-Language') && !$request->header('Accept')) {
            return true;
        }

        return false;
    }

    /**
     * Check for suspicious email domains
     */
    private function isSuspiciousEmail(string $email): bool
    {
        $domain = substr(strrchr($email, '@'), 1);
        
        // Temporary email domains
        $tempDomains = [
            '10minutemail.com', 'guerrillamail.com', 'mailinator.com',
            'tempmail.org', 'throwaway.email', 'yopmail.com',
            'temp-mail.org', 'sharklasers.com', 'getairmail.com'
        ];
        
        return in_array(strtolower($domain), $tempDomains);
    }

    /**
     * Check if request is too fast (minimum time between requests)
     */
    private function isTooFast(string $ip): bool
    {
        $key = 'registration_timing:' . $ip;
        $lastRequest = Cache::get($key);
        
        if ($lastRequest && (time() - $lastRequest) < 3) { // 3 seconds minimum
            return true;
        }
        
        Cache::put($key, time(), 300); // 5 minutes
        return false;
    }
} 