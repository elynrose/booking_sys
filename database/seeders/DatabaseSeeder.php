<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Schedule;
use App\Models\Payment;
use App\Models\Booking;
use App\Models\Child;
use App\Models\Role;
use App\Models\Category;
use App\Models\Permission;
use Illuminate\Support\Facades\Hash;
use Faker\Factory;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $faker = Factory::create();

        // First run the basic seeders
        $this->call([
            PermissionSeeder::class,
            RoleSeeder::class,
            AssignPermissionsToAdminSeeder::class,
            CategorySeeder::class,
            SiteSettingsSeeder::class,
            StripeSettingsSeeder::class,
        ]);

        // Create admin user
        $admin = User::firstOrCreate([
            'email' => 'admin@example.com',
        ], [
            'name' => $faker->name(),
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $adminRole = Role::where('name', 'Admin')->first();
        if ($adminRole && !$admin->roles()->where('name', 'Admin')->exists()) {
            $admin->roles()->attach($adminRole);
        }

        // Create trainer user
        $trainer = User::firstOrCreate([
            'email' => 'trainer@example.com',
        ], [
            'name' => $faker->name(),
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $trainerRole = Role::where('name', 'Trainer')->first();
        if ($trainerRole && !$trainer->roles()->where('name', 'Trainer')->exists()) {
            $trainer->roles()->attach($trainerRole);
        }

        // Create regular users with realistic names
        $users = User::factory()->count(10)->create();
        $userRole = Role::where('name', 'User')->first();
        foreach ($users as $user) {
            if ($userRole && !$user->roles()->where('name', 'User')->exists()) {
                $user->roles()->attach($userRole);
            }
        }

        // Seed Schedules with realistic gym activities
        Schedule::factory()->count(5)->create();

        // Seed Children with realistic names and ages
        Child::factory()->count(15)->create();

        // Seed Bookings
        Booking::factory()->count(20)->create();

        // Seed Payments
        Payment::factory()->count(20)->create();
    }
}
