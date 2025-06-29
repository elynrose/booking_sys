@extends('layouts.frontend')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-body text-center p-5">
                    <!-- Success Icon -->
                    <div class="mb-4">
                        <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>
                    </div>

                    <!-- Success Message -->
                    <h3 class="mt-3">{{ __('app.checkins.checkin_successful') }}</h3>
                    
                    @if(isset($lateMinutes) && $lateMinutes > 0)
                        <div class="alert alert-warning mt-3">
                            <strong>{{ __('app.checkins.late_checkin_notice', ['minutes' => $lateMinutes]) }}</strong>
                            <br><small>{{ __('app.checkins.late_checkin_help') }}</small>
                        </div>
                    @endif

                    <!-- Session Details -->
                    <div class="mt-4">
                        <h4>{{ $booking->schedule->class->name }}</h4>
                        
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <p>{{ __('app.checkins.child') }}: {{ $booking->child->name }}</p>
                            </div>
                            <div class="col-md-6">
                                <p>{{ __('app.checkins.checkin_time') }}: {{ ($checkin ?? $existingCheckin)->checkin_time->format('h:i A') }}</p>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <p>{{ __('app.checkins.class_time') }}: {{ $booking->schedule->start_time }} - {{ $booking->schedule->end_time }}</p>
                            </div>
                            <div class="col-md-6">
                                <p>{{ __('app.schedules.participants') }}: {{ $booking->schedule->current_participants }} / {{ $booking->schedule->max_participants }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="mt-4">
                        <a href="{{ route('frontend.schedules.index') }}" class="btn btn-primary me-2">
                            <i class="fas fa-calendar me-1"></i>{{ __('app.welcome.view_classes') }}
                        </a>
                        <a href="{{ route('frontend.profile.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-user me-1"></i>{{ __('app.profile.my_profile') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
    .timer-container {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 10px;
    }
    .class-details {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 10px;
    }
    .timer-display {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 10px;
    }
    .timer-box {
        background: #fff;
        padding: 15px;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        min-width: 80px;
        text-align: center;
    }
    .timer-box span:first-child {
        font-size: 24px;
        font-weight: bold;
        color: #333;
        display: block;
    }
    .timer-label {
        font-size: 14px;
        color: #666;
    }
    .timer-separator {
        font-size: 24px;
        font-weight: bold;
        color: #333;
    }
</style>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const checkinTime = new Date('{{ ($checkin ?? $existingCheckin)->checkin_time->toISOString() }}');
    console.log('Check-in time:', checkinTime.toISOString());

    function updateTimer() {
        const now = new Date();
        const diff = Math.floor((now - checkinTime) / 1000); // Convert to seconds
        
        // Calculate hours, minutes, and seconds
        const hours = Math.floor(diff / 3600);
        const minutes = Math.floor((diff % 3600) / 60);
        const seconds = diff % 60;
        
        // Update the display
        document.getElementById('hours').textContent = hours.toString().padStart(2, '0');
        document.getElementById('minutes').textContent = minutes.toString().padStart(2, '0');
        document.getElementById('seconds').textContent = seconds.toString().padStart(2, '0');
    }

    // Update the timer immediately and then every second
    updateTimer();
    setInterval(updateTimer, 1000);
});
</script>
@endsection 