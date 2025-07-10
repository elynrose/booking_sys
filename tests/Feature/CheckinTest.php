<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Trainer;
use App\Models\Schedule;
use App\Models\Booking;
use App\Models\Checkin;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

class CheckinTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create(['role' => 'user']);
        $this->trainer = Trainer::factory()->create();
        $this->category = Category::factory()->create();
        $this->schedule = Schedule::factory()->create([
            'trainer_id' => $this->trainer->id,
            'category_id' => $this->category->id,
            'status' => 'active',
        ]);
    }

    public function test_user_can_check_in()
    {
        $this->actingAs($this->user);
        
        $booking = Booking::factory()->create([
            'user_id' => $this->user->id,
            'schedule_id' => $this->schedule->id,
            'status' => 'confirmed',
        ]);

        $response = $this->post('/checkins', [
            'booking_id' => $booking->id,
            'checkin_time' => now()->format('Y-m-d H:i:s'),
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('checkins', [
            'user_id' => $this->user->id,
            'booking_id' => $booking->id,
            'schedule_id' => $this->schedule->id,
        ]);
    }

    public function test_user_cannot_check_in_without_confirmed_booking()
    {
        $this->actingAs($this->user);
        
        $booking = Booking::factory()->create([
            'user_id' => $this->user->id,
            'schedule_id' => $this->schedule->id,
            'status' => 'pending',
        ]);

        $response = $this->post('/checkins', [
            'booking_id' => $booking->id,
            'checkin_time' => now()->format('Y-m-d H:i:s'),
        ]);

        $response->assertSessionHasErrors();
        $this->assertDatabaseMissing('checkins', [
            'booking_id' => $booking->id,
        ]);
    }

    public function test_user_cannot_check_in_twice_for_same_booking()
    {
        $this->actingAs($this->user);
        
        $booking = Booking::factory()->create([
            'user_id' => $this->user->id,
            'schedule_id' => $this->schedule->id,
            'status' => 'confirmed',
        ]);

        // First check-in
        $this->post('/checkins', [
            'booking_id' => $booking->id,
            'checkin_time' => now()->format('Y-m-d H:i:s'),
        ]);

        // Second check-in attempt
        $response = $this->post('/checkins', [
            'booking_id' => $booking->id,
            'checkin_time' => now()->format('Y-m-d H:i:s'),
        ]);

        $response->assertSessionHasErrors();
    }

    public function test_user_can_check_out()
    {
        $this->actingAs($this->user);
        
        $booking = Booking::factory()->create([
            'user_id' => $this->user->id,
            'schedule_id' => $this->schedule->id,
            'status' => 'confirmed',
        ]);

        $checkin = Checkin::factory()->create([
            'user_id' => $this->user->id,
            'booking_id' => $booking->id,
            'schedule_id' => $this->schedule->id,
            'checkout_time' => null,
        ]);

        $response = $this->patch("/checkins/{$checkin->id}/checkout", [
            'checkout_time' => now()->format('Y-m-d H:i:s'),
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('checkins', [
            'id' => $checkin->id,
            'checkout_time' => now()->format('Y-m-d H:i:s'),
        ]);
    }

    public function test_auto_checkout_for_unlimited_schedules()
    {
        $this->actingAs($this->user);
        
        $unlimitedSchedule = Schedule::factory()->create([
            'is_unlimited' => true,
            'status' => 'active',
        ]);

        $response = $this->post('/checkins', [
            'schedule_id' => $unlimitedSchedule->id,
            'checkin_time' => now()->format('Y-m-d H:i:s'),
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('checkins', [
            'schedule_id' => $unlimitedSchedule->id,
            'user_id' => $this->user->id,
        ]);
    }

    public function test_checkin_verification()
    {
        $this->actingAs($this->user);
        
        $booking = Booking::factory()->create([
            'user_id' => $this->user->id,
            'schedule_id' => $this->schedule->id,
            'status' => 'confirmed',
        ]);

        $response = $this->get("/checkins/verify/{$booking->id}");

        $response->assertStatus(200);
        $response->assertSee($this->schedule->title);
    }

    public function test_admin_can_view_all_checkins()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);
        
        $checkin = Checkin::factory()->create([
            'user_id' => $this->user->id,
            'schedule_id' => $this->schedule->id,
        ]);

        $response = $this->get('/admin/checkins');

        $response->assertStatus(200);
        $response->assertSee($this->user->name);
        $response->assertSee($this->schedule->title);
    }

    public function test_checkin_notification_sent()
    {
        Notification::fake();
        
        $this->actingAs($this->user);
        
        $booking = Booking::factory()->create([
            'user_id' => $this->user->id,
            'schedule_id' => $this->schedule->id,
            'status' => 'confirmed',
        ]);

        $response = $this->post('/checkins', [
            'booking_id' => $booking->id,
            'checkin_time' => now()->format('Y-m-d H:i:s'),
        ]);

        Notification::assertSentTo($this->user, \App\Notifications\CheckinNotification::class);
    }

    public function test_checkin_with_qr_code()
    {
        $this->actingAs($this->user);
        
        $booking = Booking::factory()->create([
            'user_id' => $this->user->id,
            'schedule_id' => $this->schedule->id,
            'status' => 'confirmed',
        ]);

        $response = $this->post('/checkins/qr', [
            'qr_code' => $booking->qr_code,
            'checkin_time' => now()->format('Y-m-d H:i:s'),
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('checkins', [
            'booking_id' => $booking->id,
        ]);
    }

    public function test_checkin_validation_errors()
    {
        $this->actingAs($this->user);

        $response = $this->post('/checkins', [
            'booking_id' => '',
            'checkin_time' => '',
        ]);

        $response->assertSessionHasErrors(['booking_id', 'checkin_time']);
    }

    public function test_checkin_for_expired_schedule()
    {
        $this->actingAs($this->user);
        
        $expiredSchedule = Schedule::factory()->create([
            'start_date' => now()->subDays(1),
            'end_date' => now()->subDays(1),
            'status' => 'active',
        ]);

        $booking = Booking::factory()->create([
            'user_id' => $this->user->id,
            'schedule_id' => $expiredSchedule->id,
            'status' => 'confirmed',
        ]);

        $response = $this->post('/checkins', [
            'booking_id' => $booking->id,
            'checkin_time' => now()->format('Y-m-d H:i:s'),
        ]);

        $response->assertSessionHasErrors();
    }

    public function test_checkin_time_validation()
    {
        $this->actingAs($this->user);
        
        $booking = Booking::factory()->create([
            'user_id' => $this->user->id,
            'schedule_id' => $this->schedule->id,
            'status' => 'confirmed',
        ]);

        // Try to check in before schedule start time
        $response = $this->post('/checkins', [
            'booking_id' => $booking->id,
            'checkin_time' => now()->subHours(2)->format('Y-m-d H:i:s'),
        ]);

        $response->assertSessionHasErrors();
    }

    public function test_checkin_export()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);
        
        $checkin = Checkin::factory()->create([
            'user_id' => $this->user->id,
            'schedule_id' => $this->schedule->id,
        ]);

        $response = $this->get('/admin/checkins/export');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv');
    }

    public function test_checkin_statistics()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);
        
        $checkin = Checkin::factory()->create([
            'user_id' => $this->user->id,
            'schedule_id' => $this->schedule->id,
        ]);

        $response = $this->get('/admin/checkins/statistics');

        $response->assertStatus(200);
        $response->assertSee('Check-in Statistics');
    }

    // NEW TESTS FOR DIFFERENT SCHEDULE TYPES

    public function test_checkin_for_regular_schedule_with_booking()
    {
        $this->actingAs($this->user);
        
        $regularSchedule = Schedule::factory()->create([
            'is_unlimited' => false,
            'max_participants' => 10,
            'status' => 'active',
        ]);

        $booking = Booking::factory()->create([
            'user_id' => $this->user->id,
            'schedule_id' => $regularSchedule->id,
            'status' => 'confirmed',
        ]);

        $response = $this->post('/checkins', [
            'booking_id' => $booking->id,
            'checkin_time' => now()->format('Y-m-d H:i:s'),
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('checkins', [
            'booking_id' => $booking->id,
            'schedule_id' => $regularSchedule->id,
        ]);
    }

    public function test_checkin_for_unlimited_schedule_without_booking()
    {
        $this->actingAs($this->user);
        
        $unlimitedSchedule = Schedule::factory()->create([
            'is_unlimited' => true,
            'unlimited_price' => 100.00,
            'status' => 'active',
        ]);

        $response = $this->post('/checkins', [
            'schedule_id' => $unlimitedSchedule->id,
            'checkin_time' => now()->format('Y-m-d H:i:s'),
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('checkins', [
            'schedule_id' => $unlimitedSchedule->id,
            'user_id' => $this->user->id,
            'booking_id' => null, // No booking required for unlimited
        ]);
    }

    public function test_checkin_for_unlimited_schedule_with_booking()
    {
        $this->actingAs($this->user);
        
        $unlimitedSchedule = Schedule::factory()->create([
            'is_unlimited' => true,
            'unlimited_price' => 100.00,
            'status' => 'active',
        ]);

        $booking = Booking::factory()->create([
            'user_id' => $this->user->id,
            'schedule_id' => $unlimitedSchedule->id,
            'status' => 'confirmed',
        ]);

        $response = $this->post('/checkins', [
            'booking_id' => $booking->id,
            'checkin_time' => now()->format('Y-m-d H:i:s'),
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('checkins', [
            'booking_id' => $booking->id,
            'schedule_id' => $unlimitedSchedule->id,
        ]);
    }

    public function test_cannot_checkin_unlimited_schedule_without_payment()
    {
        $this->actingAs($this->user);
        
        $unlimitedSchedule = Schedule::factory()->create([
            'is_unlimited' => true,
            'unlimited_price' => 100.00,
            'status' => 'active',
        ]);

        // Try to check in without payment
        $response = $this->post('/checkins', [
            'schedule_id' => $unlimitedSchedule->id,
            'checkin_time' => now()->format('Y-m-d H:i:s'),
        ]);

        // Should require payment first
        $response->assertSessionHasErrors();
    }

    public function test_checkin_for_free_unlimited_schedule()
    {
        $this->actingAs($this->user);
        
        $freeUnlimitedSchedule = Schedule::factory()->create([
            'is_unlimited' => true,
            'unlimited_price' => 0.00,
            'status' => 'active',
        ]);

        $response = $this->post('/checkins', [
            'schedule_id' => $freeUnlimitedSchedule->id,
            'checkin_time' => now()->format('Y-m-d H:i:s'),
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('checkins', [
            'schedule_id' => $freeUnlimitedSchedule->id,
            'user_id' => $this->user->id,
        ]);
    }

    public function test_checkin_for_group_class_schedule()
    {
        $this->actingAs($this->user);
        
        $groupSchedule = Schedule::factory()->create([
            'is_unlimited' => false,
            'max_participants' => 20,
            'title' => 'Group Yoga Class',
            'status' => 'active',
        ]);

        $booking = Booking::factory()->create([
            'user_id' => $this->user->id,
            'schedule_id' => $groupSchedule->id,
            'status' => 'confirmed',
        ]);

        $response = $this->post('/checkins', [
            'booking_id' => $booking->id,
            'checkin_time' => now()->format('Y-m-d H:i:s'),
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('checkins', [
            'booking_id' => $booking->id,
            'schedule_id' => $groupSchedule->id,
        ]);
    }

    public function test_checkin_for_private_session_schedule()
    {
        $this->actingAs($this->user);
        
        $privateSchedule = Schedule::factory()->create([
            'is_unlimited' => false,
            'max_participants' => 1,
            'title' => 'Private Training Session',
            'status' => 'active',
        ]);

        $booking = Booking::factory()->create([
            'user_id' => $this->user->id,
            'schedule_id' => $privateSchedule->id,
            'status' => 'confirmed',
        ]);

        $response = $this->post('/checkins', [
            'booking_id' => $booking->id,
            'checkin_time' => now()->format('Y-m-d H:i:s'),
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('checkins', [
            'booking_id' => $booking->id,
            'schedule_id' => $privateSchedule->id,
        ]);
    }

    public function test_checkin_for_recurring_schedule()
    {
        $this->actingAs($this->user);
        
        $recurringSchedule = Schedule::factory()->create([
            'is_unlimited' => false,
            'max_participants' => 15,
            'title' => 'Weekly Yoga Class',
            'recurring' => true,
            'recurring_days' => ['monday', 'wednesday', 'friday'],
            'status' => 'active',
        ]);

        $booking = Booking::factory()->create([
            'user_id' => $this->user->id,
            'schedule_id' => $recurringSchedule->id,
            'status' => 'confirmed',
        ]);

        $response = $this->post('/checkins', [
            'booking_id' => $booking->id,
            'checkin_time' => now()->format('Y-m-d H:i:s'),
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('checkins', [
            'booking_id' => $booking->id,
            'schedule_id' => $recurringSchedule->id,
        ]);
    }

    public function test_checkin_for_workshop_schedule()
    {
        $this->actingAs($this->user);
        
        $workshopSchedule = Schedule::factory()->create([
            'is_unlimited' => false,
            'max_participants' => 30,
            'title' => 'Fitness Workshop',
            'schedule_type' => 'workshop',
            'status' => 'active',
        ]);

        $booking = Booking::factory()->create([
            'user_id' => $this->user->id,
            'schedule_id' => $workshopSchedule->id,
            'status' => 'confirmed',
        ]);

        $response = $this->post('/checkins', [
            'booking_id' => $booking->id,
            'checkin_time' => now()->format('Y-m-d H:i:s'),
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('checkins', [
            'booking_id' => $booking->id,
            'schedule_id' => $workshopSchedule->id,
        ]);
    }

    public function test_checkin_for_seminar_schedule()
    {
        $this->actingAs($this->user);
        
        $seminarSchedule = Schedule::factory()->create([
            'is_unlimited' => false,
            'max_participants' => 50,
            'title' => 'Nutrition Seminar',
            'schedule_type' => 'seminar',
            'status' => 'active',
        ]);

        $booking = Booking::factory()->create([
            'user_id' => $this->user->id,
            'schedule_id' => $seminarSchedule->id,
            'status' => 'confirmed',
        ]);

        $response = $this->post('/checkins', [
            'booking_id' => $booking->id,
            'checkin_time' => now()->format('Y-m-d H:i:s'),
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('checkins', [
            'booking_id' => $booking->id,
            'schedule_id' => $seminarSchedule->id,
        ]);
    }

    public function test_checkin_for_trial_class_schedule()
    {
        $this->actingAs($this->user);
        
        $trialSchedule = Schedule::factory()->create([
            'is_unlimited' => false,
            'max_participants' => 10,
            'title' => 'Trial Class',
            'schedule_type' => 'trial',
            'price' => 0.00,
            'status' => 'active',
        ]);

        $booking = Booking::factory()->create([
            'user_id' => $this->user->id,
            'schedule_id' => $trialSchedule->id,
            'status' => 'confirmed',
        ]);

        $response = $this->post('/checkins', [
            'booking_id' => $booking->id,
            'checkin_time' => now()->format('Y-m-d H:i:s'),
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('checkins', [
            'booking_id' => $booking->id,
            'schedule_id' => $trialSchedule->id,
        ]);
    }

    public function test_checkin_for_membership_schedule()
    {
        $this->actingAs($this->user);
        
        $membershipSchedule = Schedule::factory()->create([
            'is_unlimited' => true,
            'unlimited_price' => 0.00,
            'title' => 'Membership Class',
            'schedule_type' => 'membership',
            'status' => 'active',
        ]);

        $response = $this->post('/checkins', [
            'schedule_id' => $membershipSchedule->id,
            'checkin_time' => now()->format('Y-m-d H:i:s'),
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('checkins', [
            'schedule_id' => $membershipSchedule->id,
            'user_id' => $this->user->id,
        ]);
    }

    public function test_checkin_for_drop_in_schedule()
    {
        $this->actingAs($this->user);
        
        $dropInSchedule = Schedule::factory()->create([
            'is_unlimited' => false,
            'max_participants' => 25,
            'title' => 'Drop-in Class',
            'schedule_type' => 'drop_in',
            'status' => 'active',
        ]);

        // For drop-in classes, users can check in without booking
        $response = $this->post('/checkins', [
            'schedule_id' => $dropInSchedule->id,
            'checkin_time' => now()->format('Y-m-d H:i:s'),
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('checkins', [
            'schedule_id' => $dropInSchedule->id,
            'user_id' => $this->user->id,
        ]);
    }

    public function test_checkin_for_full_schedule()
    {
        $this->actingAs($this->user);
        
        $fullSchedule = Schedule::factory()->create([
            'is_unlimited' => false,
            'max_participants' => 1,
            'title' => 'Full Class',
            'status' => 'active',
        ]);

        // Create a booking to fill the schedule
        $existingBooking = Booking::factory()->create([
            'schedule_id' => $fullSchedule->id,
            'status' => 'confirmed',
        ]);

        $checkin = Checkin::factory()->create([
            'user_id' => $existingBooking->user_id,
            'schedule_id' => $fullSchedule->id,
            'booking_id' => $existingBooking->id,
        ]);

        // Try to check in another user
        $newUser = User::factory()->create();
        $this->actingAs($newUser);

        $response = $this->post('/checkins', [
            'schedule_id' => $fullSchedule->id,
            'checkin_time' => now()->format('Y-m-d H:i:s'),
        ]);

        $response->assertSessionHasErrors();
        $this->assertDatabaseMissing('checkins', [
            'user_id' => $newUser->id,
            'schedule_id' => $fullSchedule->id,
        ]);
    }

    public function test_checkin_for_cancelled_schedule()
    {
        $this->actingAs($this->user);
        
        $cancelledSchedule = Schedule::factory()->create([
            'is_unlimited' => false,
            'max_participants' => 10,
            'title' => 'Cancelled Class',
            'status' => 'cancelled',
        ]);

        $booking = Booking::factory()->create([
            'user_id' => $this->user->id,
            'schedule_id' => $cancelledSchedule->id,
            'status' => 'confirmed',
        ]);

        $response = $this->post('/checkins', [
            'booking_id' => $booking->id,
            'checkin_time' => now()->format('Y-m-d H:i:s'),
        ]);

        $response->assertSessionHasErrors();
        $this->assertDatabaseMissing('checkins', [
            'booking_id' => $booking->id,
        ]);
    }

    public function test_checkin_for_inactive_schedule()
    {
        $this->actingAs($this->user);
        
        $inactiveSchedule = Schedule::factory()->create([
            'is_unlimited' => false,
            'max_participants' => 10,
            'title' => 'Inactive Class',
            'status' => 'inactive',
        ]);

        $booking = Booking::factory()->create([
            'user_id' => $this->user->id,
            'schedule_id' => $inactiveSchedule->id,
            'status' => 'confirmed',
        ]);

        $response = $this->post('/checkins', [
            'booking_id' => $booking->id,
            'checkin_time' => now()->format('Y-m-d H:i:s'),
        ]);

        $response->assertSessionHasErrors();
        $this->assertDatabaseMissing('checkins', [
            'booking_id' => $booking->id,
        ]);
    }
} 