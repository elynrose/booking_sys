@extends('layouts.frontend')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">Booking Details</h4>
                        <span class="badge bg-light text-dark">#{{ $booking->id }}</span>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Child Information -->
                    @if($booking->child)
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card border-0 bg-light">
                                <div class="card-body">
                                    <h6 class="card-title text-muted mb-3">
                                        <i class="fas fa-child me-2"></i>Student Information
                                    </h6>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p class="mb-1"><strong>Name:</strong> {{ $booking->child->name }}</p>
                                            @if($booking->child->age)
                                                <p class="mb-1"><strong>Age:</strong> {{ $booking->child->age }} years old</p>
                                            @endif
                                            @if($booking->child->date_of_birth)
                                                <p class="mb-1"><strong>Date of Birth:</strong> {{ $booking->child->date_of_birth->format('M d, Y') }}</p>
                                            @endif
                                        </div>
                                        <div class="col-md-6">
                                            @if($booking->child->medical_conditions)
                                                <p class="mb-1"><strong>Medical Conditions:</strong> {{ $booking->child->medical_conditions }}</p>
                                            @endif
                                            @if($booking->child->allergies)
                                                <p class="mb-1"><strong>Allergies:</strong> {{ $booking->child->allergies }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card border-0 bg-light h-100">
                                <div class="card-body">
                                    <h6 class="card-title text-muted mb-3">
                                        <i class="fas fa-calendar me-2"></i>Class Information
                                    </h6>
                                    <div class="mb-3">
                                        <p class="mb-0 text-muted">Class Name</p>
                                        <h6 class="mb-0">{{ $booking->schedule->title }}</h6>
                                    </div>
                                    <div class="mb-3">
                                        <p class="mb-0 text-muted">Trainer</p>
                                        <h6 class="mb-0">
                                            @if($booking->schedule->trainer)
                                                {{ $booking->schedule->trainer->user->name }}
                                            @else
                                                Not assigned
                                            @endif
                                        </h6>
                                    </div>
                                    <div class="mb-3">
                                        <p class="mb-0 text-muted">Schedule</p>
                                        <h6 class="mb-0">
                                            @if($booking->schedule->start_date && $booking->schedule->end_date)
                                                {{ $booking->schedule->start_date->format('M d, Y') }} - {{ $booking->schedule->end_date->format('M d, Y') }}
                                            @else
                                                Dates not set
                                            @endif
                                        </h6>
                                    </div>
                                    <div class="mb-3">
                                        <p class="mb-0 text-muted">Time</p>
                                        <h6 class="mb-0">
                                            @if($booking->schedule->start_time && $booking->schedule->end_time)
                                                {{ $booking->schedule->start_time->format('h:i A') }} - {{ $booking->schedule->end_time->format('h:i A') }}
                                            @else
                                                Times not set
                                            @endif
                                        </h6>
                                    </div>
                                    @if($booking->schedule->day)
                                    <div class="mb-3">
                                        <p class="mb-0 text-muted">Day of Week</p>
                                        <h6 class="mb-0">{{ $booking->schedule->day }}</h6>
                                    </div>
                                    @endif
                                    <div class="mb-3">
                                        <p class="mb-0 text-muted">Class Type</p>
                                        <h6 class="mb-0">{{ ucfirst($booking->schedule->type ?? 'Not specified') }}</h6>
                                    </div>
                                    <div class="mb-3">
                                        <p class="mb-0 text-muted">Location</p>
                                        <h6 class="mb-0">{{ $booking->schedule->location ?? 'Not specified' }}</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border-0 bg-light h-100">
                                <div class="card-body">
                                    <h6 class="card-title text-muted mb-3">
                                        <i class="fas fa-info-circle me-2"></i>Booking Status
                                    </h6>
                                    <div class="d-flex justify-content-between mb-3">
                                        <span class="text-muted">Status:</span>
                                        <span class="badge bg-{{ $booking->status === 'confirmed' ? 'success' : ($booking->status === 'pending' ? 'warning' : 'danger') }} text-white">
                                            {{ ucfirst($booking->status) }}
                                        </span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-3">
                                        <span class="text-muted">Payment Status:</span>
                                        <span class="badge bg-{{ $booking->is_paid ? 'success' : 'warning' }} text-white">
                                            {{ $booking->is_paid ? 'Paid' : 'Pending' }}
                                        </span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-3">
                                        <span class="text-muted">Sessions Remaining:</span>
                                        <span class="fw-bold">{{ $booking->sessions_remaining }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-3">
                                        <span class="text-muted">Total Check-ins:</span>
                                        <span class="fw-bold">{{ $booking->checkins->count() }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-3">
                                        <span class="text-muted">Booking Date:</span>
                                        <span class="fw-bold">{{ $booking->created_at->format('M d, Y') }}</span>
                                    </div>
                                    @if($booking->total_cost)
                                    <div class="d-flex justify-content-between mb-3">
                                        <span class="text-muted">Total Cost:</span>
                                        <span class="fw-bold">${{ number_format($booking->total_cost, 2) }}</span>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    @if(!$booking->is_paid)
                        <div class="card border-0 bg-light mb-4">
                            <div class="card-body">
                                <h6 class="card-title text-muted mb-3">
                                    <i class="fas fa-credit-card me-2"></i>Payment Information
                                </h6>
                                <div class="alert alert-info">
                                    <h6 class="alert-heading mb-2">Payment Required</h6>
                                    <p class="mb-2">Please complete your payment to confirm your booking:</p>
                                    <ul class="mb-0">
                                        <li><strong>Amount Due:</strong> 
                                            @if($booking->schedule->hasDiscount())
                                                <span class="text-decoration-line-through text-muted">${{ number_format($booking->schedule->price, 2) }}</span>
                                                <span class="text-danger font-weight-bold">${{ number_format($booking->schedule->discounted_price, 2) }}</span>
                                                <span class="badge badge-danger ml-1">{{ $booking->schedule->discount_percentage }}% OFF</span>
                                            @else
                                                ${{ number_format($booking->schedule->price, 2) }}
                                            @endif
                                        </li>
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
                                <h6 class="card-title text-muted mb-3">
                                    <i class="fas fa-credit-card me-2"></i>Payment Information
                                </h6>
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
                                            <p class="mb-0"><strong>Payment Date:</strong> {{ $booking->payment_date ? $booking->payment_date->format('M d, Y') : 'N/A' }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Check-in History -->
                    @if($booking->checkins->count() > 0)
                    <div class="card border-0 bg-light mb-4">
                        <div class="card-body">
                            <h6 class="card-title text-muted mb-3">
                                <i class="fas fa-history me-2"></i>Check-in History
                            </h6>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Check-in Time</th>
                                            <th>Check-out Time</th>
                                            <th>Duration</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($booking->checkins as $checkin)
                                        <tr>
                                            <td>{{ $checkin->created_at->format('M d, Y') }}</td>
                                            <td>{{ $checkin->created_at->format('h:i A') }}</td>
                                            <td>
                                                @if($checkin->checkout_time)
                                                    {{ $checkin->checkout_time->format('h:i A') }}
                                                @else
                                                    <span class="text-muted">Not checked out</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($checkin->checkout_time)
                                                    {{ $checkin->created_at->diffInMinutes($checkin->checkout_time) }} minutes
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($checkin->checkout_time)
                                                    <span class="badge bg-success">Completed</span>
                                                @else
                                                    <span class="badge bg-primary">Checked In</span>
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
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
.table th {
    background-color: #f8f9fa;
    border-top: none;
    font-weight: 600;
}
.table td,
.table tr,
.table th {
    color: #000 !important;
}
</style>
@endsection 