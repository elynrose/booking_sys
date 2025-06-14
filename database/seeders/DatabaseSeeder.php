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

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            PermissionSeeder::class,
            RoleSeeder::class,
            AssignPermissionsToAdminSeeder::class,
            CategorySeeder::class,
        ]);

        // Create admin user
        $admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
        ]);
        $adminRole = Role::where('title', 'Admin')->first();
        $admin->roles()->attach($adminRole);

        // Create trainer user
        $trainer = User::factory()->create([
            'name' => 'Trainer User',
            'email' => 'trainer@example.com',
        ]);
        $trainer->roles()->attach(Role::where('title', 'Trainer')->first());

        // Create regular users
        $users = User::factory()->count(10)->create();
        $userRole = Role::where('title', 'User')->first();
        foreach ($users as $user) {
            $user->roles()->attach($userRole);
        }

        // Seed Schedules
        Schedule::factory()->count(5)->create();

        // Seed Children
        Child::factory()->count(15)->create();

        // Seed Bookings
        Booking::factory()->count(20)->create();

        // Seed Payments
        Payment::factory()->count(20)->create();
    }
}
