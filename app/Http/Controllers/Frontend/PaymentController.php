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

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        abort_if(Gate::denies('payment_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $booking = Booking::with(['schedule', 'child'])->findOrFail($request->booking_id);
        
        // Create Stripe payment intent
        Stripe::setApiKey(config('services.stripe.secret'));
        
        $paymentIntent = PaymentIntent::create([
            'amount' => $booking->schedule->price * 100, // Convert to cents
            'currency' => 'usd',
            'metadata' => [
                'booking_id' => $booking->id
            ]
        ]);

        return view('frontend.payments.index', [
            'booking' => $booking,
            'clientSecret' => $paymentIntent->client_secret,
            'stripeKey' => config('services.stripe.key')
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
                    'booking_id' => $booking->id,
                    'amount' => $booking->schedule->price,
                    'payment_method' => 'zelle',
                    'status' => 'pending'
                ]);

                DB::commit();

                return redirect()->route('bookings.index')
                    ->with('success', 'Booking created! Please complete your Zelle payment to confirm your booking.');
            } else {
                // For Stripe, create a payment intent and redirect to Stripe
                Stripe::setApiKey(config('services.stripe.secret'));
                
                $paymentIntent = PaymentIntent::create([
                    'amount' => $booking->schedule->price * 100,
                    'currency' => 'usd',
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
        abort_if(Gate::denies('payment_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'payment_method' => 'required|in:zelle,stripe',
            'payment_intent' => 'required_if:payment_method,stripe'
        ]);

        $booking = Booking::findOrFail($request->booking_id);

        try {
            DB::beginTransaction();

            // Update booking payment status
            $booking->update([
                'payment_method' => $request->payment_method,
                'payment_status' => 'completed',
                'is_paid' => true
            ]);

            // Update payment record
            $payment = Payment::where('booking_id', $booking->id)
                ->where('status', 'pending')
                ->latest()
                ->first();

            if ($payment) {
                $payment->update([
                    'status' => 'completed',
                    'transaction_id' => $request->payment_method === 'stripe' ? $request->payment_intent : null
                ]);
            }

            DB::commit();

            return redirect()->route('bookings.index')
                ->with('success', 'Payment confirmed successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to confirm payment. Please try again.');
        }
    }

    public function success()
    {
        abort_if(Gate::denies('payment_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('frontend.payments.success');
    }
} 