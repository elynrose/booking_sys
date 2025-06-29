<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Gate;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        abort_if(Gate::denies('payment_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $booking = Booking::with(['schedule', 'child'])->findOrFail($request->booking_id);
        
        // Get Stripe configuration from site settings
        if (!\App\Models\SiteSettings::isStripeEnabled()) {
            return redirect()->route('bookings.index')
                ->with('error', 'Stripe payments are currently disabled or not properly configured.');
        }
        
        $stripeConfig = \App\Models\SiteSettings::getStripeConfig();
        
        // Create Stripe checkout session
        Stripe::setApiKey($stripeConfig['secret_key']);
        
        $checkoutSession = \Stripe\Checkout\Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => $stripeConfig['currency'],
                    'product_data' => [
                        'name' => $booking->schedule->title,
                        'description' => 'Payment for ' . $booking->child->name,
                    ],
                    'unit_amount' => $booking->schedule->price * 100, // Convert to cents
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => route('frontend.payments.confirm') . '?booking_id=' . $booking->id . '&payment_method=stripe&session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('bookings.index') . '?cancelled=true',
            'metadata' => [
                'booking_id' => $booking->id,
                'user_id' => auth()->id(),
            ],
        ]);

        return view('frontend.payments.index', [
            'booking' => $booking,
            'checkoutSessionId' => $checkoutSession->id,
            'stripeKey' => $stripeConfig['publishable_key']
        ]);
    }

    public function process(Request $request)
    {
        abort_if(Gate::denies('payment_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'payment_method' => 'required|in:zelle,stripe'
        ]);

        $booking = Booking::findOrFail($request->booking_id);

        try {
            DB::beginTransaction();

            if ($request->payment_method === 'zelle') {
                // For Zelle, just update the booking status and create a pending payment
                $booking->update([
                    'payment_method' => 'zelle',
                    'payment_status' => 'pending',
                    'is_paid' => false
                ]);

                Payment::create([
                    'user_id' => Auth::id(),
                    'booking_id' => $booking->id,
                    'amount' => $booking->schedule->price,
                    'payment_method' => 'zelle',
                    'status' => 'pending',
                    'description' => 'Payment for ' . $booking->schedule->title . ' - ' . $booking->child->name,
                ]);

                DB::commit();

                return redirect()->route('bookings.index')
                    ->with('success', 'Booking created! Please complete your Zelle payment to confirm your booking.');
            } else {
                // For Stripe, create a payment intent and redirect to Stripe
                if (!\App\Models\SiteSettings::isStripeEnabled()) {
                    return response()->json(['error' => 'Stripe payments are currently disabled or not properly configured.'], 400);
                }
                
                $stripeConfig = \App\Models\SiteSettings::getStripeConfig();
                Stripe::setApiKey($stripeConfig['secret_key']);
                
                $paymentIntent = PaymentIntent::create([
                    'amount' => $booking->schedule->price * 100,
                    'currency' => $stripeConfig['currency'],
                    'metadata' => [
                        'booking_id' => $booking->id
                    ]
                ]);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'clientSecret' => $paymentIntent->client_secret
                ]);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function confirm(Request $request)
    {
        // Add debugging
        \Log::info('Payment confirm method called', [
            'request_params' => $request->all(),
            'user_id' => auth()->id(),
            'url' => $request->fullUrl()
        ]);

        abort_if(Gate::denies('payment_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'payment_method' => 'required|in:zelle,stripe',
            'session_id' => 'required_if:payment_method,stripe'
        ]);

        $booking = Booking::findOrFail($request->booking_id);

        \Log::info('Booking found', ['booking_id' => $booking->id, 'booking_status' => $booking->payment_status]);

        try {
            DB::beginTransaction();

            if ($request->payment_method === 'stripe') {
                // Verify the Stripe session and get payment details
                $stripeConfig = \App\Models\SiteSettings::getStripeConfig();
                Stripe::setApiKey($stripeConfig['secret_key']);
                
                $session = \Stripe\Checkout\Session::retrieve($request->session_id);
                
                \Log::info('Stripe session retrieved', [
                    'session_id' => $session->id,
                    'payment_status' => $session->payment_status,
                    'payment_intent' => $session->payment_intent
                ]);
                
                if ($session->payment_status !== 'paid') {
                    throw new \Exception('Payment was not completed successfully.');
                }
                
                $paymentIntent = \Stripe\PaymentIntent::retrieve($session->payment_intent);
            }

            // Update booking payment status
            $booking->update([
                'payment_method' => $request->payment_method,
                'payment_status' => 'paid',
                'is_paid' => true
            ]);

            \Log::info('Booking updated successfully', [
                'booking_id' => $booking->id,
                'new_payment_status' => $booking->payment_status,
                'new_is_paid' => $booking->is_paid
            ]);

            // Update or create payment record
            $payment = Payment::where('booking_id', $booking->id)
                ->where('status', 'pending')
                ->latest()
                ->first();

            \Log::info('Payment record lookup', [
                'existing_payment' => $payment ? 'Yes' : 'No',
                'payment_id' => $payment ? $payment->id : null
            ]);

            if (!$payment) {
                // Create payment record if it doesn't exist
                try {
                    $payment = Payment::create([
                        'user_id' => $booking->user_id,
                        'booking_id' => $booking->id,
                        'schedule_id' => $booking->schedule_id,
                        'amount' => $booking->schedule->price,
                        'payment_method' => $request->payment_method,
                        'status' => 'paid',
                        'transaction_id' => $request->payment_method === 'stripe' ? $paymentIntent->id : null,
                        'description' => 'Payment for ' . $booking->schedule->title . ' - ' . $booking->child->name,
                        'paid_at' => now()
                    ]);

                    \Log::info('New payment record created', ['payment_id' => $payment->id]);
                } catch (\Exception $e) {
                    \Log::error('Error creating payment record', ['error' => $e->getMessage()]);
                    throw $e;
                }
            } else {
                try {
                    $payment->update([
                        'status' => 'paid',
                        'transaction_id' => $request->payment_method === 'stripe' ? $paymentIntent->id : null,
                        'paid_at' => now()
                    ]);

                    \Log::info('Existing payment record updated', ['payment_id' => $payment->id]);
                } catch (\Exception $e) {
                    \Log::error('Error updating payment record', ['error' => $e->getMessage()]);
                    throw $e;
                }
            }

            // Send payment confirmation email
            try {
                \Log::info('Attempting to send payment confirmation email');
                $booking->user->notify(new \App\Notifications\PaymentConfirmedNotification($payment));
                \Log::info('Payment confirmation email sent successfully');
                
                // Notify admins about the payment
                \Log::info('Attempting to send admin notifications');
                $admins = \App\Models\User::whereHas('roles', function($query) {
                    $query->where('title', 'Admin');
                })->get();
                
                \Log::info('Found admins to notify', ['admin_count' => $admins->count()]);
                
                foreach ($admins as $admin) {
                    $admin->notify(new \App\Notifications\AdminPaymentReceivedNotification($payment));
                }
                \Log::info('Admin notifications sent successfully');
            } catch (\Exception $e) {
                // Log email error but don't fail the payment
                \Log::error('Failed to send payment confirmation email', ['error' => $e->getMessage()]);
            }

            DB::commit();

            \Log::info('Database transaction committed successfully');

            // Redirect to success page with booking info
            \Log::info('Redirecting to success page', [
                'route' => 'frontend.payments.success',
                'session_data' => [
                    'booking_id' => $booking->id,
                    'payment_amount' => $booking->schedule->price,
                    'class_name' => $booking->schedule->title,
                    'child_name' => $booking->child->name
                ]
            ]);

            return redirect()->route('frontend.payments.success')
                ->with('success', 'Payment confirmed successfully!')
                ->with('booking_id', $booking->id)
                ->with('payment_amount', $booking->schedule->price)
                ->with('class_name', $booking->schedule->title)
                ->with('child_name', $booking->child->name);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to confirm payment. Please try again.');
        }
    }

    public function success()
    {
        \Log::info('Payment success method called', [
            'session_data' => [
                'booking_id' => session('booking_id'),
                'payment_amount' => session('payment_amount'),
                'class_name' => session('class_name'),
                'child_name' => session('child_name')
            ]
        ]);

        abort_if(Gate::denies('payment_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        // Get booking and payment data from session
        $bookingId = session('booking_id');
        $paymentAmount = session('payment_amount');
        $className = session('class_name');
        $childName = session('child_name');

        if (!$bookingId) {
            return redirect()->route('bookings.index')
                ->with('error', 'No payment information found.');
        }

        $booking = Booking::with(['schedule', 'child'])->find($bookingId);
        $payment = Payment::where('booking_id', $bookingId)
            ->where('status', 'paid')
            ->latest()
            ->first();

        if (!$booking || !$payment) {
            return redirect()->route('bookings.index')
                ->with('error', 'Payment information not found.');
        }

        return view('frontend.payments.success', compact('booking', 'payment'));
    }
} 