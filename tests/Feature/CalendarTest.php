<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Trainer;
use App\Models\TrainerUnavailability;
use App\Models\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class CalendarTest extends TestCase
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

    public function test_admin_trainer_calendar_displays()
    {
        $this->actingAs($this->admin);

        $response = $this->get('/admin/trainer-calendar');

        $response->assertStatus(200);
        $response->assertSee('Trainer Calendar');
    }

    public function test_trainer_calendar_with_sunday_start()
    {
        $this->actingAs($this->admin);

        $response = $this->get('/admin/trainer-calendar?year=2025&month=7');

        $response->assertStatus(200);
        // July 4, 2025 should be a Friday
        $response->assertSee('4');
    }

    public function test_trainer_calendar_filter()
    {
        $this->actingAs($this->admin);
        
        $trainer2 = Trainer::factory()->create();

        $response = $this->get('/admin/trainer-calendar?trainer_id=' . $this->trainer->id);

        $response->assertStatus(200);
        $response->assertSee($this->trainer->user->name);
        $response->assertDontSee($trainer2->user->name);
    }

    public function test_trainer_calendar_navigation()
    {
        $this->actingAs($this->admin);

        $nextMonth = Carbon::now()->addMonth();
        $response = $this->get("/admin/trainer-calendar?month={$nextMonth->month}&year={$nextMonth->year}");

        $response->assertStatus(200);
        $response->assertSee($nextMonth->format('F Y'));
    }

    public function test_trainer_calendar_with_unavailability()
    {
        $this->actingAs($this->admin);
        
        // Create unavailability for Friday
        TrainerUnavailability::factory()->create([
            'trainer_id' => $this->trainer->id,
            'day_of_week' => 'friday',
        ]);

        $response = $this->get('/admin/trainer-calendar');

        $response->assertStatus(200);
        // Should show trainer as unavailable on Fridays
        $response->assertSee('No availability');
    }

    public function test_trainer_calendar_availability_display()
    {
        $this->actingAs($this->admin);
        
        // Trainer should be available on other days
        $monday = Carbon::now()->next(Carbon::MONDAY);
        $response = $this->get("/admin/trainer-calendar?date={$monday->format('Y-m-d')}");

        $response->assertStatus(200);
        $response->assertSee($this->trainer->user->name);
    }

    public function test_frontend_trainer_calendar()
    {
        $this->actingAs($this->trainer->user);

        $response = $this->get('/trainer/availability/calendar');

        $response->assertStatus(200);
        $response->assertSee('Availability Calendar');
    }

    public function test_frontend_trainer_calendar_unavailability()
    {
        $this->actingAs($this->trainer->user);
        
        // Create unavailability
        TrainerUnavailability::factory()->create([
            'trainer_id' => $this->trainer->id,
            'day_of_week' => 'friday',
        ]);

        $response = $this->get('/trainer/availability/calendar');

        $response->assertStatus(200);
        $response->assertSee('Unavailable');
    }

    public function test_calendar_week_start_sunday()
    {
        $this->actingAs($this->admin);

        $response = $this->get('/admin/trainer-calendar');

        $response->assertStatus(200);
        // Check that Sunday is the first day of the week
        $response->assertSeeInOrder(['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']);
    }

    public function test_calendar_date_calculation()
    {
        $this->actingAs($this->admin);

        // Test July 2025 calendar
        $response = $this->get('/admin/trainer-calendar?year=2025&month=7');

        $response->assertStatus(200);
        // July 4, 2025 is a Friday
        $response->assertSee('4');
    }

    public function test_calendar_trainer_colors()
    {
        $this->actingAs($this->admin);
        
        $trainer2 = Trainer::factory()->create();

        $response = $this->get('/admin/trainer-calendar');

        $response->assertStatus(200);
        // Should have different colors for different trainers
        $response->assertSee('trainer-color');
    }

    public function test_calendar_click_events()
    {
        $this->actingAs($this->admin);

        $response = $this->get('/admin/trainer-calendar');

        $response->assertStatus(200);
        // Should have clickable trainer slots
        $response->assertSee('trainer-slot');
    }

    public function test_calendar_legend()
    {
        $this->actingAs($this->admin);

        $response = $this->get('/admin/trainer-calendar');

        $response->assertStatus(200);
        $response->assertSee('Legend');
    }

    public function test_calendar_export()
    {
        $this->actingAs($this->admin);

        $response = $this->get('/admin/trainer-calendar/export');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv');
    }

    public function test_calendar_print_view()
    {
        $this->actingAs($this->admin);

        $response = $this->get('/admin/trainer-calendar/print');

        $response->assertStatus(200);
        $response->assertSee('Print Calendar');
    }

    public function test_calendar_mobile_responsive()
    {
        $this->actingAs($this->admin);

        $response = $this->get('/admin/trainer-calendar');

        $response->assertStatus(200);
        $response->assertSee('mobile-calendar');
    }

    public function test_calendar_ajax_loading()
    {
        $this->actingAs($this->admin);

        $response = $this->get('/admin/trainer-calendar/ajax-data');

        $response->assertStatus(200);
        $response->assertJsonStructure(['trainers', 'unavailabilities']);
    }

    public function test_calendar_bulk_operations()
    {
        $this->actingAs($this->admin);
        
        $trainer2 = Trainer::factory()->create();

        $response = $this->post('/admin/trainer-calendar/bulk-update', [
            'trainer_ids' => [$this->trainer->id, $trainer2->id],
            'action' => 'set_unavailable',
            'day_of_week' => 'sunday',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('trainer_unavailabilities', [
            'trainer_id' => $this->trainer->id,
            'day_of_week' => 'sunday',
        ]);
    }
} 