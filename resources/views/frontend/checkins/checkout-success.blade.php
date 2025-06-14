@extends('layouts.frontend')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-body text-center">
                    <div class="mb-4">
                        <i class="fas fa-check-circle text-success fa-4x mb-3"></i>
                        <h3 class="text-success">Successfully Checked Out!</h3>
                        <p class="text-muted">Thank you for attending the class.</p>
                    </div>

                    <div class="duration-container mb-4">
                        <h4>Total Duration</h4>
                        <div class="duration display-4">
                            {{ str_pad($hours, 2, '0', STR_PAD_LEFT) }}:{{ str_pad($minutes, 2, '0', STR_PAD_LEFT) }}:{{ str_pad($seconds, 2, '0', STR_PAD_LEFT) }}
                        </div>
                    </div>

                    <div class="class-details mb-4">
                        <p class="mb-1">{{ $booking->schedule->title }}</p>
                        <p class="mb-1"><strong>Child:</strong> {{ $booking->child->name }}</p>
                        <p class="mb-1"><strong>Check-in Time:</strong> {{ $checkin->checkin_time->format('g:i A') }}</p>
                        <p class="mb-1"><strong>Check-out Time:</strong> {{ $checkin->checkout_time->format('g:i A') }}</p>
                    </div>

                    <div class="mt-4">
                        <a href="{{ route('frontend.checkins.index') }}" class="btn btn-primary">
                            <i class="fas fa-arrow-left me-2"></i> Back to Check-in
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.duration-container {
    background-color: #f8f9fa;
    padding: 2rem;
    border-radius: 10px;
    margin: 2rem 0;
}
.duration {
    font-family: 'Courier New', monospace;
    font-weight: bold;
    color: #2c3e50;
}
.class-details {
    background-color: #f8f9fa;
    padding: 1.5rem;
    border-radius: 10px;
    text-align: left;
}
</style>
@endsection 