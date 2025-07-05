@extends('layouts.frontend')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">
                        <i class="fas fa-credit-card me-2"></i>Payment for {{ $booking->schedule->title }}
                    </h4>
                </div>
                <div class="card-body">
                    <!-- Booking Summary -->
                    <div class="booking-summary mb-4">
                        <div class="card bg-light">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-0">Amount Due</h6>
                                        @if($booking->schedule->hasDiscount())
                                            <h4 class="mb-0">
                                                <span class="text-decoration-line-through text-muted">${{ number_format($booking->schedule->price, 2) }}</span>
                                                <span class="text-danger font-weight-bold">${{ number_format($booking->schedule->discounted_price, 2) }}</span>
                                                <span class="badge badge-danger ml-1">{{ $booking->schedule->discount_percentage }}% OFF</span>
                                            </h4>
                                        @else
                                            <h4 class="mb-0">${{ number_format($booking->schedule->price, 2) }}</h4>
                                        @endif
                                    </div>
                                    <span class="badge bg-warning">Pending Payment</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Method -->
                    <div class="mb-4">
                        <h5 class="text-primary mb-3">
                            <i class="fas fa-credit-card me-2"></i>Payment Method
                        </h5>
                        
                        <div class="payment-methods">
                            <!-- Zelle Option -->
                            <div class="payment-option mb-3">
                                <div class="form-check">
                                    <input class="form-check-input payment-method-radio" type="radio" name="payment_method" id="zelle" value="zelle" checked>
                                    <label class="form-check-label" for="zelle">
                                        <i class="fas fa-university me-2"></i>Zelle
                                    </label>
                                </div>
                                <div class="zelle-details mt-2 ps-4">
                                    <p class="mb-1">Send payment to: <strong>your-zelle-email@example.com</strong></p>
                                    <p class="mb-1">Please include booking ID: <strong>{{ $booking->id }}</strong></p>
                                    <p class="text-muted small">Your booking will be confirmed once payment is received.</p>
                                </div>
                            </div>

                            <!-- Stripe Option -->
                            <div class="payment-option">
                                <div class="form-check">
                                    <input class="form-check-input payment-method-radio" type="radio" name="payment_method" id="stripe" value="stripe">
                                    <label class="form-check-label" for="stripe">
                                        <i class="fab fa-stripe me-2"></i>Credit Card (Stripe)
                                    </label>
                                </div>
                                <div class="stripe-details mt-2 ps-4">
                                    <p class="text-muted small">You will be redirected to Stripe's secure payment page to complete your payment.</p>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex justify-content-between mt-4">
                            <a href="{{ route('bookings.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Back to Bookings
                            </a>
                            <div>
                                <!-- Zelle Submit Button -->
                                <form id="zelle-form" action="{{ route('frontend.payments.process') }}" method="POST" style="display: inline;">
                                    @csrf
                                    <input type="hidden" name="booking_id" value="{{ $booking->id }}">
                                    <input type="hidden" name="payment_method" value="zelle">
                                    <button type="submit" class="btn btn-success" id="zelle-submit">
                                        <i class="fas fa-university me-2"></i>Submit Zelle Payment
                                    </button>
                                </form>
                                <!-- Stripe Submit Button -->
                                <button type="button" class="btn btn-primary" id="stripe-submit" style="display: none;">
                                    <i class="fab fa-stripe me-2"></i>Pay with Card
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@section('scripts')
<script src="https://js.stripe.com/v3/"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('Payment page JavaScript loading...');
    
    // Get all the elements we need to manipulate
    const paymentMethodRadios = document.querySelectorAll('.payment-method-radio');
    const zelleSubmit = document.getElementById('zelle-submit');
    const stripeSubmit = document.getElementById('stripe-submit');
    
    console.log('Found elements:', {
        paymentMethodRadios: paymentMethodRadios.length,
        zelleSubmit: zelleSubmit ? 'Yes' : 'No',
        stripeSubmit: stripeSubmit ? 'Yes' : 'No'
    });

    // Function to update UI based on selected payment method
    function updatePaymentUI() {
        const selectedMethod = document.querySelector('.payment-method-radio:checked').value;
        console.log('Selected payment method:', selectedMethod);
        
        if (selectedMethod === 'stripe') {
            console.log('Showing Stripe button');
            zelleSubmit.style.display = 'none';
            stripeSubmit.style.display = 'inline-block';
        } else {
            console.log('Showing Zelle button');
            zelleSubmit.style.display = 'inline-block';
            stripeSubmit.style.display = 'none';
        }
    }

    // Add change event listener to all radio buttons
    paymentMethodRadios.forEach(radio => {
        radio.addEventListener('change', updatePaymentUI);
        console.log('Added change listener to radio button:', radio.value);
    });

    // Set initial state
    console.log('Setting initial state...');
    updatePaymentUI();

    // Handle Stripe payment - redirect to Stripe Checkout
    stripeSubmit.addEventListener('click', function() {
        console.log('Stripe button clicked! Redirecting to Stripe Checkout...');
        this.disabled = true;
        this.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Redirecting...';
        
        // Redirect to Stripe Checkout
        const stripe = Stripe('{{ $stripeKey }}');
        stripe.redirectToCheckout({
            sessionId: '{{ $checkoutSessionId }}'
        }).then(function (result) {
            if (result.error) {
                console.error('Stripe error:', result.error.message);
                alert('Error: ' + result.error.message);
                stripeSubmit.disabled = false;
                stripeSubmit.innerHTML = '<i class="fab fa-stripe me-2"></i>Pay with Card';
            }
        });
    });
    
    console.log('Payment page JavaScript initialized successfully');
});
</script>
@endsection

<style>
.StripeElement {
    box-sizing: border-box;
    height: 40px;
    padding: 10px 12px;
    border: 1px solid #ccd0d5;
    border-radius: 4px;
    background-color: white;
}

.StripeElement--focus {
    border-color: #80bdff;
    outline: 0;
    box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
}

.StripeElement--invalid {
    border-color: #dc3545;
}

.StripeElement--webkit-autofill {
    background-color: #fefde5 !important;
}

.card {
    transition: transform 0.2s;
}

.card:hover {
    transform: translateY(-2px);
}

.bg-light {
    background-color: #f8f9fa !important;
}

.badge {
    font-size: 0.9em;
    padding: 0.5em 1em;
}

.alert {
    border-radius: 8px;
}

.form-check-input:checked {
    background-color: #0d6efd;
    border-color: #0d6efd;
}
</style>
@endsection 