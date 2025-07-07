<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
        $this->middleware('antispam');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name'     => [
                'required', 
                'string', 
                'max:50', 
                'min:2',
                'regex:/^[a-zA-Z\s]+$/',
                'not_regex:/\d{3,}/',
                'not_regex:/(.)\1{4,}/'
            ],
            'email'    => [
                'required', 
                'string', 
                'email', 
                'max:255', 
                'unique:users',
                'not_regex:/[0-9]{6,}/',
                'not_regex:/\.{3,}/'
            ],
            'password' => [
                'required', 
                'string', 
                'min:8', 
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/'
            ],
            'captcha'  => 'required|captcha',
            'terms'    => 'required|accepted',
            // Honeypot fields should be empty
            'website'  => 'prohibited',
            'phone_number' => 'prohibited',
            'company'  => 'prohibited',
        ], [
            'name.regex' => 'Name can only contain letters and spaces.',
            'name.not_regex' => 'Name contains invalid patterns.',
            'email.not_regex' => 'Email contains invalid patterns.',
            'password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character.',
            'captcha.captcha' => 'Please enter the correct verification code.',
            'terms.required' => 'You must accept the terms and conditions.',
            'website.prohibited' => 'Invalid request.',
            'phone_number.prohibited' => 'Invalid request.',
            'company.prohibited' => 'Invalid request.',
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User
     */
    protected function create(array $data)
    {
        // Log successful registration for monitoring
        Log::info('New user registration', [
            'email' => $data['email'],
            'name' => $data['name'],
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent()
        ]);

        return User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
            'member_id' => $this->generateUniqueMemberId(),
        ]);
    }

    /**
     * Generate a unique member ID
     *
     * @return string
     */
    private function generateUniqueMemberId()
    {
        do {
            // Generate a member ID with format: GYM-YYYY-XXXX (e.g., GYM-2024-0001)
            $year = date('Y');
            $randomNumber = str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
            $memberId = "GYM-{$year}-{$randomNumber}";
        } while (User::where('member_id', $memberId)->exists());

        return $memberId;
    }

    /**
     * Handle a registration request for the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        // Additional rate limiting check
        if ($this->isRateLimited($request)) {
            return back()->withErrors(['email' => 'Too many registration attempts. Please try again later.']);
        }

        return parent::register($request);
    }

    /**
     * Check if user is rate limited
     */
    private function isRateLimited(Request $request): bool
    {
        $key = 'registration_attempts:' . $request->ip();
        $attempts = cache($key, 0);
        
        if ($attempts >= 3) { // 3 attempts per hour
            return true;
        }
        
        cache([$key => $attempts + 1], 3600); // 1 hour
        return false;
    }
}
