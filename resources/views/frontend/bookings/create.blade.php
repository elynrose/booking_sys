@extends('layouts.frontend')

@section('content')
<div class="container-fluid px-0">
    @if($schedule->photo)
        <div class="position-relative">
            <img src="{{ $schedule->photo_url }}" alt="{{ $schedule->title }}" class="w-100" style="height: 400px; object-fit: cover;">
            <div class="position-absolute top-0 start-0 w-100 h-100" style="background: linear-gradient(to bottom, rgba(0,0,0,0.3), rgba(0,0,0,0.7));"></div>
            <div class="position-absolute bottom-0 start-0 p-4 text-white">
                <h1 class="display-5 fw-bold mb-2">{{ $schedule->title }}</h1>
                @if($schedule->trainer && $schedule->trainer->user)
                    <p class="lead mb-0">
                        <i class="fas fa-user-tie me-2"></i>
                        {{ $schedule->trainer->user->name }}
                    </p>
                @endif
            </div>
        </div>
    @endif

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-12">
                @if(!$schedule->photo)
                    <h4 class="mb-4">{{ $schedule->title }}</h4>
                @endif

                <div class="card shadow" style="margin-top: 70px;">
                    <div class="card-body">
                        <form action="{{ route('bookings.store', $schedule) }}" method="POST">
                            @csrf
                            <input type="hidden" name="schedule_id" value="{{ $schedule->id }}">
                            <input type="hidden" name="price_per_session" value="{{ $schedule->current_price }}">

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="card h-100 border-0 bg-light">
                                        <div class="card-body">
                                            <h6 class="card-title text-muted mb-2">Schedule</h6>
                                            <p>
                                                @if($schedule->start_date && $schedule->end_date)
                                                    <i class="fas fa-calendar-alt text-primary me-2"></i> <strong>Dates:</strong> {{ $schedule->start_date->format('M d, Y') }} to {{ $schedule->end_date->format('M d, Y') }}
                                                @else
                                                    <span class="text-muted">No dates set</span>
                                                @endif
                                            </p>
                                            <p><i class="fas fa-dollar-sign text-primary me-2"></i> <strong>Price per session:</strong> 
                                                @if($schedule->hasDiscount())
                                                    <span class="text-decoration-line-through text-muted">${{ number_format($schedule->price, 2) }}</span>
                                                    <span class="text-danger font-weight-bold">${{ number_format($schedule->discounted_price, 2) }}</span>
                                                    <span class="badge badge-danger ml-1">{{ $schedule->discount_percentage }}% OFF</span>
                                                @else
                                                    ${{ number_format($schedule->price, 2) }}
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <div class="card h-100 border-0 bg-light">
                                        <div class="card-body">
                                            <h6 class="card-title text-muted mb-2">Location</h6>
                                            <p class="mb-1"><i class="fas fa-map-marker-alt text-primary me-2"></i> {{ $schedule->location ?? 'No location specified' }}</p>
                                            <p class="mb-0"><i class="fas fa-info-circle text-primary me-2"></i> {{ $schedule->description ?? 'No description specified' }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card border-0 bg-light mb-3">
                                        <div class="card-body">
                                            <h6 class="card-title text-muted mb-3">Select Child</h6>
                                            <div class="mb-3">
                                                <select class="form-select form-control" name="child_id" required>
                                                    <option value="">Select a child</option>
                                                    @foreach($children as $child)
                                                        <option value="{{ $child->id }}">{{ $child->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card border-0 bg-light mb-3">
                                        <div class="card-body">
                                            <h6 class="card-title text-muted mb-3">Number of Sessions</h6>
                                            <div class="mb-3">
                                                <select class="form-select form-control" id="sessions" name="sessions">
                                                    @for($i = 1; $i <= $totalDays; $i++)
                                                        <option value="{{ $i }}">{{ $i }} session{{ $i > 1 ? 's' : '' }}</option>
                                                    @endfor
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="card border-0 bg-light mb-3">
                                    <div class="card-body">
                                        <h6 class="card-title text-muted mb-3">Payment Method</h6>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-check mb-3">
                                                    <input class="form-check-input" type="radio" name="payment_method" id="zelle" value="zelle" checked>
                                                    <label class="form-check-label" for="zelle">
                                                        <i class="fas fa-university me-2"></i>Zelle
                                                    </label>
                                                </div>
                                                <div id="zelle-info" class="ps-4">
                                                    <div class="alert alert-info">
                                                        <h6 class="alert-heading mb-2">Zelle Payment Instructions</h6>
                                                        <p class="mb-2">Please send your payment to:</p>
                                                        <ul class="mb-0">
                                                            <li><strong>Email:</strong> your-zelle-email@example.com</li>
                                                            <li><strong>Name:</strong> Your Business Name</li>
                                                            <li><strong>Amount:</strong> $<span id="zelle-amount">{{ number_format($schedule->current_price, 2) }}</span></li>
                                                        </ul>
                                                        <hr>
                                                        <p class="mb-0"><small>Please include your name and booking reference <strong>username</strong> in the memo field.</small></p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-check mb-3">
                                                    <input class="form-check-input" type="radio" name="payment_method" id="stripe" value="stripe">
                                                    <label class="form-check-label" for="stripe">
                                                        <i class="fab fa-stripe me-2"></i><br>Credit/Debit Card (Stripe)
                                                    </label>
                                                </div>
                                                <div id="stripe-form" class="ps-4" style="display: none;">
                                                    <div class="alert alert-info">
                                                        <p class="mb-0">You will be redirected to Stripe to complete your payment after booking.</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="card border-0 bg-light mb-3">
                                    <div class="card-body">
                                        <h6 class="card-title text-muted mb-3">Total Cost</h6>
                                        <h3 class="mb-0" id="totalCost">$ {{ number_format($schedule->current_price, 2) }}</h3>
                                    </div>
                                </div>

                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary btn-lg">Book Now</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const sessionsSelect = document.getElementById('sessions');
        const pricePerSession = {{ $schedule->current_price }};
        
        function updateTotal(value) {
            const total = pricePerSession * value;
            document.getElementById('totalCost').textContent = '$' + total.toFixed(2);
            document.getElementById('zelle-amount').textContent = total.toFixed(2);
        }

        // Update on change
        sessionsSelect.addEventListener('change', function() {
            updateTotal(this.value);
        });

        // Set initial value
        updateTotal(sessionsSelect.value);
    });
</script>
@endsection 