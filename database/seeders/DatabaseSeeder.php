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
        
        // Assign admin role
        $admin->assignRole('Admin');

        // Create trainer user
        $trainer = User::firstOrCreate([
            'email' => 'trainer@example.com',
        ], [
            'name' => $faker->name(),
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        
        // Assign trainer role
        $trainer->assignRole('Trainer');

        // Ensure at least one Trainer model exists for the user
        \App\Models\Trainer::firstOrCreate([
            'user_id' => $trainer->id
        ], [
            'bio' => 'Default trainer bio',
            'is_active' => true,
        ]);

        // Create regular users with realistic names
        $users = User::factory()->count(10)->create();
        
        // Assign user role to all factory-created users
        foreach ($users as $user) {
            $user->assignRole('User');
        }

        // Seed Schedules with realistic gym activities
        Schedule::factory()->count(5)->create();

        // Seed Children with realistic names and ages
        Child::factory()->count(15)->create();

        // Seed Bookings
        Booking::factory()->count(20)->create();

        // Seed Payments
        Payment::factory()->count(20)->create();

        // Verify all user emails after users are created
        $this->call([
            VerifyAllEmailsSeeder::class,
        ]);
    }
}
