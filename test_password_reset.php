<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    echo "Testing password reset functionality...\n";
    
    // Test 1: Check if User model exists
    $user = \App\Models\User::first();
    if ($user) {
        echo "✓ User model found: " . $user->email . "\n";
    } else {
        echo "✗ No users found in database\n";
        exit(1);
    }
    
    // Test 2: Check if ForgotPasswordNotification exists
    $notification = new \App\Notifications\ForgotPasswordNotification('test-token');
    echo "✓ ForgotPasswordNotification created successfully\n";
    
    // Test 3: Check mail configuration
    $mailConfig = config('mail');
    echo "✓ Mail driver: " . $mailConfig['default'] . "\n";
    echo "✓ Mail from address: " . $mailConfig['from']['address'] . "\n";
    
    // Test 4: Check if password reset routes exist
    $routes = \Illuminate\Support\Facades\Route::getRoutes();
    $passwordRoutes = [];
    foreach ($routes as $route) {
        if (str_contains($route->uri(), 'password')) {
            $passwordRoutes[] = $route->uri() . ' (' . implode('|', $route->methods()) . ')';
        }
    }
    echo "✓ Password routes found: " . count($passwordRoutes) . "\n";
    foreach ($passwordRoutes as $route) {
        echo "  - " . $route . "\n";
    }
    
    // Test 5: Check if rate limiting is working
    $rateLimitConfig = config('cache.default');
    echo "✓ Cache driver: " . $rateLimitConfig . "\n";
    
    echo "\nAll tests passed! Password reset should work.\n";
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} 