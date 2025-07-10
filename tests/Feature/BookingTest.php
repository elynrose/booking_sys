<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Trainer;
use App\Models\Schedule;
use App\Models\Booking;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

class BookingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test data
        $this->user = User::factory()->create(['role' => 'user']);
        $this->trainer = Trainer::factory()->create();
        $this->category = Category::factory()->create();
        $this->schedule = Schedule::factory()->create([
            'trainer_id' => $this->trainer->id,
            'category_id' => $this->category->id,
            'status' => 'active',
        ]);
    }

    public function test_user_can_create_booking()
    {
        $this->actingAs($this->user);

        $response = $this->post('/bookings', [
            'schedule_id' => $this->schedule->id,
            'booking_date' => now()->addDays(1)->format('Y-m-d'),
            'notes' => 'Test booking',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('bookings', [
            'user_id' => $this->user->id,
            'schedule_id' => $this->schedule->id,
            'status' => 'pending',
        ]);
    }

    public function test_user_cannot_book_expired_schedule()
    {
        $this->actingAs($this->user);
        
        $expiredSchedule = Schedule::factory()->create([
            'start_date' => now()->subDays(1),
            'end_date' => now()->subDays(1),
            'status' => 'active',
        ]);

        $response = $this->post('/bookings', [
            'schedule_id' => $expiredSchedule->id,
            'booking_date' => now()->format('Y-m-d'),
        ]);

        $response->assertSessionHasErrors();
        $this->assertDatabaseMissing('bookings', [
            'schedule_id' => $expiredSchedule->id,
        ]);
    }

    public function test_user_cannot_book_full_schedule()
    {
        $this->actingAs($this->user);
        
        $fullSchedule = Schedule::factory()->create([
            'max_participants' => 1,
            'status' => 'active',
        ]);

        // Create a booking to fill the schedule
        Booking::factory()->create([
            'schedule_id' => $fullSchedule->id,
            'status' => 'confirmed',
        ]);

        $response = $this->post('/bookings', [
            'schedule_id' => $fullSchedule->id,
            'booking_date' => now()->addDays(1)->format('Y-m-d'),
        ]);

        $response->assertSessionHasErrors();
    }

    public function test_user_can_cancel_booking()
    {
        $this->actingAs($this->user);
        
        $booking = Booking::factory()->create([
            'user_id' => $this->user->id,
            'schedule_id' => $this->schedule->id,
            'status' => 'confirmed',
        ]);

        $response = $this->delete("/bookings/{$booking->id}");

        $response->assertRedirect();
        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'cancelled',
        ]);
    }

    public function test_admin_can_confirm_booking()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);
        
        $booking = Booking::factory()->create([
            'schedule_id' => $this->schedule->id,
            'status' => 'pending',
        ]);

        $response = $this->patch("/admin/bookings/{$booking->id}", [
            'status' => 'confirmed',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'confirmed',
        ]);
    }

    public function test_admin_can_reject_booking()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);
        
        $booking = Booking::factory()->create([
            'schedule_id' => $this->schedule->id,
            'status' => 'pending',
        ]);

        $response = $this->patch("/admin/bookings/{$booking->id}", [
            'status' => 'rejected',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'rejected',
        ]);
    }

    public function test_booking_notification_sent()
    {
        Notification::fake();
        
        $this->actingAs($this->user);

        $response = $this->post('/bookings', [
            'schedule_id' => $this->schedule->id,
            'booking_date' => now()->addDays(1)->format('Y-m-d'),
        ]);

        Notification::assertSentTo($this->user, \App\Notifications\BookingCreatedNotification::class);
    }

    public function test_user_can_view_their_bookings()
    {
        $this->actingAs($this->user);
        
        $booking = Booking::factory()->create([
            'user_id' => $this->user->id,
            'schedule_id' => $this->schedule->id,
        ]);

        $response = $this->get('/bookings');

        $response->assertStatus(200);
        $response->assertSee($this->schedule->title);
    }

    public function test_admin_can_view_all_bookings()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);
        
        $booking = Booking::factory()->create([
            'schedule_id' => $this->schedule->id,
        ]);

        $response = $this->get('/admin/bookings');

        $response->assertStatus(200);
        $response->assertSee($this->schedule->title);
    }

    public function test_booking_with_unlimited_schedule()
    {
        $this->actingAs($this->user);
        
        $unlimitedSchedule = Schedule::factory()->create([
            'is_unlimited' => true,
            'status' => 'active',
        ]);

        $response = $this->post('/bookings', [
            'schedule_id' => $unlimitedSchedule->id,
            'booking_date' => now()->addDays(1)->format('Y-m-d'),
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('bookings', [
            'schedule_id' => $unlimitedSchedule->id,
        ]);
    }

    public function test_booking_validation_errors()
    {
        $this->actingAs($this->user);

        $response = $this->post('/bookings', [
            'schedule_id' => '',
            'booking_date' => '',
        ]);

        $response->assertSessionHasErrors(['schedule_id', 'booking_date']);
    }
} 