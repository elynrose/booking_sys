@extends('layouts.frontend')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-sm-12">
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <div class="mb-4">
                        <i class="fas fa-clock fa-4x text-warning mb-3"></i>
                        <h3 class="text-warning">Session Completed</h3>
                        <p class="lead">Your session has ended and you have been automatically checked out.</p>
                    </div>

                    <div class="alert alert-info">
                        <h5>Session Summary</h5>
                        <div class="row text-start">
                            <div class="col-md-6">
                                <p><strong>Class:</strong> {{ $booking->schedule->title }}</p>
                                <p><strong>Child:</strong> {{ $booking->child->name }}</p>
                                <p><strong>Check-in:</strong> {{ $checkin->checkin_time->format('M d, Y h:i A') }}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Check-out:</strong> {{ $checkin->checkout_time->format('M d, Y h:i A') }}</p>
                                <p><strong>Duration:</strong> {{ sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds) }}</p>
                                <p><strong>Sessions Remaining:</strong> {{ $booking->sessions_remaining }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-success">
                        <i class="fas fa-envelope me-2"></i>
                        An email notification has been sent to your registered email address with session details.
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                        <a href="{{ route('frontend.checkins.verify') }}" class="btn btn-primary">&nbsp;
                            <i class="fas fa-qrcode me-2"></i>Check In Again
                        </a>&nbsp;
                        <a href="{{ route('frontend.home') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-home me-2"></i>Go to Dashboard
                        </a>&nbsp;
                        <a href="{{ route('bookings.index') }}" class="btn btn-outline-info">
                            <i class="fas fa-calendar me-2"></i>View Bookings
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.card {
    border: none;
    border-radius: 15px;
}

.alert {
    border-radius: 10px;
}

.btn {
    border-radius: 8px;
    padding: 10px 20px;
}

.fa-clock {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.1); }
    100% { transform: scale(1); }
}
</style>
@endsection 