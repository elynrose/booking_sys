<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Booking;
use App\Models\User;
use App\Notifications\PaymentConfirmedNotification;
use App\Notifications\PaymentReminderNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class PaymentService
{
    /**
     * Create a new payment
     */
    public function createPayment(array $data, User $user): Payment
    {
        try {
            DB::beginTransaction();
            
            $booking = Booking::findOrFail($data['booking_id']);
            
            // Verify booking belongs to user or user is admin
            if ($booking->user_id !== $user->id && !$user->hasRole('Admin')) {
                throw new Exception('Unauthorized to create payment for this booking.');
            }
            
            // Create payment
            $payment = Payment::create([
                'booking_id' => $booking->id,
                'user_id' => $user->id,
                'amount' => $data['amount'],
                'payment_method' => $data['payment_method'] ?? 'stripe',
                'status' => 'pending',
                'transaction_id' => $data['transaction_id'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);
            
            // Update booking payment status
            $booking->update(['payment_status' => 'pending']);
            
            DB::commit();
            
            Log::info('Payment created successfully', [
                'payment_id' => $payment->id,
                'booking_id' => $booking->id,
                'user_id' => $user->id,
                'amount' => $data['amount']
            ]);
            
            return $payment;
            
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Payment creation failed', [
                'user_id' => $user->id,
                'booking_id' => $data['booking_id'] ?? null,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
    
    /**
     * Mark payment as paid
     */
    public function markAsPaid(Payment $payment, User $admin): bool
    {
        try {
            DB::beginTransaction();
            
            // Verify admin permissions
            if (!$admin->hasRole('Admin')) {
                throw new Exception('Unauthorized to mark payment as paid.');
            }
            
            // Update payment status
            $payment->update([
                'status' => 'completed',
                'paid_at' => now(),
            ]);
            
            // Update booking payment status
            $booking = $payment->booking;
            $booking->update(['payment_status' => 'paid']);
            
            // Send notification
            $payment->user->notify(new PaymentConfirmedNotification($payment));
            
            DB::commit();
            
            Log::info('Payment marked as paid', [
                'payment_id' => $payment->id,
                'admin_id' => $admin->id
            ]);
            
            return true;
            
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Payment status update failed', [
                'payment_id' => $payment->id,
                'admin_id' => $admin->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
    
    /**
     * Process refund
     */
    public function processRefund(Payment $payment, float $amount, User $admin): bool
    {
        try {
            DB::beginTransaction();
            
            // Verify admin permissions
            if (!$admin->hasRole('Admin')) {
                throw new Exception('Unauthorized to process refund.');
            }
            
            // Verify payment is completed
            if ($payment->status !== 'completed') {
                throw new Exception('Payment must be completed to process refund.');
            }
            
            // Create refund record
            $refund = Payment::create([
                'booking_id' => $payment->booking_id,
                'user_id' => $payment->user_id,
                'amount' => -$amount, // Negative amount for refund
                'payment_method' => $payment->payment_method,
                'status' => 'completed',
                'transaction_id' => 'refund_' . $payment->transaction_id,
                'notes' => 'Refund for payment #' . $payment->id,
                'paid_at' => now(),
            ]);
            
            DB::commit();
            
            Log::info('Refund processed successfully', [
                'payment_id' => $payment->id,
                'refund_id' => $refund->id,
                'admin_id' => $admin->id,
                'amount' => $amount
            ]);
            
            return true;
            
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Refund processing failed', [
                'payment_id' => $payment->id,
                'admin_id' => $admin->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
    
    /**
     * Get payment statistics
     */
    public function getPaymentStats(): array
    {
        return [
            'total_amount' => Payment::where('status', 'completed')->sum('amount'),
            'pending_amount' => Payment::where('status', 'pending')->sum('amount'),
            'refunded_amount' => Payment::where('status', 'completed')->where('amount', '<', 0)->sum('amount'),
            'total_payments' => Payment::where('status', 'completed')->count(),
            'pending_payments' => Payment::where('status', 'pending')->count(),
            'failed_payments' => Payment::where('status', 'failed')->count(),
        ];
    }
    
    /**
     * Send payment reminders
     */
    public function sendPaymentReminders(): int
    {
        $pendingPayments = Payment::where('status', 'pending')
            ->where('created_at', '<=', now()->subDays(3))
            ->with('user')
            ->get();
        
        $sentCount = 0;
        
        foreach ($pendingPayments as $payment) {
            try {
                $payment->user->notify(new PaymentReminderNotification($payment));
                $sentCount++;
                
                Log::info('Payment reminder sent', [
                    'payment_id' => $payment->id,
                    'user_id' => $payment->user_id
                ]);
            } catch (Exception $e) {
                Log::error('Failed to send payment reminder', [
                    'payment_id' => $payment->id,
                    'user_id' => $payment->user_id,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        return $sentCount;
    }
} 