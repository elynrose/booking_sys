<?php

/**
 * Temporary 2FA Disable Script
 * This script disables 2FA for the admin user to test CSV import
 */

require_once 'vendor/autoload.php';

use App\Models\User;
use Illuminate\Support\Facades\DB;

echo "=== 2FA Disable Script ===\n\n";

try {
    // Find admin user
    $admin = User::where('email', 'admin@example.com')->first();
    
    if (!$admin) {
        echo "❌ Admin user not found. Creating one...\n";
        
        // Create admin user if it doesn't exist
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
        ]);
        
        // Assign admin role
        $adminRole = \App\Models\Role::where('title', 'Admin')->first();
        if ($adminRole) {
            $admin->roles()->attach($adminRole->id);
        }
        
        echo "✅ Admin user created successfully!\n";
    } else {
        echo "✅ Admin user found: {$admin->email}\n";
    }
    
    // Disable 2FA by clearing the two_factor_code
    $admin->resetTwoFactorCode();
    
    echo "✅ 2FA disabled for admin user\n";
    echo "📧 Email: admin@example.com\n";
    echo "🔑 Password: password\n\n";
    
    echo "🔧 Now you can:\n";
    echo "1. Go to: http://localhost:8008/login\n";
    echo "2. Login with admin@example.com / password\n";
    echo "3. You should NOT be redirected to 2FA\n";
    echo "4. Go to: http://localhost:8008/admin/schedules/import\n";
    echo "5. Test the CSV import functionality\n\n";
    
    echo "⚠️  IMPORTANT: This is for testing only!\n";
    echo "   - 2FA is disabled for security\n";
    echo "   - Re-enable 2FA after testing\n";
    echo "   - Change the password in production\n\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "=== End of Script ===\n"; 