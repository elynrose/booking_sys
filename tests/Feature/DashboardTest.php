<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Trainer;
use App\Models\Schedule;
use App\Models\Booking;
use App\Models\Checkin;
use App\Models\Payment;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->user = User::factory()->create(['role' => 'user']);
        $this->trainer = Trainer::factory()->create();
        $this->category = Category::factory()->create();
        $this->schedule = Schedule::factory()->create([
            'trainer_id' => $this->trainer->id,
            'category_id' => $this->category->id,
            'status' => 'active',
        ]);
    }

    public function test_admin_dashboard_displays()
    {
        $this->actingAs($this->admin);

        $response = $this->get('/admin/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Dashboard');
    }

    public function test_admin_dashboard_statistics()
    {
        $this->actingAs($this->admin);
        
        // Create test data
        $booking = Booking::factory()->create([
            'user_id' => $this->user->id,
            'schedule_id' => $this->schedule->id,
            'status' => 'confirmed',
        ]);

        $checkin = Checkin::factory()->create([
            'user_id' => $this->user->id,
            'schedule_id' => $this->schedule->id,
        ]);

        $payment = Payment::factory()->create([
            'user_id' => $this->user->id,
            'booking_id' => $booking->id,
            'amount' => 50.00,
        ]);

        $response = $this->get('/admin/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Total Bookings');
        $response->assertSee('Total Check-ins');
        $response->assertSee('Total Revenue');
    }

    public function test_admin_live_dashboard()
    {
        $this->actingAs($this->admin);

        $response = $this->get('/admin/live-dashboard');

        $response->assertStatus(200);
        $response->assertSee('Live Dashboard');
    }

    public function test_user_dashboard_displays()
    {
        $this->actingAs($this->user);

        $response = $this->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('My Dashboard');
    }

    public function test_user_dashboard_bookings()
    {
        $this->actingAs($this->user);
        
        $booking = Booking::factory()->create([
            'user_id' => $this->user->id,
            'schedule_id' => $this->schedule->id,
        ]);

        $response = $this->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee($this->schedule->title);
    }

    public function test_user_dashboard_checkins()
    {
        $this->actingAs($this->user);
        
        $checkin = Checkin::factory()->create([
            'user_id' => $this->user->id,
            'schedule_id' => $this->schedule->id,
        ]);

        $response = $this->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Recent Check-ins');
    }

    public function test_user_dashboard_payments()
    {
        $this->actingAs($this->user);
        
        $booking = Booking::factory()->create([
            'user_id' => $this->user->id,
            'schedule_id' => $this->schedule->id,
        ]);

        $payment = Payment::factory()->create([
            'user_id' => $this->user->id,
            'booking_id' => $booking->id,
            'amount' => 50.00,
        ]);

        $response = $this->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Recent Payments');
    }

    public function test_trainer_dashboard()
    {
        $this->actingAs($this->trainer->user);

        $response = $this->get('/trainer/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Trainer Dashboard');
    }

    public function test_trainer_dashboard_schedules()
    {
        $this->actingAs($this->trainer->user);
        
        $schedule = Schedule::factory()->create([
            'trainer_id' => $this->trainer->id,
            'category_id' => $this->category->id,
            'status' => 'active',
        ]);

        $response = $this->get('/trainer/dashboard');

        $response->assertStatus(200);
        $response->assertSee($schedule->title);
    }

    public function test_dashboard_recent_activities()
    {
        $this->actingAs($this->admin);
        
        $booking = Booking::factory()->create([
            'user_id' => $this->user->id,
            'schedule_id' => $this->schedule->id,
        ]);

        $response = $this->get('/admin/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Recent Activities');
    }

    public function test_dashboard_charts()
    {
        $this->actingAs($this->admin);
        
        // Create multiple bookings for chart data
        for ($i = 0; $i < 5; $i++) {
            Booking::factory()->create([
                'user_id' => $this->user->id,
                'schedule_id' => $this->schedule->id,
                'created_at' => now()->subDays($i),
            ]);
        }

        $response = $this->get('/admin/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Booking Trends');
    }

    public function test_dashboard_revenue_chart()
    {
        $this->actingAs($this->admin);
        
        // Create multiple payments for revenue chart
        for ($i = 0; $i < 5; $i++) {
            $booking = Booking::factory()->create([
                'user_id' => $this->user->id,
                'schedule_id' => $this->schedule->id,
            ]);

            Payment::factory()->create([
                'user_id' => $this->user->id,
                'booking_id' => $booking->id,
                'amount' => 50.00 * ($i + 1),
                'created_at' => now()->subDays($i),
            ]);
        }

        $response = $this->get('/admin/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Revenue Overview');
    }

    public function test_dashboard_quick_actions()
    {
        $this->actingAs($this->admin);

        $response = $this->get('/admin/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Quick Actions');
    }

    public function test_dashboard_notifications()
    {
        $this->actingAs($this->admin);
        
        $booking = Booking::factory()->create([
            'user_id' => $this->user->id,
            'schedule_id' => $this->schedule->id,
            'status' => 'pending',
        ]);

        $response = $this->get('/admin/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Pending Bookings');
    }

    public function test_dashboard_export_functionality()
    {
        $this->actingAs($this->admin);

        $response = $this->get('/admin/dashboard/export');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv');
    }

    public function test_dashboard_search()
    {
        $this->actingAs($this->admin);
        
        $schedule1 = Schedule::factory()->create(['title' => 'Yoga Class']);
        $schedule2 = Schedule::factory()->create(['title' => 'Pilates Class']);

        $response = $this->get('/admin/dashboard?search=yoga');

        $response->assertStatus(200);
        $response->assertSee('Yoga Class');
        $response->assertDontSee('Pilates Class');
    }

    public function test_dashboard_filters()
    {
        $this->actingAs($this->admin);
        
        $booking1 = Booking::factory()->create([
            'status' => 'confirmed',
            'created_at' => now(),
        ]);

        $booking2 = Booking::factory()->create([
            'status' => 'pending',
            'created_at' => now()->subDays(7),
        ]);

        $response = $this->get('/admin/dashboard?status=confirmed&date_range=this_week');

        $response->assertStatus(200);
        $response->assertSee($booking1->id);
        $response->assertDontSee($booking2->id);
    }

    public function test_dashboard_mobile_responsive()
    {
        $this->actingAs($this->admin);

        $response = $this->get('/admin/dashboard');

        $response->assertStatus(200);
        $response->assertSee('mobile-friendly');
    }
} 