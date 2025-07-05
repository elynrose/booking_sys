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
                            
                            <div class="checkin-info mb-3">
                                <p class="text-muted mb-2">
                                    <i class="fas fa-clock me-2"></i>
                                    Check-in Time: {{ $activeCheckin->checkin_time->format('h:i A') }}
                                </p>
                                <p class="text-muted mb-2">
                                    <i class="fas fa-child me-2"></i>
                                    Child: {{ $activeCheckin->booking->child->name }}
                                </p>
                                <p class="text-muted mb-3">
                                    <i class="fas fa-calendar me-2"></i>
                                    Class: {{ $activeCheckin->booking->schedule->title }}
                                </p>
                            </div>
                            
                            @if($activeCheckin->booking->schedule->photo)
                                <div class="position-relative mb-4">
                                    <img src="{{ $activeCheckin->booking->schedule->photo_url }}" alt="{{ $activeCheckin->booking->schedule->title }}" class="w-100 rounded" style="height: 250px; object-fit: cover;">
                                    <div class="position-absolute bottom-0 start-0 p-3 text-white">
                                        <h5 class="mb-1">{{ $activeCheckin->booking->schedule->title }}</h5>
                                        <p class="mb-0">
                                            <i class="fas fa-child me-2"></i>
                                            {{ $activeCheckin->booking->child->name }}
                                        </p>
                                    </div>
                                </div>
                            @endif
                            
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
                                            <div class="d-flex align-items-start mb-3">
                                                @if($booking->schedule->trainer && $booking->schedule->trainer->user)
                                                    @if($booking->schedule->trainer->user->photo)
                                                        <img src="{{ config('filesystems.default') === 's3' ? Storage::disk('s3')->url($booking->schedule->trainer->user->photo) : Storage::url($booking->schedule->trainer->user->photo) }}" 
                                                             alt="{{ $booking->schedule->trainer->user->name }}" 
                                                             class="rounded-circle me-4"
                                                             style="width: 60px; height: 60px; object-fit: cover; flex-shrink: 0;">
                                                    @else
                                                        <div class="rounded-circle bg-secondary me-4 d-flex align-items-center justify-content-center"
                                                             style="width: 60px; height: 60px; flex-shrink: 0;">
                                                            <i class="fas fa-user text-white"></i>
                                                        </div>
                                                    @endif
                                                    <div class="flex-grow-1 ms-2">
                                                        <h5 class="card-title mb-2">{{ $booking->schedule->title }}</h5>
                                                        <p class="text-muted small mb-2">{{ $booking->schedule->description }}</p>
                                                        <div class="d-flex align-items-center">
                                                            <i class="fas fa-user-tie text-primary me-2" style="font-size: 0.9rem;"></i>
                                                            <span class="text-primary fw-bold">{{ $booking->schedule->trainer->user->name }}</span>
                                                        </div>
                                                    </div>
                                                @else
                                                    <div class="flex-grow-1">
                                                        <h5 class="card-title mb-2">{{ $booking->schedule->title }}</h5>
                                                        <p class="text-muted small mb-2">{{ $booking->schedule->description }}</p>
                                                        <div class="d-flex align-items-center">
                                                            <i class="fas fa-user-tie text-muted me-2" style="font-size: 0.9rem;"></i>
                                                            <span class="text-muted">Trainer not assigned</span>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>

                                            <div class="class-details mb-3">
                                                <div class="d-flex align-items-center mb-2">
                                                    <i class="fas fa-clock text-muted me-2 mr-2"></i>
                                                    <span>
                                                        @if($booking->schedule->start_time && $booking->schedule->end_time)
                                                            @php
                                                                $siteTimezone = \App\Models\SiteSettings::getTimezone();
                                                                $startTime = $booking->schedule->start_time->setTimezone($siteTimezone);
                                                                $endTime = $booking->schedule->end_time->setTimezone($siteTimezone);
                                                            @endphp
                                                            {{ $startTime->format('h:i A') }} - {{ $endTime->format('h:i A') }}
                                                        @else
                                                            Times not set
                                                        @endif
                                                    </span>
                                                </div>
                                                <div class="d-flex align-items-center mb-2">
                                                    <i class="fas fa-calendar text-muted me-2 mr-2"></i>
                                                    <span>
                                                        @if($booking->schedule->start_date && $booking->schedule->end_date)
                                                            {{ $booking->schedule->start_date->format('M d, Y') }} to {{ $booking->schedule->end_date->format('M d, Y') }}
                                                        @else
                                                            Dates not set
                                                        @endif
                                                    </span>
                                                </div>
                                                @if($booking->schedule->location)
                                                <div class="d-flex align-items-center mb-2">
                                                    <i class="fas fa-map-marker-alt text-muted me-2 mr-2"></i>
                                                    <span>{{ $booking->schedule->location }}</span>
                                                </div>
                                                @endif
                                                <div class="d-flex align-items-center mb-2">
                                                    <i class="fas fa-child text-muted me-2 mr-2"></i>
                                                    <span>{{ $booking->child->name }} ({{ $booking->child->age }} years old)</span>
                                                </div>
                                                @php
                                                    $currentTime = \Carbon\Carbon::now(\App\Models\SiteSettings::getTimezone());
                                                    $scheduleStartDate = \Carbon\Carbon::parse($booking->schedule->start_date, \App\Models\SiteSettings::getTimezone());
                                                    $scheduleEndDate = \Carbon\Carbon::parse($booking->schedule->end_date, \App\Models\SiteSettings::getTimezone());
                                                    $scheduleStartTime = \Carbon\Carbon::parse($booking->schedule->start_time, \App\Models\SiteSettings::getTimezone());
                                                    $scheduleEndTime = \Carbon\Carbon::parse($booking->schedule->end_time, \App\Models\SiteSettings::getTimezone());
                                                    // Combine date and time for proper comparison
                                                    $classStartDateTime = $scheduleStartDate->copy()->setTime($scheduleStartTime->hour, $scheduleStartTime->minute, $scheduleStartTime->second);
                                                    $classEndDateTime = $scheduleEndDate->copy()->setTime($scheduleEndTime->hour, $scheduleEndTime->minute, $scheduleEndTime->second);
                                                    $classHasStarted = $currentTime->gte($classStartDateTime);
                                                    $classHasEnded = $currentTime->gt($classEndDateTime);
                                                @endphp
                                                <div class="d-flex align-items-center mb-2">
                                                    <i class="fas fa-info-circle text-muted me-2 mr-2"></i>
                                                    <span>
                                                        @if($classHasEnded)
                                                            <span class="badge bg-secondary">Class Ended</span>
                                                        @elseif($classHasStarted)
                                                            <span class="badge bg-success">Class Running</span>
                                                        @else
                                                            <span class="badge bg-warning">Class Not Started</span>
                                                        @endif
                                                    </span>
                                                </div>
                                                @if($booking->schedule->allow_unlimited_bookings)
                                                    <div class="d-flex align-items-center">
                                                        <i class="fas fa-infinity text-success me-2 mr-2"></i>
                                                        <span class="text-success fw-bold">Unlimited Access</span>
                                                    </div>
                                                @else
                                                    <div class="d-flex align-items-center">
                                                        <i class="fas fa-ticket-alt text-muted me-2 mr-2"></i>
                                                        <span>{{ $booking->sessions_remaining }} {{ Str::plural('session', $booking->sessions_remaining) }} remaining</span>
                                                    </div>
                                                @endif
                                            </div>

                                            <div class="d-grid">
                                                @php
                                                    $currentTime = \Carbon\Carbon::now(\App\Models\SiteSettings::getTimezone());
                                                    $scheduleStartDate = \Carbon\Carbon::parse($booking->schedule->start_date, \App\Models\SiteSettings::getTimezone());
                                                    $scheduleEndDate = \Carbon\Carbon::parse($booking->schedule->end_date, \App\Models\SiteSettings::getTimezone());
                                                    $scheduleStartTime = \Carbon\Carbon::parse($booking->schedule->start_time, \App\Models\SiteSettings::getTimezone());
                                                    $scheduleEndTime = \Carbon\Carbon::parse($booking->schedule->end_time, \App\Models\SiteSettings::getTimezone());
                                                    // Combine date and time for proper comparison
                                                    $classStartDateTime = $scheduleStartDate->copy()->setTime($scheduleStartTime->hour, $scheduleStartTime->minute, $scheduleStartTime->second);
                                                    $classEndDateTime = $scheduleEndDate->copy()->setTime($scheduleEndTime->hour, $scheduleEndTime->minute, $scheduleEndTime->second);
                                                    $classHasStarted = $currentTime->gte($classStartDateTime);
                                                    $classHasEnded = $currentTime->gt($classEndDateTime);
                                                @endphp
                                                
                                                @if(isset($activeCheckin))
                                                    <button class="btn btn-secondary w-100" disabled>
                                                        <i class="fas fa-info-circle me-2"></i> Already Checked In
                                                    </button>
                                                @elseif($classHasEnded)
                                                    <button class="btn btn-secondary w-100" disabled>
                                                        <i class="fas fa-clock me-2"></i> Class Has Ended
                                                    </button>
                                                @elseif($classHasStarted && !$classHasEnded && ($booking->schedule->allow_unlimited_bookings || ($booking->sessions_remaining > 0 && $booking->checkins->count() < $booking->sessions_remaining)))
                                                    <form action="{{ route('frontend.checkins.checkin') }}" method="POST">
                                                        @csrf
                                                        <input type="hidden" name="booking_id" value="{{ $booking->id }}">
                                                        <input type="hidden" name="user_id" value="{{ $user->id }}">
                                                        <button type="submit" class="btn btn-primary w-100">
                                                            <i class="fas fa-sign-in-alt me-2"></i> Check In
                                                        </button>
                                                    </form>
                                                @elseif(!$classHasStarted)
                                                    <button class="btn btn-secondary w-100" disabled>
                                                        <i class="fas fa-clock me-2"></i> Class Not Started Yet
                                                    </button>
                                                @elseif($booking->checkins->isNotEmpty() && !$booking->checkins->first()->checkout_time)
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

@section('scripts')
@if(isset($activeCheckin))
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM Content Loaded');
    
    // Get the site timezone from settings
    const siteTimezone = '{{ \App\Models\SiteSettings::getTimezone() }}';
    console.log('Site timezone:', siteTimezone);
    
    // Get the check-in time as ISO string (already in UTC)
    const checkinTimeStr = '{{ $activeCheckin->formatted_checkin_time }}';
    console.log('Check-in time (ISO):', checkinTimeStr);
    
    // Create a date object from the ISO string (this will be in UTC)
    const checkinTime = new Date(checkinTimeStr);
    console.log('Check-in time (UTC):', checkinTime.toISOString());
    console.log('Check-in time (site timezone):', checkinTime.toLocaleString('en-US', { timeZone: siteTimezone }));

    // Get session start and end times from the schedule (these are in UTC)
    const sessionStartTime = new Date('{{ $activeCheckin->booking->schedule->start_time->toISOString() }}');
    const sessionEndTime = new Date('{{ $activeCheckin->booking->schedule->end_time->toISOString() }}');
    
    // Calculate session duration in seconds
    const sessionDurationSeconds = Math.floor((sessionEndTime.getTime() - sessionStartTime.getTime()) / 1000);
    console.log('Session duration (seconds):', sessionDurationSeconds);
    console.log('Session start (UTC):', sessionStartTime.toISOString());
    console.log('Session end (UTC):', sessionEndTime.toISOString());
    
    // Calculate when auto-checkout should happen (check-in time + session duration)
    // All times are in UTC, so this calculation is correct
    const autoCheckoutTime = new Date(checkinTime.getTime() + (sessionDurationSeconds * 1000));
    console.log('Auto checkout time (UTC):', autoCheckoutTime.toISOString());
    console.log('Current time (UTC):', new Date().toISOString());
    console.log('Auto checkout time is in the past:', autoCheckoutTime < new Date());
    console.log('Time until auto checkout (ms):', autoCheckoutTime.getTime() - new Date().getTime());
    console.log('Time until auto checkout (hours):', (autoCheckoutTime.getTime() - new Date().getTime()) / (1000 * 60 * 60));

    let autoCheckoutTriggered = false;

    function updateTimer() {
        const now = new Date();
        
        // Calculate the time difference in milliseconds
        // Both times are in UTC, so the difference calculation is correct
        const diff = now.getTime() - checkinTime.getTime();
        
        console.log('Current time (UTC):', now.toISOString());
        console.log('Check-in time (UTC):', checkinTime.toISOString());
        console.log('Difference (ms):', diff);
        
        // Convert to hours, minutes, seconds
        const hours = Math.floor(diff / (1000 * 60 * 60));
        const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((diff % (1000 * 60)) / 1000);
        
        // Ensure we don't show negative values
        const displayHours = Math.max(0, hours);
        const displayMinutes = Math.max(0, minutes);
        const displaySeconds = Math.max(0, seconds);
        
        document.getElementById('hours').textContent = displayHours.toString().padStart(2, '0');
        document.getElementById('minutes').textContent = displayMinutes.toString().padStart(2, '0');
        document.getElementById('seconds').textContent = displaySeconds.toString().padStart(2, '0');

        // Check if current time has passed the auto-checkout time (check-in + session duration)
        if (now >= autoCheckoutTime && !autoCheckoutTriggered) {
            autoCheckoutTriggered = true;
            triggerAutoCheckout();
        }
        
        // Also check immediately when page loads if auto-checkout time has already passed
        if (autoCheckoutTime < new Date() && !autoCheckoutTriggered) {
            console.log('Auto checkout time has already passed, triggering immediately');
            autoCheckoutTriggered = true;
            triggerAutoCheckout();
        }
        
        // Debug: Log timing info every 30 seconds
        if (seconds % 30 === 0) {
            const timeUntilAutoCheckout = Math.max(0, autoCheckoutTime.getTime() - now.getTime());
            const remainingHours = Math.floor(timeUntilAutoCheckout / (1000 * 60 * 60));
            const remainingMinutes = Math.floor((timeUntilAutoCheckout % (1000 * 60 * 60)) / (1000 * 60));
            const remainingSeconds = Math.floor((timeUntilAutoCheckout % (1000 * 60)) / 1000);
            
            console.log('Auto checkout timing:', {
                current: now.toISOString(),
                autoCheckoutTime: autoCheckoutTime.toISOString(),
                timeUntilAutoCheckout: `${remainingHours}:${remainingMinutes.toString().padStart(2, '0')}:${remainingSeconds.toString().padStart(2, '0')}`,
                sessionDuration: `${Math.floor(sessionDurationSeconds / 3600)}:${Math.floor((sessionDurationSeconds % 3600) / 60).toString().padStart(2, '0')}:${(sessionDurationSeconds % 60).toString().padStart(2, '0')}`,
                autoCheckoutOver: now >= autoCheckoutTime
            });
        }
    }

    function triggerAutoCheckout() {
        console.log('Triggering auto checkout...');
        console.log('Auto checkout time reached:', autoCheckoutTime.toISOString());
        console.log('Session duration completed:', sessionDurationSeconds, 'seconds');
        
        // Show notification to user
        const notification = document.createElement('div');
        notification.className = 'alert alert-warning alert-dismissible fade show position-fixed';
        notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        notification.innerHTML = `
            <i class="fas fa-clock me-2"></i>
            <strong>Session Complete!</strong> Your session duration has ended. You are being automatically checked out.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        document.body.appendChild(notification);

        // Perform auto checkout
        fetch('{{ route("frontend.checkins.auto-checkout") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                booking_id: {{ $activeCheckin->booking->id }},
                user_id: {{ $user->id }}
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log('Auto checkout successful:', data);
                // Redirect to auto checkout success page
                setTimeout(() => {
                    window.location.href = '{{ route("frontend.checkins.auto-checkout-success") }}';
                }, 2000);
            } else {
                console.error('Auto checkout failed:', data.error);
                notification.className = 'alert alert-danger alert-dismissible fade show position-fixed';
                notification.innerHTML = `
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Error!</strong> Auto checkout failed. Please check out manually.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;
            }
        })
        .catch(error => {
            console.error('Auto checkout error:', error);
            notification.className = 'alert alert-danger alert-dismissible fade show position-fixed';
            notification.innerHTML = `
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>Error!</strong> Auto checkout failed. Please check out manually.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
        });
    }

    // Update the timer every second
    setInterval(updateTimer, 1000);
    updateTimer(); // Initial update
});
</script>
@else
<script>
console.log('No active check-in found');
</script>
@endif
@endsection 