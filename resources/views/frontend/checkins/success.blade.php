@extends('layouts.frontend')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card mt-5">
                <div class="card-body text-center">
                    <div class="mb-4">
                        <i class="fas fa-check-circle text-success" style="font-size: 48px;"></i>
                        <h3 class="mt-3">Check-in Successful!</h3>
                    </div>

                    @if(isset($isLateCheckin) && $isLateCheckin)
                        <div class="alert alert-warning" role="alert">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Late Check-in Notice:</strong> You checked in {{ $lateMinutes }} minutes after the class start time.
                            <br><small>Your session will still end at the scheduled end time.</small>
                        </div>
                    @endif

                    <div class="class-details mb-4">
                        @if($booking && $booking->schedule && $booking->schedule->class)
                            <h4>{{ $booking->schedule->class->name }}</h4>
                        @endif
                        @if($booking && $booking->child)
                            <p>Child: {{ $booking->child->name }}</p>
                        @endif
                        <p>Check-in Time: {{ ($checkin ?? $existingCheckin)->checkin_time->format('h:i A') }}</p>
                        @if($booking && $booking->schedule)
                            <p>Class Time: {{ $booking->schedule->start_time }} - {{ $booking->schedule->end_time }}</p>
                        @endif
                    </div>

                

                    <div class="d-flex justify-content-center gap-3">
                        <form action="{{ route('frontend.checkins.checkout') }}" method="POST">
                            @csrf
                            <input type="hidden" name="booking_id" value="{{ $booking->id }}">
                            <input type="hidden" name="user_id" value="{{ $booking->user_id }}">
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-sign-out-alt"></i> Check Out
                            </button>
                        </form> &nbsp;
                        <a href="{{ route('frontend.checkins.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Check-in
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