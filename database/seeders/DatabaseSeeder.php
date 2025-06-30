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
use Faker\Factory as Faker;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $faker = Faker::create();

        // First run the basic seeders
        $this->call([
            PermissionSeeder::class,
            RoleSeeder::class,
            AssignPermissionsToAdminSeeder::class,
            CategorySeeder::class,
            SiteSettingsSeeder::class,
        ]);

        // Create admin user
        $admin = User::create([
            'name' => $faker->name(),
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $adminRole = Role::where('title', 'Admin')->first();
        $admin->roles()->attach($adminRole);

        // Create trainer user
        $trainer = User::create([
            'name' => $faker->name(),
            'email' => 'trainer@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $trainer->roles()->attach(Role::where('name', 'Trainer')->first());

        // Create regular users with realistic names
        $users = User::factory()->count(10)->create();
        $userRole = Role::where('title', 'User')->first();
        foreach ($users as $user) {
            $user->roles()->attach($userRole);
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
