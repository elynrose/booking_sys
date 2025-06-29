<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\DB;

echo "Updating all users to be verified...\n";

// Update all users to be verified
$updated = User::query()
    ->whereNull('email_verified_at')
    ->orWhere('verified', 0)
    ->orWhereNull('verified_at')
    ->update([
        'email_verified_at' => now(),
        'verified' => 1,
        'verified_at' => now(),
    ]);

echo "Updated {$updated} users to be verified.\n";

// Show current verification status
$totalUsers = User::count();
$verifiedUsers = User::where('verified', 1)->count();
$emailVerifiedUsers = User::whereNotNull('email_verified_at')->count();

echo "\nVerification Status:\n";
echo "Total users: {$totalUsers}\n";
echo "Verified users: {$verifiedUsers}\n";
echo "Email verified users: {$emailVerifiedUsers}\n";

echo "\nAll users are now verified!\n"; 