<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Trainer;
use App\Models\TrainerUnavailability;
use App\Models\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class TrainerAvailabilityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->trainer = Trainer::factory()->create([
            'default_available_days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'],
        ]);
    }

    public function test_trainer_default_availability()
    {
        $this->actingAs($this->admin);

        $response = $this->get('/admin/trainer-availability');

        $response->assertStatus(200);
        $response->assertSee($this->trainer->user->name);
    }

    public function test_trainer_unavailability_creation()
    {
        $this->actingAs($this->trainer->user);

        $response = $this->post('/trainer/unavailability', [
            'day_of_week' => 'friday',
            'start_time' => '09:00',
            'end_time' => '17:00',
            'reason' => 'Personal day',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('trainer_unavailabilities', [
            'trainer_id' => $this->trainer->id,
            'day_of_week' => 'friday',
            'start_time' => '09:00',
            'end_time' => '17:00',
        ]);
    }

    public function test_trainer_unavailability_update()
    {
        $this->actingAs($this->trainer->user);
        
        $unavailability = TrainerUnavailability::factory()->create([
            'trainer_id' => $this->trainer->id,
            'day_of_week' => 'friday',
        ]);

        $response = $this->put("/trainer/unavailability/{$unavailability->id}", [
            'day_of_week' => 'saturday',
            'start_time' => '10:00',
            'end_time' => '18:00',
            'reason' => 'Updated reason',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('trainer_unavailabilities', [
            'id' => $unavailability->id,
            'day_of_week' => 'saturday',
            'start_time' => '10:00',
            'end_time' => '18:00',
        ]);
    }

    public function test_trainer_unavailability_deletion()
    {
        $this->actingAs($this->trainer->user);
        
        $unavailability = TrainerUnavailability::factory()->create([
            'trainer_id' => $this->trainer->id,
        ]);

        $response = $this->delete("/trainer/unavailability/{$unavailability->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('trainer_unavailabilities', [
            'id' => $unavailability->id,
        ]);
    }

    public function test_trainer_availability_calendar_display()
    {
        $this->actingAs($this->admin);

        $response = $this->get('/admin/trainer-availability/calendar');

        $response->assertStatus(200);
        $response->assertSee($this->trainer->user->name);
    }

    public function test_trainer_calendar_with_unavailability()
    {
        $this->actingAs($this->admin);
        
        // Create unavailability for Friday
        TrainerUnavailability::factory()->create([
            'trainer_id' => $this->trainer->id,
            'day_of_week' => 'friday',
        ]);

        $response = $this->get('/admin/trainer-availability/calendar');

        $response->assertStatus(200);
        // Should show trainer as unavailable on Fridays
        $response->assertSee('No availability');
    }

    public function test_trainer_calendar_filter()
    {
        $this->actingAs($this->admin);
        
        $trainer2 = Trainer::factory()->create();

        $response = $this->get('/admin/trainer-availability/calendar?trainer_id=' . $this->trainer->id);

        $response->assertStatus(200);
        $response->assertSee($this->trainer->user->name);
        $response->assertDontSee($trainer2->user->name);
    }

    public function test_trainer_availability_settings()
    {
        $this->actingAs($this->trainer->user);

        $response = $this->get('/trainer/availability/settings');

        $response->assertStatus(200);
        $response->assertSee('Availability Settings');
    }

    public function test_trainer_availability_settings_update()
    {
        $this->actingAs($this->trainer->user);

        $response = $this->put('/trainer/availability/settings', [
            'default_available_days' => ['monday', 'tuesday', 'wednesday', 'thursday'],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('trainers', [
            'id' => $this->trainer->id,
            'default_available_days' => json_encode(['monday', 'tuesday', 'wednesday', 'thursday']),
        ]);
    }

    public function test_trainer_availability_for_specific_date()
    {
        $this->actingAs($this->admin);
        
        // Create unavailability for Friday
        TrainerUnavailability::factory()->create([
            'trainer_id' => $this->trainer->id,
            'day_of_week' => 'friday',
        ]);

        // Test Friday (should be unavailable)
        $friday = Carbon::now()->next(Carbon::FRIDAY);
        $response = $this->get("/admin/trainer-availability/calendar?date={$friday->format('Y-m-d')}");

        $response->assertStatus(200);
        $response->assertSee('No availability');

        // Test Monday (should be available)
        $monday = Carbon::now()->next(Carbon::MONDAY);
        $response = $this->get("/admin/trainer-availability/calendar?date={$monday->format('Y-m-d')}");

        $response->assertStatus(200);
        $response->assertSee($this->trainer->user->name);
    }

    public function test_trainer_availability_validation()
    {
        $this->actingAs($this->trainer->user);

        $response = $this->post('/trainer/unavailability', [
            'day_of_week' => '',
            'start_time' => '',
            'end_time' => '',
        ]);

        $response->assertSessionHasErrors(['day_of_week', 'start_time', 'end_time']);
    }

    public function test_trainer_availability_time_conflict()
    {
        $this->actingAs($this->trainer->user);
        
        // Create first unavailability
        TrainerUnavailability::factory()->create([
            'trainer_id' => $this->trainer->id,
            'day_of_week' => 'friday',
            'start_time' => '09:00',
            'end_time' => '12:00',
        ]);

        // Try to create overlapping unavailability
        $response = $this->post('/trainer/unavailability', [
            'day_of_week' => 'friday',
            'start_time' => '10:00',
            'end_time' => '14:00',
            'reason' => 'Overlapping time',
        ]);

        $response->assertSessionHasErrors();
    }

    public function test_trainer_availability_calendar_navigation()
    {
        $this->actingAs($this->admin);

        $nextMonth = Carbon::now()->addMonth();
        $response = $this->get("/admin/trainer-availability/calendar?month={$nextMonth->month}&year={$nextMonth->year}");

        $response->assertStatus(200);
        $response->assertSee($nextMonth->format('F Y'));
    }

    public function test_trainer_availability_export()
    {
        $this->actingAs($this->admin);

        $response = $this->get('/admin/trainer-availability/export');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv');
    }

    public function test_trainer_availability_bulk_operations()
    {
        $this->actingAs($this->admin);
        
        $trainer2 = Trainer::factory()->create();

        $response = $this->post('/admin/trainer-availability/bulk-update', [
            'trainer_ids' => [$this->trainer->id, $trainer2->id],
            'action' => 'set_unavailable',
            'day_of_week' => 'sunday',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('trainer_unavailabilities', [
            'trainer_id' => $this->trainer->id,
            'day_of_week' => 'sunday',
        ]);
        $this->assertDatabaseHas('trainer_unavailabilities', [
            'trainer_id' => $trainer2->id,
            'day_of_week' => 'sunday',
        ]);
    }
} 