@extends('layouts.frontend')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-body text-center p-5">
                    <!-- Session Completed Icon -->
                    <div class="mb-4">
                        <i class="fas fa-clock text-warning" style="font-size: 4rem;"></i>
                    </div>

                    <!-- Session Completed Message -->
                    <h3 class="text-warning">{{ __('app.checkins.session_completed') }}</h3>
                    <p class="lead">{{ __('app.checkins.session_ended') }}</p>

                    <!-- Session Summary -->
                    <div class="mt-4">
                        <h5>{{ __('app.checkins.session_summary') }}</h5>
                        
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <p><strong>{{ __('app.checkins.class') }}:</strong> {{ $booking->schedule->title }}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>{{ __('app.checkins.child') }}:</strong> {{ $booking->child->name }}</p>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>{{ __('app.checkins.checkin') }}:</strong> {{ $checkin->checkin_time->format('M d, Y h:i A') }}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>{{ __('app.checkins.checkout') }}:</strong> {{ $checkin->checkout_time->format('M d, Y h:i A') }}</p>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>{{ __('app.checkins.duration') }}:</strong> {{ sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds) }}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>{{ __('app.checkins.sessions_remaining') }}:</strong> {{ $booking->sessions_remaining }}</p>
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