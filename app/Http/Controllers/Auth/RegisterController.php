<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

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
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
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
}
