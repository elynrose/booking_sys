@extends('frontend.layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Payment Details</h4>
                </div>
                <div class="card-body">
                    <!-- Class Information -->
                    <div class="mb-4">
                        <h5 class="text-primary mb-3">
                            <i class="fas fa-info-circle me-2"></i>Class Information
                        </h5>
                        <div class="row">
                            <div class="col-md-6">
                                <p class="mb-2">
                                    <strong>Class:</strong> {{ $booking->schedule->title }}
                                </p>
                                <p class="mb-2">
                                    <strong>Child:</strong> {{ $booking->child->name }} ({{ $booking->child->age }} years)
                                </p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-2">
                                    <strong>Schedule:</strong><br>
                                    {{ $booking->schedule->start_date }} to {{ $booking->schedule->end_date }}<br>
                                    {{ $booking->schedule->start_time }} - {{ $booking->schedule->end_time }}
                                </p>
                                <p class="mb-2">
                                    <strong>Class Capacity:</strong> {{ $booking->schedule->current_participants }}/{{ $booking->schedule->max_participants }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Summary -->
                    <div class="mb-4">
                        <h5 class="text-primary mb-3">
                            <i class="fas fa-receipt me-2"></i>Payment Summary
                        </h5>
                        <div class="alert alert-info">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-0">Amount Due</h6>
                                    <h4 class="mb-0">${{ number_format($booking->schedule->price, 2) }}</h4>
                                </div>
                                <span class="badge bg-warning">Pending Payment</span>
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
                                <div id="stripe-form" class="mt-3 ps-4" style="display: none;">
                                    <div id="payment-element"></div>
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
                                <form id="zelle-form" action="{{ route('payments.process') }}" method="POST" style="display: inline;">
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

@push('scripts')
<script src="https://js.stripe.com/v3/"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const stripe = Stripe('{{ $stripeKey }}');
    const elements = stripe.elements({
        clientSecret: '{{ $clientSecret }}'
    });

    const paymentElement = elements.create('payment');
    paymentElement.mount('#payment-element');

    // Get all the elements we need to manipulate
    const paymentMethodRadios = document.querySelectorAll('.payment-method-radio');
    const stripeForm = document.getElementById('stripe-form');
    const zelleSubmit = document.getElementById('zelle-submit');
    const stripeSubmit = document.getElementById('stripe-submit');

    // Function to update UI based on selected payment method
    function updatePaymentUI() {
        const selectedMethod = document.querySelector('.payment-method-radio:checked').value;
        console.log('Selected payment method:', selectedMethod); // Debug log

        if (selectedMethod === 'stripe') {
            stripeForm.style.display = 'block';
            zelleSubmit.style.display = 'none';
            stripeSubmit.style.display = 'inline-block';
        } else {
            stripeForm.style.display = 'none';
            zelleSubmit.style.display = 'inline-block';
            stripeSubmit.style.display = 'none';
        }
    }

    // Add change event listener to all radio buttons
    paymentMethodRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            console.log('Radio changed to:', this.value); // Debug log
            updatePaymentUI();
        });
    });

    // Set initial state
    updatePaymentUI();

    // Handle Stripe payment
    stripeSubmit.addEventListener('click', async function() {
        this.disabled = true;
        this.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing...';

        try {
            const { error } = await stripe.confirmPayment({
                elements,
                confirmParams: {
                    return_url: '{{ route("payments.confirm") }}?booking_id={{ $booking->id }}&payment_method=stripe'
                }
            });

            if (error) {
                const messageDiv = document.createElement('div');
                messageDiv.textContent = error.message;
                stripeForm.appendChild(messageDiv);
                this.disabled = false;
                this.innerHTML = '<i class="fab fa-stripe me-2"></i>Pay with Card';
            }
        } catch (error) {
            console.error('Error:', error);
            this.disabled = false;
            this.innerHTML = '<i class="fab fa-stripe me-2"></i>Pay with Card';
        }
    });
});
</script>
@endpush

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