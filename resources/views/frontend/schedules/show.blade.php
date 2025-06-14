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
                <img src="{{ $schedule->image_url ?? asset('images/default-class.jpg') }}" class="card-img-top" alt="{{ $schedule->title }}">
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
                                   {{ $schedule->start_date->format('M d, Y') }} - {{ $schedule->end_date->format('M d, Y') }}
                                </p>
                                <p class="mb-1">
                                    <i class="fas fa-clock text-primary me-2"></i>
                                    {{ \Carbon\Carbon::parse($schedule->start_time)->format('g:i A') }} - 
                                    {{ \Carbon\Carbon::parse($schedule->end_time)->format('g:i A') }}
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <h5 class="text-muted mb-2">Capacity</h5>
                                <p class="mb-1">
                                    <i class="fas fa-users text-primary me-2"></i>
                                    {{ $schedule->capacity - $schedule->bookings_count }} spots available
                                </p>
                                <p class="mb-1">
                                    <i class="fas fa-dollar-sign text-primary me-2"></i>
                                    ${{ number_format($schedule->price, 2) }} per session
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

          
        </div>

        <div class="col-md-4">
        @if($schedule->trainer)
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h3 class="h4 mb-4">Trainer</h3>
                    <div class="d-flex align-items-center">
                        <img src="{{ $schedule->trainer->profile_photo_url }}" class="rounded-circle me-3" width="64" height="64" alt="{{ $schedule->trainer->name }}">
                        <div>
                            <h5 class="mb-1">{{ $schedule->trainer->name }}</h5>
                            <p class="text-muted mb-0">{{ $schedule->trainer->bio ?? 'Certified trainer specializing in children\'s fitness.' }}</p>
                        </div>
                    </div>
                </div>
            </div>
            @endif
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