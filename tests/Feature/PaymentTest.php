<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Trainer;
use App\Models\Schedule;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

class PaymentTest extends TestCase
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
            'price' => 50.00,
        ]);
    }

    public function test_user_can_make_payment()
    {
        $this->actingAs($this->user);
        
        $booking = Booking::factory()->create([
            'user_id' => $this->user->id,
            'schedule_id' => $this->schedule->id,
            'status' => 'confirmed',
        ]);

        $response = $this->post('/payments', [
            'booking_id' => $booking->id,
            'amount' => 50.00,
            'payment_method' => 'credit_card',
            'transaction_id' => 'TXN123456',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('payments', [
            'user_id' => $this->user->id,
            'booking_id' => $booking->id,
            'amount' => 50.00,
            'payment_method' => 'credit_card',
            'status' => 'completed',
        ]);
    }

    public function test_payment_for_unlimited_schedule()
    {
        $this->actingAs($this->user);
        
        $unlimitedSchedule = Schedule::factory()->create([
            'is_unlimited' => true,
            'unlimited_price' => 100.00,
            'status' => 'active',
        ]);

        $response = $this->post('/payments', [
            'schedule_id' => $unlimitedSchedule->id,
            'amount' => 100.00,
            'payment_method' => 'paypal',
            'transaction_id' => 'TXN789012',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('payments', [
            'schedule_id' => $unlimitedSchedule->id,
            'amount' => 100.00,
            'payment_method' => 'paypal',
        ]);
    }

    public function test_payment_validation_errors()
    {
        $this->actingAs($this->user);

        $response = $this->post('/payments', [
            'booking_id' => '',
            'amount' => '',
            'payment_method' => '',
        ]);

        $response->assertSessionHasErrors(['booking_id', 'amount', 'payment_method']);
    }

    public function test_payment_amount_validation()
    {
        $this->actingAs($this->user);
        
        $booking = Booking::factory()->create([
            'user_id' => $this->user->id,
            'schedule_id' => $this->schedule->id,
            'status' => 'confirmed',
        ]);

        $response = $this->post('/payments', [
            'booking_id' => $booking->id,
            'amount' => 25.00, // Less than schedule price
            'payment_method' => 'credit_card',
        ]);

        $response->assertSessionHasErrors(['amount']);
    }

    public function test_payment_success_page()
    {
        $this->actingAs($this->user);
        
        $payment = Payment::factory()->create([
            'user_id' => $this->user->id,
            'booking_id' => Booking::factory()->create([
                'user_id' => $this->user->id,
                'schedule_id' => $this->schedule->id,
            ])->id,
            'status' => 'completed',
        ]);

        $response = $this->get("/payments/{$payment->id}/success");

        $response->assertStatus(200);
        $response->assertSee('Payment Successful');
    }

    public function test_payment_failure_handling()
    {
        $this->actingAs($this->user);
        
        $booking = Booking::factory()->create([
            'user_id' => $this->user->id,
            'schedule_id' => $this->schedule->id,
            'status' => 'confirmed',
        ]);

        $response = $this->post('/payments', [
            'booking_id' => $booking->id,
            'amount' => 50.00,
            'payment_method' => 'credit_card',
            'transaction_id' => 'FAILED_TXN',
            'status' => 'failed',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('payments', [
            'booking_id' => $booking->id,
            'status' => 'failed',
        ]);
    }

    public function test_admin_can_view_all_payments()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);
        
        $payment = Payment::factory()->create([
            'user_id' => $this->user->id,
            'booking_id' => Booking::factory()->create([
                'user_id' => $this->user->id,
                'schedule_id' => $this->schedule->id,
            ])->id,
        ]);

        $response = $this->get('/admin/payments');

        $response->assertStatus(200);
        $response->assertSee($this->user->name);
        $response->assertSee($payment->amount);
    }

    public function test_payment_notification_sent()
    {
        Notification::fake();
        
        $this->actingAs($this->user);
        
        $booking = Booking::factory()->create([
            'user_id' => $this->user->id,
            'schedule_id' => $this->schedule->id,
            'status' => 'confirmed',
        ]);

        $response = $this->post('/payments', [
            'booking_id' => $booking->id,
            'amount' => 50.00,
            'payment_method' => 'credit_card',
            'transaction_id' => 'TXN123456',
        ]);

        Notification::assertSentTo($this->user, \App\Notifications\PaymentReceivedNotification::class);
    }

    public function test_payment_refund()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);
        
        $payment = Payment::factory()->create([
            'user_id' => $this->user->id,
            'booking_id' => Booking::factory()->create([
                'user_id' => $this->user->id,
                'schedule_id' => $this->schedule->id,
            ])->id,
            'status' => 'completed',
        ]);

        $response = $this->patch("/admin/payments/{$payment->id}/refund", [
            'refund_amount' => 50.00,
            'refund_reason' => 'Customer request',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => 'refunded',
        ]);
    }

    public function test_payment_export()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);
        
        $payment = Payment::factory()->create([
            'user_id' => $this->user->id,
            'booking_id' => Booking::factory()->create([
                'user_id' => $this->user->id,
                'schedule_id' => $this->schedule->id,
            ])->id,
        ]);

        $response = $this->get('/admin/payments/export');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv');
    }

    public function test_payment_statistics()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);
        
        $payment = Payment::factory()->create([
            'user_id' => $this->user->id,
            'booking_id' => Booking::factory()->create([
                'user_id' => $this->user->id,
                'schedule_id' => $this->schedule->id,
            ])->id,
            'amount' => 50.00,
        ]);

        $response = $this->get('/admin/payments/statistics');

        $response->assertStatus(200);
        $response->assertSee('Payment Statistics');
    }

    public function test_payment_methods()
    {
        $this->actingAs($this->user);
        
        $booking = Booking::factory()->create([
            'user_id' => $this->user->id,
            'schedule_id' => $this->schedule->id,
            'status' => 'confirmed',
        ]);

        $paymentMethods = ['credit_card', 'paypal', 'bank_transfer', 'cash'];

        foreach ($paymentMethods as $method) {
            $response = $this->post('/payments', [
                'booking_id' => $booking->id,
                'amount' => 50.00,
                'payment_method' => $method,
                'transaction_id' => "TXN_{$method}_" . time(),
            ]);

            $response->assertRedirect();
            $this->assertDatabaseHas('payments', [
                'payment_method' => $method,
            ]);
        }
    }

    public function test_payment_with_discount()
    {
        $this->actingAs($this->user);
        
        $booking = Booking::factory()->create([
            'user_id' => $this->user->id,
            'schedule_id' => $this->schedule->id,
            'status' => 'confirmed',
        ]);

        $response = $this->post('/payments', [
            'booking_id' => $booking->id,
            'amount' => 40.00, // Discounted amount
            'payment_method' => 'credit_card',
            'transaction_id' => 'TXN_DISCOUNT',
            'discount_amount' => 10.00,
            'discount_code' => 'SAVE10',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('payments', [
            'booking_id' => $booking->id,
            'amount' => 40.00,
            'discount_amount' => 10.00,
            'discount_code' => 'SAVE10',
        ]);
    }
} 