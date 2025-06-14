@extends('layouts.frontend')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body text-center">
                    <div class="mb-4">
                        <i class="fas fa-check-circle text-success" style="font-size: 48px;"></i>
                        <h3 class="mt-3">Check-in Successful!</h3>
                    </div>

                    <div class="class-details mb-4">
                        @if($booking && $booking->schedule && $booking->schedule->class)
                            <h4>{{ $booking->schedule->class->name }}</h4>
                        @endif
                        @if($booking && $booking->child)
                            <p>Child: {{ $booking->child->name }}</p>
                        @endif
                        <p>Check-in Time: {{ ($checkin ?? $existingCheckin)->created_at->format('h:i A') }}</p>
                    </div>

                    <div class="timer-container mb-4">
                        <h5>Time Elapsed</h5>
                        <div id="countdown" class="simply-countdown"></div>
                    </div>

                    <div class="d-flex justify-content-center gap-3">
                        <form action="{{ route('frontend.checkins.checkout') }}" method="POST">
                            @csrf
                            <input type="hidden" name="booking_id" value="{{ $booking->id }}">
                            <input type="hidden" name="user_id" value="{{ $booking->user_id }}">
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-sign-out-alt"></i> Check Out
                            </button>
                        </form>
                        <a href="{{ route('frontend.checkins.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Check-in
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/simplycountdown.js@3.0.1/dist/themes/default.css">
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
    .simply-countdown {
        display: flex;
        justify-content: center;
        gap: 20px;
    }
    .simply-countdown > .simply-section {
        background: #fff;
        padding: 15px;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .simply-countdown > .simply-section .simply-amount {
        font-size: 24px;
        font-weight: bold;
        color: #333;
    }
    .simply-countdown > .simply-section .simply-word {
        font-size: 14px;
        color: #666;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/simplycountdown.js@3.0.1/dist/simplyCountdown.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Get the check-in time from the server
    const checkinTime = new Date('{{ ($checkin ?? $existingCheckin)->created_at }}');
    
    // Initialize the countdown
    simplyCountdown('#countdown', {
        year: checkinTime.getFullYear(),
        month: checkinTime.getMonth() + 1,
        day: checkinTime.getDate(),
        hours: checkinTime.getHours(),
        minutes: checkinTime.getMinutes(),
        seconds: checkinTime.getSeconds(),
        words: {
            days: { root: 'day', lambda: (root, n) => n > 1 ? root + 's' : root },
            hours: { root: 'hr', lambda: (root, n) => n > 1 ? root + 's' : root },
            minutes: { root: 'min', lambda: (root, n) => n > 1 ? root + 's' : root },
            seconds: { root: 'sec', lambda: (root, n) => n > 1 ? root + 's' : root }
        },
        countUp: true,
        zeroPad: true,
        refresh: 1000,
        inline: false,
        enableUtc: false,
        onEnd: function() {
            // Optional: Handle when countdown ends
        }
    });
});
</script>
@endpush
@endsection 