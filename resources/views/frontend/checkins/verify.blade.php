@extends('layouts.frontend')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-sm-12">
            <div class="card shadow-sm">
               
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif

                    @if(isset($activeCheckin))
                        <div class="text-center mb-4">
                            <h4 class="mb-3">Currently Checked In</h4>
                            <div class="timer-display mb-3">
                                <div class="d-flex justify-content-center align-items-center">
                                    <div class="timer-box">
                                        <span id="hours">00</span>
                                        <span class="timer-label">Hours</span>
                                    </div>
                                    <span class="timer-separator">:</span>
                                    <div class="timer-box">
                                        <span id="minutes">00</span>
                                        <span class="timer-label">Minutes</span>
                                    </div>
                                    <span class="timer-separator">:</span>
                                    <div class="timer-box">
                                        <span id="seconds">00</span>
                                        <span class="timer-label">Seconds</span>
                                    </div>
                                </div>
                            </div>
                            <form action="{{ route('frontend.checkins.checkout') }}" method="POST" class="d-inline">
                                @csrf
                                <input type="hidden" name="booking_id" value="{{ $activeCheckin->booking->id }}">
                                <input type="hidden" name="user_id" value="{{ $user->id }}">
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-sign-out-alt me-2"></i> Check Out
                                </button>
                            </form>
                        </div>
                    @endif

                    @if($unpaidBookings > 0)
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            You have {{ $unpaidBookings }} {{ Str::plural('class', $unpaidBookings) }} that need payment.
                            <a href="{{ route('bookings.index') }}" class="alert-link">View and pay for your classes</a>
                        </div>
                    @endif

                    @if($bookings->isEmpty())
                        <div class="text-center py-5">
                            <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No paid classes found</h5>
                            <p class="text-muted">Book a class to get started.</p>
                            <a href="{{ route('frontend.schedules.index') }}" class="btn btn-primary mt-3">
                                <i class="fas fa-calendar-plus me-2"></i>View Schedule
                            </a>
                        </div>
                    @else
                        <div class="row g-4">
                            @foreach($bookings as $booking)
                                <div class="col-md-12">
                                    <div class="card h-100 border-0 shadow-sm">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center mb-3">
                                               
                                                <div>
                                                    <h5 class="card-title mb-1">{{ $booking->schedule->title }}</h5>
                                                    <p class="text-muted small mb-0">{{ $booking->schedule->description }}</p>
                                                </div>
                                            </div>

                                            <div class="class-details mb-3">
                                                <div class="d-flex align-items-center mb-2">
                                                    <i class="fas fa-clock text-muted me-2 mr-2"></i>
                                                    <span>{{ $booking->schedule->start_time->format('h:i A') }} - {{ $booking->schedule->end_time->format('h:i A') }}</span>
                                                </div>
                                                <div class="d-flex align-items-center mb-2">
                                                    <i class="fas fa-calendar text-muted me-2 mr-2"></i>
                                                    <span>{{ $booking->schedule->start_date->format('M d, Y') }} to {{ $booking->schedule->end_date->format('M d, Y') }}</span>
                                                </div>
                                                <div class="d-flex align-items-center">
                                                    <i class="fas fa-child text-muted me-2 mr-2"></i>
                                                    <span>{{ $booking->child->name }} ({{ $booking->child->age }} years old)</span>
                                                </div>
                                            </div>

                                            <div class="d-grid">
                                                @if(isset($activeCheckin))
                                                    <button class="btn btn-secondary w-100" disabled>
                                                        <i class="fas fa-info-circle me-2"></i> Already Checked In
                                                    </button>
                                                @elseif($booking->sessions_remaining > 0 && $booking->checkins->count() < $booking->sessions_remaining)
                                                    <form action="{{ route('frontend.checkins.checkin') }}" method="POST">
                                                        @csrf
                                                        <input type="hidden" name="booking_id" value="{{ $booking->id }}">
                                                        <input type="hidden" name="user_id" value="{{ $user->id }}">
                                                        <button type="submit" class="btn btn-primary w-100">
                                                            <i class="fas fa-sign-in-alt me-2"></i> Check In
                                                        </button>
                                                    </form>
                                                @elseif(!$booking->checkins->first()->checkout_time)
                                                    <form action="{{ route('frontend.checkins.checkout') }}" method="POST">
                                                        @csrf
                                                        <input type="hidden" name="booking_id" value="{{ $booking->id }}">
                                                        <input type="hidden" name="user_id" value="{{ $user->id }}">
                                                        <button type="submit" class="btn btn-success w-100">
                                                            <i class="fas fa-sign-out-alt me-2"></i> Check Out
                                                        </button>
                                                    </form>
                                                @else
                                                    <button class="btn btn-secondary w-100" disabled>
                                                        <i class="fas fa-check-circle me-2"></i> Sessions Completed
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.class-details {
    background-color: #f8f9fa;
    padding: 1rem;
    border-radius: 0.5rem;
}
.status-badge .badge {
    font-size: 0.9rem;
    padding: 0.5rem 1rem;
}
.card {
    transition: transform 0.2s;
}
.card:hover {
    transform: translateY(-5px);
}
.timer-display {
    font-size: 2rem;
    font-weight: bold;
    color: #2c3e50;
}
.timer-box {
    display: inline-flex;
    flex-direction: column;
    align-items: center;
    background: #f8f9fa;
    padding: 1rem;
    border-radius: 0.5rem;
    min-width: 100px;
}
.timer-label {
    font-size: 0.8rem;
    color: #6c757d;
    margin-top: 0.5rem;
}
.timer-separator {
    font-size: 2rem;
    margin: 0 0.5rem;
    color: #2c3e50;
}
</style>
@endsection

@push('scripts')
@if(isset($activeCheckin))
<script>
// Get the check-in time from the server
const checkinTimeStr = '{{ $activeCheckin->formatted_checkin_time }}';
const userTimezone = '{{ $user->timezone ?? "UTC" }}';

// Parse the check-in time
const checkinTime = new Date(checkinTimeStr);
console.log('Check-in time:', checkinTimeStr);
console.log('User timezone:', userTimezone);
console.log('Local time:', checkinTime.toLocaleString());

function updateTimer() {
    const now = new Date();
    const diff = now.getTime() - checkinTime.getTime();
    
    if (diff < 0) {
        console.error('Negative time difference detected:', diff);
        return;
    }
    
    // Calculate hours, minutes, seconds
    const hours = Math.floor(diff / (1000 * 60 * 60));
    const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
    const seconds = Math.floor((diff % (1000 * 60)) / 1000);
    
    // Update the display
    document.getElementById('hours').textContent = hours.toString().padStart(2, '0');
    document.getElementById('minutes').textContent = minutes.toString().padStart(2, '0');
    document.getElementById('seconds').textContent = seconds.toString().padStart(2, '0');
}

// Update immediately
updateTimer();

// Update every second
const timerInterval = setInterval(updateTimer, 1000);

// Clean up interval when page is unloaded
window.addEventListener('beforeunload', function() {
    clearInterval(timerInterval);
});
</script>
@endif
@endpush 