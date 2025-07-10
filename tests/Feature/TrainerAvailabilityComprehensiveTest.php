<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Trainer;
use App\Models\Schedule;
use App\Models\Booking;
use App\Models\Category;
use App\Models\TrainerAvailability;
use App\Models\TrainerUnavailability;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Carbon\Carbon;

class TrainerAvailabilityComprehensiveTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test data
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->user = User::factory()->create(['role' => 'user']);
        $this->trainerUser = User::factory()->create(['role' => 'trainer']);
        
        // Clear any two-factor codes that might interfere with tests
        $this->admin->update(['two_factor_code' => null, 'two_factor_expires_at' => null]);
        $this->user->update(['two_factor_code' => null, 'two_factor_expires_at' => null]);
        $this->trainerUser->update(['two_factor_code' => null, 'two_factor_expires_at' => null]);
        
        // Assign roles to test users
        $adminRole = \App\Models\Role::where('name', 'Admin')->first();
        $userRole = \App\Models\Role::where('name', 'User')->first();
        $trainerRole = \App\Models\Role::where('name', 'Trainer')->first();
        
        if ($adminRole) {
            $this->admin->assignRole($adminRole);
        }
        if ($userRole) {
            $this->user->assignRole($userRole);
        }
        if ($trainerRole) {
            $this->trainerUser->assignRole($trainerRole);
        }
        
        // Refresh users to reload roles
        $this->admin->refresh();
        $this->user->refresh();
        $this->trainerUser->refresh();
        
        // Clear Spatie permission cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        
        // Debug: Check if roles are assigned correctly
        \Log::info('Test Setup Debug', [
            'admin_has_role' => $this->admin->hasRole('Admin'),
            'trainer_has_role' => $this->trainerUser->hasRole('Trainer'),
            'user_has_role' => $this->user->hasRole('User'),
            'admin_roles' => $this->admin->roles->pluck('name')->toArray(),
            'trainer_roles' => $this->trainerUser->roles->pluck('name')->toArray(),
            'user_roles' => $this->user->roles->pluck('name')->toArray(),
        ]);
        $this->trainer = Trainer::factory()->create([
            'user_id' => $this->trainerUser->id,
            'is_active' => true,
            'is_available_by_default' => true,
            'default_start_time' => '09:00:00',
            'default_end_time' => '17:00:00',
            'default_available_days' => [1, 2, 3, 4, 5], // Monday to Friday
        ]);
        $this->trainer->refresh();
        
        // Debug: Let's see what the actual default_available_days value is
        \Log::info('Trainer default_available_days:', [
            'value' => $this->trainer->default_available_days,
            'type' => gettype($this->trainer->default_available_days)
        ]);
        $this->category = Category::factory()->create();
        $this->schedule = Schedule::factory()->create([
            'trainer_id' => $this->trainerUser->id,
            'category_id' => $this->category->id,
            'status' => 'active',
            'start_date' => now()->addDays(1),
            'end_date' => now()->addDays(30),
            'start_time' => now()->addDays(1)->setTime(10, 0),
            'end_time' => now()->addDays(1)->setTime(11, 0),
            'max_participants' => 10,
        ]);
    }

    /**
     * Test trainer default availability settings
     */
    public function test_trainer_default_availability_settings()
    {
        $this->assertTrue($this->trainer->is_available_by_default);
        $this->assertEquals('09:00:00', $this->trainer->default_start_time->format('H:i:s'));
        $this->assertEquals('17:00:00', $this->trainer->default_end_time->format('H:i:s'));
        $this->assertEquals([1, 2, 3, 4, 5], $this->trainer->default_available_days);
    }

    /**
     * Test trainer availability for specific date and time
     */
    public function test_trainer_availability_for_specific_datetime()
    {
        $availableDate = now()->addDays(1); // Monday
        $availableTime = '10:30:00';
        
        // Should be available on Monday at 10:30 AM
        $this->assertTrue($this->trainer->isAvailable($availableDate->format('Y-m-d'), $availableTime, $this->schedule->id));
        
        // Should not be available on Sunday
        $sundayDate = now()->next(Carbon::SUNDAY); // Correct next Sunday
        \Log::info('Sunday availability check:', [
            'sunday_date' => $sundayDate->format('Y-m-d'),
            'day_of_week' => $sundayDate->dayOfWeek,
            'available_days' => $this->trainer->default_available_days,
            'is_available' => $this->trainer->isAvailable($sundayDate->format('Y-m-d'), $availableTime, $this->schedule->id)
        ]);
        $this->assertFalse($this->trainer->isAvailable($sundayDate->format('Y-m-d'), $availableTime, $this->schedule->id));
        
        // Should not be available outside working hours
        $lateTime = '18:00:00';
        $this->assertFalse($this->trainer->isAvailable($availableDate->format('Y-m-d'), $lateTime, $this->schedule->id));
    }

    /**
     * Test trainer unavailability creation and its effect on availability
     */
    public function test_trainer_unavailability_affects_availability()
    {
        $unavailableDate = now()->addDays(1)->format('Y-m-d');
        $unavailableTime = '10:30:00';
        
        // Create unavailability for the trainer
        TrainerUnavailability::create([
            'trainer_id' => $this->trainer->user_id,
            'schedule_id' => $this->schedule->id,
            'date' => $unavailableDate,
            'start_time' => $unavailableDate . ' 10:00:00',
            'end_time' => $unavailableDate . ' 12:00:00',
            'reason' => 'personal',
            'notes' => 'Personal appointment',
        ]);
        
        // Trainer should not be available during unavailability period
        $this->assertFalse($this->trainer->isAvailable($unavailableDate, $unavailableTime, $this->schedule->id));
        
        // Trainer should still be available at other times
        $this->assertTrue($this->trainer->isAvailable($unavailableDate, '14:00:00', $this->schedule->id));
    }

    /**
     * Test schedule availability when trainer is unavailable
     */
    public function test_schedule_availability_when_trainer_unavailable()
    {
        $unavailableDate = now()->addDays(1)->format('Y-m-d');
        
        // Create unavailability for the trainer
        TrainerUnavailability::create([
            'trainer_id' => $this->trainer->user_id,
            'schedule_id' => $this->schedule->id,
            'date' => $unavailableDate,
            'start_time' => $unavailableDate . ' 09:00:00',
            'end_time' => $unavailableDate . ' 18:00:00',
            'reason' => 'sick',
        ]);
        
        // Schedule should not be available when trainer is unavailable
        // Check if trainer is available for the specific date we made unavailable
        $this->assertFalse($this->trainer->isAvailable($unavailableDate, '10:00:00', $this->schedule->id));
    }

    /**
     * Test booking creation when trainer is available
     */
    public function test_booking_creation_when_trainer_available()
    {
        $this->actingAs($this->user);
        
        $bookingDate = now()->addDays(1)->format('Y-m-d');
        
        $response = $this->post("/bookings/{$this->schedule->id}", [
            'booking_date' => $bookingDate,
            'notes' => 'Test booking with available trainer',
        ]);
        
        $response->assertRedirect();
        $this->assertDatabaseHas('bookings', [
            'user_id' => $this->user->id,
            'schedule_id' => $this->schedule->id,
            'status' => 'pending',
        ]);
    }

    /**
     * Test booking creation when trainer is unavailable
     */
    public function test_booking_creation_when_trainer_unavailable()
    {
        $this->actingAs($this->user);
        
        $unavailableDate = now()->addDays(1)->format('Y-m-d');
        
        // Create unavailability for the trainer
        TrainerUnavailability::create([
            'trainer_id' => $this->trainer->user_id,
            'schedule_id' => $this->schedule->id,
            'date' => $unavailableDate,
            'start_time' => $unavailableDate . ' 09:00:00',
            'end_time' => $unavailableDate . ' 18:00:00',
            'reason' => 'vacation',
        ]);
        
        // Debug: Check if trainer is actually unavailable
        \Log::info('Trainer availability check:', [
            'trainer_id' => $this->trainer->id,
            'date' => $unavailableDate,
            'is_available' => $this->trainer->isAvailable($unavailableDate, '10:00:00', $this->schedule->id)
        ]);
        
        $response = $this->post("/bookings/{$this->schedule->id}", [
            'booking_date' => $unavailableDate,
            'notes' => 'Test booking with unavailable trainer',
        ]);
        
        // Debug: Check response
        \Log::info('Booking response:', [
            'status' => $response->status(),
            'session_errors' => session('error'),
            'session_data' => session()->all()
        ]);
        
        // Should not allow booking when trainer is unavailable
        $response->assertSessionHas('error');
        $response->assertSessionHas('error', function ($msg) {
            return str_contains($msg, 'Trainer is not available') && str_contains($msg, 'Next available date');
        });
        $this->assertDatabaseMissing('bookings', [
            'user_id' => $this->user->id,
            'schedule_id' => $this->schedule->id,
        ]);
    }

    /**
     * Test trainer availability creation and management
     */
    public function test_trainer_availability_creation()
    {
        // Assign admin role to user
        $adminRole = \App\Models\Role::where('name', 'Admin')->first();
        $this->admin->assignRole($adminRole);
        $this->admin->refresh();
        
        $this->actingAs($this->admin);
        
        $availabilityDate = now()->addDays(1)->format('Y-m-d');
        
        $response = $this->post("/admin/trainer-unavailability", [
            'dates' => [$availabilityDate],
            'start_time' => '10:00',
            'end_time' => '11:00',
            'reason' => 'Regular session',
        ]);
        
        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('trainer_unavailabilities', [
            'trainer_id' => $this->trainer->user_id,
            'schedule_id' => $this->schedule->id,
            'date' => $availabilityDate,
        ]);
    }

    /**
     * Test recurring availability creation
     */
    public function test_recurring_availability_creation()
    {
        $this->actingAs($this->admin);
        
        $startDate = now()->addDays(1)->format('Y-m-d');
        $endDate = now()->addDays(7)->format('Y-m-d');
        
        $response = $this->post("/admin/trainer-availability/{$this->schedule->id}/create-recurring", [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'days_of_week' => [1, 3, 5], // Monday, Wednesday, Friday
            'start_time' => '10:00',
            'end_time' => '11:00',
            'status' => 'available',
        ]);
        
        $response->assertRedirect();
        
        // Should create unavailability for Monday, Wednesday, Friday
        $this->assertDatabaseHas('trainer_unavailabilities', [
            'trainer_id' => $this->trainer->user_id,
            'schedule_id' => $this->schedule->id,
        ]);
    }

    /**
     * Test bulk availability update
     */
    public function test_bulk_availability_update()
    {
        $this->actingAs($this->admin);
        
        $dates = [
            now()->addDays(1)->format('Y-m-d'),
            now()->addDays(2)->format('Y-m-d'),
            now()->addDays(3)->format('Y-m-d'),
        ];
        
        $response = $this->post("/admin/trainer-unavailability/bulk-create", [
            'dates' => $dates,
            'action' => 'mark_unavailable',
            'reason' => 'Training workshop',
        ]);
        
        $response->assertJson(['success' => true]);
        
        foreach ($dates as $date) {
            $this->assertDatabaseHas('trainer_unavailabilities', [
                'trainer_id' => $this->trainer->user_id,
                'schedule_id' => $this->schedule->id,
                'date' => $date,
            ]);
        }
    }

    /**
     * Test trainer availability affects existing bookings
     */
    public function test_trainer_unavailability_affects_existing_bookings()
    {
        $this->actingAs($this->user);
        
        $bookingDate = now()->addDays(1)->format('Y-m-d');
        
        // Create a booking
        $booking = Booking::factory()->create([
            'user_id' => $this->user->id,
            'schedule_id' => $this->schedule->id,
            'status' => 'confirmed',
        ]);
        
        // Create unavailability for the trainer after booking
        TrainerUnavailability::create([
            'trainer_id' => $this->trainer->user_id,
            'schedule_id' => $this->schedule->id,
            'date' => $bookingDate,
            'start_time' => $bookingDate . ' 09:00:00',
            'end_time' => $bookingDate . ' 18:00:00',
            'reason' => 'other',
        ]);
        
        // The booking should still exist but the schedule should show trainer as unavailable
        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'confirmed',
        ]);
        
        // Check if trainer is available for the specific date we made unavailable
        $this->assertFalse($this->trainer->isAvailable($bookingDate, '10:00:00', $this->schedule->id));
    }

    /**
     * Test trainer availability calendar functionality
     */
    public function test_trainer_availability_calendar()
    {
        $this->actingAs($this->admin);
        
        $response = $this->get("/admin/trainer-availability/{$this->schedule->id}/calendar");
        
        $response->assertStatus(200);
        $response->assertViewIs('admin.trainer-availability.calendar');
    }

    /**
     * Test frontend trainer availability management
     */
    public function test_frontend_trainer_availability_management()
    {
        $this->actingAs($this->trainerUser);
        
        $response = $this->get('/trainer/availability');
        
        $response->assertStatus(200);
        $response->assertViewIs('frontend.trainer.availability.index');
    }

    /**
     * Test trainer can mark themselves as unavailable
     */
    public function test_trainer_can_mark_unavailable()
    {
        $this->actingAs($this->trainerUser);
        
        $unavailableDate = now()->addDays(1)->format('Y-m-d');
        
        $response = $this->post('/trainer/unavailability', [
            'schedule_id' => $this->schedule->id,
            'date' => $unavailableDate,
            'start_time' => '10:00',
            'end_time' => '12:00',
            'reason' => 'personal',
            'notes' => 'Personal appointment',
        ]);
        
        $response->assertRedirect();
        $this->assertDatabaseHas('trainer_unavailabilities', [
            'trainer_id' => $this->trainer->user_id,
            'schedule_id' => $this->schedule->id,
            'date' => $unavailableDate,
        ]);
    }

    /**
     * Test trainer availability settings update
     */
    public function test_trainer_availability_settings_update()
    {
        $this->actingAs($this->trainerUser);
        
        $response = $this->put('/trainer/unavailability/settings', [
            'is_available_by_default' => false,
            'default_start_time' => '08:00',
            'default_end_time' => '16:00',
            'default_available_days' => [1, 2, 3], // Monday, Tuesday, Wednesday
        ]);
        
        $response->assertRedirect();
        
        $this->trainer->refresh();
        $this->assertFalse($this->trainer->is_available_by_default);
        $this->assertEquals('08:00:00', $this->trainer->default_start_time->format('H:i:s'));
        $this->assertEquals('16:00:00', $this->trainer->default_end_time->format('H:i:s'));
        $this->assertEquals([1, 2, 3], $this->trainer->default_available_days);
    }

    /**
     * Test trainer availability affects schedule visibility
     */
    public function test_trainer_availability_affects_schedule_visibility()
    {
        // Create a schedule with an unavailable trainer
        $unavailableTrainerUser = User::factory()->create(['role' => 'trainer']);
        $unavailableTrainer = Trainer::factory()->create([
            'user_id' => $unavailableTrainerUser->id,
            'is_available_by_default' => false,
        ]);
        
        $unavailableSchedule = Schedule::factory()->create([
            'trainer_id' => $unavailableTrainerUser->id,
            'category_id' => $this->category->id,
            'status' => 'active',
        ]);
        
        // Schedule should not be available when trainer is not available by default
        $this->assertFalse($unavailableSchedule->isTrainerAvailable());
    }

    /**
     * Test trainer availability with multiple schedules
     */
    public function test_trainer_availability_with_multiple_schedules()
    {
        $secondSchedule = Schedule::factory()->create([
            'trainer_id' => $this->trainerUser->id,
            'category_id' => $this->category->id,
            'status' => 'active',
            'start_date' => now()->addDays(2),
            'end_date' => now()->addDays(32),
            'start_time' => now()->addDays(2)->setTime(14, 0),
            'end_time' => now()->addDays(2)->setTime(15, 0),
        ]);
        
        $unavailableDate = now()->addDays(1)->format('Y-m-d');
        
        // Create unavailability for specific schedule
        TrainerUnavailability::create([
            'trainer_id' => $this->trainer->user_id,
            'schedule_id' => $this->schedule->id,
            'date' => $unavailableDate,
            'start_time' => $unavailableDate . ' 09:00:00',
            'end_time' => $unavailableDate . ' 18:00:00',
        ]);
        
        // First schedule should be unavailable for the specific date
        $this->assertFalse($this->trainer->isAvailable($unavailableDate, '10:00:00', $this->schedule->id));
        
        // Second schedule should still be available for the specific date
        $this->assertTrue($this->trainer->isAvailable($unavailableDate, '10:00:00', $secondSchedule->id));
    }

    /**
     * Test trainer availability with all-day unavailability
     */
    public function test_trainer_all_day_unavailability()
    {
        $unavailableDate = now()->addDays(1)->format('Y-m-d');
        
        // Create all-day unavailability
        TrainerUnavailability::create([
            'trainer_id' => $this->trainer->user_id,
            'schedule_id' => $this->schedule->id,
            'date' => $unavailableDate,
            'start_time' => null, // All day
            'end_time' => null, // All day
            'reason' => 'vacation',
        ]);
        
        // Trainer should not be available at any time on that date
        $this->assertFalse($this->trainer->isAvailable($unavailableDate, '10:00:00', $this->schedule->id));
        $this->assertFalse($this->trainer->isAvailable($unavailableDate, '14:00:00', $this->schedule->id));
        $this->assertFalse($this->trainer->isAvailable($unavailableDate, '16:00:00', $this->schedule->id));
    }

    /**
     * Test trainer availability with time-specific unavailability
     */
    public function test_trainer_time_specific_unavailability()
    {
        $unavailableDate = now()->addDays(1)->format('Y-m-d');
        
        // Create time-specific unavailability (10 AM to 12 PM)
        TrainerUnavailability::create([
            'trainer_id' => $this->trainer->user_id,
            'schedule_id' => $this->schedule->id,
            'date' => $unavailableDate,
            'start_time' => $unavailableDate . ' 10:00:00',
            'end_time' => $unavailableDate . ' 12:00:00',
            'reason' => 'personal',
        ]);
        
        // Trainer should not be available during the specified time
        $this->assertFalse($this->trainer->isAvailable($unavailableDate, '10:30:00', $this->schedule->id));
        $this->assertFalse($this->trainer->isAvailable($unavailableDate, '11:00:00', $this->schedule->id));
        
        // Trainer should be available outside the specified time
        $this->assertTrue($this->trainer->isAvailable($unavailableDate, '09:00:00', $this->schedule->id));
        $this->assertTrue($this->trainer->isAvailable($unavailableDate, '14:00:00', $this->schedule->id));
    }

    /**
     * Test trainer availability affects booking validation
     */
    public function test_trainer_availability_affects_booking_validation()
    {
        $this->actingAs($this->user);
        
        $unavailableDate = now()->addDays(1)->format('Y-m-d');
        
        // Create unavailability for the trainer
        TrainerUnavailability::create([
            'trainer_id' => $this->trainer->user_id,
            'schedule_id' => $this->schedule->id,
            'date' => $unavailableDate,
            'start_time' => $unavailableDate . ' 09:00:00',
            'end_time' => $unavailableDate . ' 18:00:00',
            'reason' => 'sick',
        ]);
        
        // Try to book when trainer is unavailable
        $response = $this->post("/bookings/{$this->schedule->id}", [
            'booking_date' => $unavailableDate,
        ]);
        
        // Should get error about trainer unavailability
        $response->assertSessionHas('error');
    }

    /**
     * Test trainer availability with recurring patterns
     */
    public function test_trainer_availability_recurring_patterns()
    {
        $this->actingAs($this->admin);
        
        $startDate = now()->addDays(1)->format('Y-m-d');
        $endDate = now()->addDays(14)->format('Y-m-d');
        
        $response = $this->post("/admin/trainer-availability/{$this->schedule->id}/create-recurring", [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'days_of_week' => [1, 3, 5], // Monday, Wednesday, Friday
            'start_time' => '09:00',
            'end_time' => '10:00',
            'status' => 'unavailable',
        ]);
        
        $response->assertRedirect();
        
        // Should create unavailability for Monday, Wednesday, Friday for 2 weeks
        $this->assertDatabaseHas('trainer_unavailabilities', [
            'trainer_id' => $this->trainer->user_id,
            'schedule_id' => $this->schedule->id,
        ]);
    }

    /**
     * Test trainer availability export functionality
     */
    public function test_trainer_availability_export()
    {
        $this->actingAs($this->admin);
        
        // Create some unavailability records
        TrainerUnavailability::create([
            'trainer_id' => $this->trainer->user_id,
            'schedule_id' => $this->schedule->id,
            'date' => now()->addDays(1)->format('Y-m-d'),
            'start_time' => now()->addDays(1)->format('Y-m-d') . ' 10:00:00',
            'end_time' => now()->addDays(1)->format('Y-m-d') . ' 12:00:00',
            'reason' => 'personal',
        ]);
        
        $response = $this->get("/admin/trainer-availability/{$this->schedule->id}/export");
        
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }
} 