@extends('layouts.frontend')

@section('content')
<div class="container py-5">
    <div class="row">
        <div class="col-md-8">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('frontend.schedules.index') }}">Classes</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $schedule->title }}</li>
                </ol>
            </nav>

            <div class="card shadow-sm mb-4">
                @if($schedule->photo)
                    <img src="{{ Storage::url($schedule->photo) }}" class="card-img-top" alt="{{ $schedule->title }}" style="height: 300px; object-fit: cover;">
                @endif
                <div class="card-body">
                    <h1 class="card-title h2 mb-3">{{ $schedule->title }}</h1>
                    <p class="card-text">{{ $schedule->description }}</p>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h3 class="h4 mb-4">Class Details</h3>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <h5 class="text-muted mb-2">Schedule</h5>
                                <p class="mb-1">
                                    <i class="fas fa-calendar text-primary me-2"></i>
                                   @if($schedule->start_date && $schedule->end_date)
                                       {{ $schedule->start_date->format('M d, Y') }} - {{ $schedule->end_date->format('M d, Y') }}
                                   @else
                                       Dates not set
                                   @endif
                                </p>
                                <p class="mb-1">
                                    <i class="fas fa-clock text-primary me-2"></i>
                                    @if($schedule->start_time && $schedule->end_time)
                                        {{ \Carbon\Carbon::parse($schedule->start_time)->format('g:i A') }} - 
                                        {{ \Carbon\Carbon::parse($schedule->end_time)->format('g:i A') }}
                                    @else
                                        Times not set
                                    @endif
                                </p>
                                @if($schedule->location)
                                <p class="mb-1">
                                    <i class="fas fa-map-marker-alt text-primary me-2"></i>
                                    {{ $schedule->location }}
                                </p>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <h5 class="text-muted mb-2">Capacity</h5>
                                <p class="mb-1">
                                    <i class="fas fa-users text-primary me-2"></i>
                                    {{ $schedule->max_participants - $schedule->bookings()->where('status', '!=', 'cancelled')->count() }} spots available
                                </p>
                                <p class="mb-1">
                                    <i class="fas fa-dollar-sign text-primary me-2"></i>
                                    @if($schedule->hasDiscount())
                                        <span class="text-decoration-line-through text-muted">${{ number_format($schedule->price, 2) }}</span>
                                        <span class="text-danger font-weight-bold">${{ number_format($schedule->discounted_price, 2) }}</span>
                                        <span class="badge badge-danger ml-1">{{ $schedule->discount_percentage }}% OFF</span>
                                    @else
                                        ${{ number_format($schedule->price, 2) }}
                                    @endif
                                    per session
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm sticky-top" style="top: 2rem;">
                <div class="card-body">
                    <div class="text-center mb-4">
                        <h4 class="card-title">Book This Class</h4>
                        @if($schedule->hasDiscount())
                            <div class="h2 text-decoration-line-through text-muted mb-1">${{ number_format($schedule->price, 2) }}</div>
                            <div class="h2 text-danger font-weight-bold mb-2">${{ number_format($schedule->discounted_price, 2) }}</div>
                            <div class="text-danger mb-2">{{ $schedule->discount_percentage }}% OFF</div>
                        @else
                            <div class="h2 text-primary mb-2">${{ number_format($schedule->price, 2) }}</div>
                        @endif
                        <div class="text-muted">per session</div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <div class="h5 mb-1">{{ $schedule->max_participants - $schedule->bookings()->where('status', '!=', 'cancelled')->count() }}</div>
                            <div class="text-muted small">spots remaining</div>
                        </div>
                        <div class="text-end">
                            <div class="h5 mb-1">{{ $schedule->bookings()->where('status', '!=', 'cancelled')->count() }}</div>
                            <div class="text-muted small">booked</div>
                        </div>
                    </div>

                    @if($schedule->isAvailable())
                        <a href="{{ route('bookings.create', $schedule) }}" class="btn btn-primary btn-lg w-100">
                            <i class="fas fa-calendar-plus me-2"></i> Book Now
                        </a>
                    @else
                        <button class="btn btn-secondary btn-lg w-100 mb-4" disabled>
                            <i class="fas fa-calendar-times me-2"></i> {{ $schedule->isAvailable() ? 'Class Full' : 'Not Available' }}
                        </button>
                    @endif

                    <div class="border-top pt-4">
                        <h5 class="text-muted mb-3">Trainer</h5>
                        <div class="d-flex align-items-center">
                                                    @if($schedule->trainer->user->photo_url)
                            <img src="{{ $schedule->trainer->user->photo_url }}" 
                                 alt="{{ $schedule->trainer->user->name }}" 
                                 class="rounded-circle me-3"
                                 style="width: 50px; height: 50px; object-fit: cover;">
                        @else
                                <div class="rounded-circle bg-secondary me-3 d-flex align-items-center justify-content-center"
                                     style="width: 50px; height: 50px;">
                                    <i class="fas fa-user text-white"></i>
                                </div>
                            @endif
                            <div>
                                <h6 class="mb-1">{{ $schedule->trainer->user->name }}</h6>
                                <small class="text-muted">Professional Trainer</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const sessionsSelect = document.getElementById('sessions');
    const totalPriceElement = document.getElementById('total-price');
    const basePrice = {{ $schedule->price }};

    function updateTotalPrice() {
        const sessions = parseInt(sessionsSelect.value);
        let discount = 0;

        if (sessions === 4) discount = 0.05;
        else if (sessions === 8) discount = 0.10;
        else if (sessions === 12) discount = 0.15;

        const total = (basePrice * sessions) * (1 - discount);
        totalPriceElement.textContent = '$' + total.toFixed(2);
    }

    sessionsSelect.addEventListener('change', updateTotalPrice);
});
</script>
@endpush
@endsection 