<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Password Reset Routes Fix ===\n\n";

// Check if password reset routes exist
echo "Checking password reset routes...\n";
$routes = \Illuminate\Support\Facades\Route::getRoutes();
$passwordRoutes = [];

foreach ($routes as $route) {
    if (str_contains($route->uri(), 'password')) {
        $passwordRoutes[] = [
            'uri' => $route->uri(),
            'methods' => $route->methods(),
            'name' => $route->getName()
        ];
    }
}

if (empty($passwordRoutes)) {
    echo "❌ No password routes found! Adding them...\n";
    
    // Add password reset routes manually
    \Illuminate\Support\Facades\Route::get('password/reset', function () {
        return view('auth.passwords.email');
    })->name('password.request');
    
    \Illuminate\Support\Facades\Route::post('password/email', function (\Illuminate\Http\Request $request) {
        $request->validate(['email' => 'required|email']);
        
        $status = \Illuminate\Support\Facades\Password::sendResetLink(
            $request->only('email')
        );
        
        return $status === \Illuminate\Support\Facades\Password::RESET_LINK_SENT
            ? back()->with(['status' => __($status)])
            : back()->withErrors(['email' => __($status)]);
    })->name('password.email');
    
    \Illuminate\Support\Facades\Route::get('password/reset/{token}', function ($token) {
        return view('auth.passwords.reset', ['token' => $token]);
    })->name('password.reset');
    
    \Illuminate\Support\Facades\Route::post('password/reset', function (\Illuminate\Http\Request $request) {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);
        
        $status = \Illuminate\Support\Facades\Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => \Illuminate\Support\Facades\Hash::make($password)
                ])->setRememberToken(\Illuminate\Support\Str::random(60));
                
                $user->save();
                
                \Illuminate\Support\Facades\Event::dispatch(new \Illuminate\Auth\Events\PasswordReset($user));
            }
        );
        
        return $status === \Illuminate\Support\Facades\Password::PASSWORD_RESET
            ? redirect()->route('login')->with('status', __($status))
            : back()->withErrors(['email' => [__($status)]]);
    })->name('password.update');
    
    echo "✅ Password reset routes added manually\n";
} else {
    echo "✅ Found " . count($passwordRoutes) . " password routes:\n";
    foreach ($passwordRoutes as $route) {
        echo "   - " . $route['uri'] . " (" . implode('|', $route['methods']) . ")";
        if ($route['name']) {
            echo " - " . $route['name'];
        }
        echo "\n";
    }
}

// Test the password reset flow
echo "\nTesting password reset flow...\n";
try {
    $user = \App\Models\User::where('email', 'admin@example.com')->first();
    
    if ($user) {
        echo "✅ Found user: " . $user->name . "\n";
        
        // Test token creation
        $token = \Illuminate\Support\Facades\Password::createToken($user);
        echo "✅ Token created: " . substr($token, 0, 10) . "...\n";
        
        // Test email sending
        try {
            $user->sendPasswordResetNotification($token);
            echo "✅ Password reset email sent\n";
            
            echo "\nReset URL: " . url('/password/reset/' . $token . '?email=' . urlencode($user->email)) . "\n";
            
        } catch (Exception $e) {
            echo "❌ Email error: " . $e->getMessage() . "\n";
        }
    } else {
        echo "❌ Admin user not found\n";
    }
    
} catch (Exception $e) {
    echo "❌ Password reset error: " . $e->getMessage() . "\n";
}

echo "\n=== Routes Fix Complete ===\n"; 