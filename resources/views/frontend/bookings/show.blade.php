@extends('layouts.frontend')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Booking Details</h4>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card border-0 bg-light h-100">
                                <div class="card-body">
                                    <h6 class="card-title text-muted mb-3">Class Information</h6>
                                    <div class="d-flex align-items-center mb-3">
                                        <div>
                                            <p class="mb-0 text-muted"><i class="fas fa-calendar text-primary me-3 fa-lg"></i> Schedule</p>
                                            <h6 class="mb-0">{{ $booking->schedule->name }}</h6>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center mb-3">
                                        
                                        <div>
                                            <h6 class="mb-0">
                                                <i class="fas fa-clock text-primary me-3 fa-lg"></i> 
                                                @if($booking->schedule->start_time && $booking->schedule->end_time)
                                                    {{ $booking->schedule->start_time->format('h:i A') }} to {{ $booking->schedule->end_time->format('h:i A') }}
                                                @else
                                                    Times not set
                                                @endif
                                            </h6>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-calendar-day text-primary me-3 fa-lg"></i>
                                        <div>
                                            <p class="mb-0 text-muted">Day</p>
                                            <h6 class="mb-0">{{ $booking->schedule->day }}</h6>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border-0 bg-light h-100">
                                <div class="card-body">
                                    <h6 class="card-title text-muted mb-3">Booking Status</h6>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted">Status:</span>
                                        <span class="badge bg-{{ $booking->status === 'confirmed' ? 'success' : 'warning' }} text-white">
                                            {{ ucfirst($booking->status) }}
                                        </span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted">Payment Status:</span>
                                        <span class="badge bg-{{ $booking->is_paid ? 'success' : 'warning' }} text-white">
                                            {{ $booking->is_paid ? 'Paid' : 'Pending' }}
                                        </span>
                                    </div>
                                    @if($booking->is_paid)
                                        <div class="d-flex justify-content-between mb-2">
                                            <span class="text-muted">Payment Method:</span>
                                            <span class="fw-bold">{{ ucfirst($booking->payment_method) }}</span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span class="text-muted">Amount Paid:</span>
                                            <span class="fw-bold">${{ number_format($booking->schedule->price, 2) }}</span>
                                        </div>
                                    @endif
                                    <div class="d-flex justify-content-between">
                                        <span class="text-muted">Sessions:</span>
                                        @if($booking->sessions_remaining > 0 && $booking->checkins->count() < $booking->sessions_remaining)
                                            <span class="fw-bold">{{ $booking->sessions_remaining }} remaining</span>
                                        @else
                                            <span class="fw-bold text-secondary">
                                                <i class="fas fa-check-circle me-1"></i> Sessions Completed
                                            </span>
                                        @endif
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span class="text-muted">Booking ID:</span>
                                        <span class="fw-bold">#{{ $booking->id }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if(!$booking->is_paid)
                        <div class="card border-0 bg-light mb-4">
                            <div class="card-body">
                                <h6 class="card-title text-muted mb-3">Payment Information</h6>
                                <div class="alert alert-info">
                                    <h6 class="alert-heading mb-2">Payment Required</h6>
                                    <p class="mb-2">Please complete your payment to confirm your booking:</p>
                                    <ul class="mb-0">
                                        <li><strong>Amount Due:</strong> ${{ number_format($booking->schedule->price, 2) }}</li>
                                        <li><strong>Payment Method:</strong> {{ ucfirst($booking->payment_method ?? 'Not selected') }}</li>
                                    </ul>
                                    <hr>
                                    <a href="{{ route('frontend.payments.index', ['booking_id' => $booking->id]) }}" class="btn btn-primary">
                                        <i class="fas fa-credit-card me-2"></i> Complete Payment
                                    </a>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="card border-0 bg-light mb-4">
                            <div class="card-body">
                                <h6 class="card-title text-muted mb-3">Payment Information</h6>
                                <div class="alert alert-success">
                                    <h6 class="alert-heading mb-2">
                                        <i class="fas fa-check-circle me-2"></i> Payment Completed
                                    </h6>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p class="mb-2"><strong>Payment Method:</strong> {{ ucfirst($booking->payment_method) }}</p>
                                            <p class="mb-0"><strong>Amount Paid:</strong> ${{ number_format($booking->schedule->price, 2) }}</p>
                                            @if($booking->payment_method === 'zelle')
                                                <p class="mb-0"><strong>Zelle Reference:</strong> {{ $booking->zelle_reference ?? 'N/A' }}</p>
                                            @endif
                                        </div>
                                        <div class="col-md-6">
                                            <p class="mb-2"><strong>Status:</strong> <span class="badge bg-success text-white">Confirmed</span></p>
                                            <p class="mb-0"><strong>Booking ID:</strong> #{{ $booking->id }}</p>
                                            <p class="mb-0"><strong>Payment Date:</strong> {{ $booking->payment_date ? $booking->payment_date->format('M d, Y') : 'N/A' }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="text-end">
                        <a href="{{ route('bookings.index') }}" class="btn btn-light me-2">
                            <i class="fas fa-arrow-left me-2"></i> Back to Bookings
                        </a>
                        @if($booking->status === 'pending' && !$booking->is_paid)
                            <form action="{{ route('bookings.destroy', $booking->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to cancel this booking?')">
                                    <i class="fas fa-times me-2"></i> Cancel Booking
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.card {
    transition: transform 0.2s;
}
.card:hover {
    transform: translateY(-2px);
}
.bg-light {
    background-color: #f8f9fa !important;
}
</style>
@endsection 