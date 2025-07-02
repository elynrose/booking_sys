<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Cloud 500 Error Debug ===\n\n";

// Check environment
echo "Environment Check:\n";
echo "APP_ENV: " . env('APP_ENV') . "\n";
echo "APP_DEBUG: " . (env('APP_DEBUG') ? 'true' : 'false') . "\n";
echo "APP_URL: " . env('APP_URL') . "\n\n";

// Check mail configuration
echo "Mail Configuration:\n";
echo "MAIL_MAILER: " . env('MAIL_MAILER') . "\n";
echo "MAIL_FROM_ADDRESS: " . env('MAIL_FROM_ADDRESS') . "\n";
echo "MAIL_FROM_NAME: " . env('MAIL_FROM_NAME') . "\n\n";

// Check if password reset routes exist
echo "Password Reset Routes Check:\n";
try {
    $routes = \Illuminate\Support\Facades\Route::getRoutes();
    $passwordRoutes = [];
    foreach ($routes as $route) {
        if (str_contains($route->uri(), 'password')) {
            $passwordRoutes[] = $route->uri() . ' (' . implode('|', $route->methods()) . ')';
        }
    }
    
    if (empty($passwordRoutes)) {
        echo "❌ No password routes found!\n";
    } else {
        echo "✅ Found " . count($passwordRoutes) . " password routes:\n";
        foreach ($passwordRoutes as $route) {
            echo "   - " . $route . "\n";
        }
    }
} catch (Exception $e) {
    echo "❌ Error checking routes: " . $e->getMessage() . "\n";
}

// Check if password reset tokens table exists
echo "\nDatabase Check:\n";
try {
    $hasTable = \Illuminate\Support\Facades\Schema::hasTable('password_reset_tokens');
    echo "password_reset_tokens table: " . ($hasTable ? "✅ Exists" : "❌ Missing") . "\n";
    
    if ($hasTable) {
        $count = \Illuminate\Support\Facades\DB::table('password_reset_tokens')->count();
        echo "Tokens in table: " . $count . "\n";
    }
} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
}

// Test mailer creation
echo "\nMailer Test:\n";
try {
    $mailer = app('mailer');
    echo "✅ Mailer created successfully\n";
    
    // Test mail configuration
    $mailConfig = config('mail');
    echo "Default mailer: " . $mailConfig['default'] . "\n";
    echo "From address: " . $mailConfig['from']['address'] . "\n";
    
} catch (Exception $e) {
    echo "❌ Mailer error: " . $e->getMessage() . "\n";
}

// Test password reset functionality
echo "\nPassword Reset Test:\n";
try {
    $user = \App\Models\User::where('email', 'admin@example.com')->first();
    
    if (!$user) {
        echo "❌ Admin user not found\n";
    } else {
        echo "✅ Found user: " . $user->name . "\n";
        
        // Test token creation
        $token = \Illuminate\Support\Facades\Password::createToken($user);
        echo "✅ Token created: " . substr($token, 0, 10) . "...\n";
        
        // Test email sending
        try {
            $user->sendPasswordResetNotification($token);
            echo "✅ Password reset email sent\n";
        } catch (Exception $e) {
            echo "❌ Email error: " . $e->getMessage() . "\n";
        }
    }
} catch (Exception $e) {
    echo "❌ Password reset error: " . $e->getMessage() . "\n";
}

// Check for missing dependencies
echo "\nDependency Check:\n";
$requiredClasses = [
    'Illuminate\Auth\Notifications\ResetPassword',
    'App\Models\User',
    'Illuminate\Support\Facades\Password',
    'Illuminate\Support\Facades\Mail'
];

foreach ($requiredClasses as $class) {
    if (class_exists($class)) {
        echo "✅ " . $class . "\n";
    } else {
        echo "❌ " . $class . " - Missing!\n";
    }
}

echo "\n=== Debug Complete ===\n"; 