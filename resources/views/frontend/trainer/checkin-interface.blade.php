@extends('layouts.frontend')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-sign-in-alt"></i> Trainer Check-In Interface
                    </h4>
                    <p class="mb-0 text-light">Check in and out students for your classes today</p>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i> {{ session('success') }}
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                        </div>
                    @endif

                    @if($todaySchedules->isEmpty())
                        <div class="text-center py-5">
                            <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No Classes Today</h5>
                            <p class="text-muted">You don't have any classes scheduled for today.</p>
                        </div>
                    @else
                        <div class="row">
                            @foreach($todaySchedules as $schedule)
                                <div class="col-md-6 mb-4">
                                    <div class="card h-100">
                                        <div class="card-header bg-light">
                                            <h5 class="mb-0">
                                                <i class="fas fa-dumbbell text-primary"></i> {{ $schedule->title }}
                                            </h5>
                                            <small class="text-muted">
                                                <i class="fas fa-clock"></i> 
                                                {{ \Carbon\Carbon::parse($schedule->start_time)->format('g:i A') }} - 
                                                {{ \Carbon\Carbon::parse($schedule->end_time)->format('g:i A') }}
                                            </small>
                                        </div>
                                        <div class="card-body">
                                            @if($schedule->location)
                                                <p class="text-muted mb-2">
                                                    <i class="fas fa-map-marker-alt"></i> {{ $schedule->location }}
                                                </p>
                                            @endif

                                            <div class="mb-3">
                                                <span class="badge bg-info">
                                                    {{ $schedule->bookings->count() }} Students
                                                </span>
                                            </div>

                                            @if($schedule->bookings->isEmpty())
                                                <p class="text-muted">No students enrolled in this class.</p>
                                            @else
                                                <div class="table-responsive">
                                                    <table class="table table-sm">
                                                        <thead>
                                                            <tr>
                                                                <th>Student</th>
                                                                <th>Status</th>
                                                                <th>Actions</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach($schedule->bookings as $booking)
                                                                @php
                                                                    $studentName = $booking->child ? $booking->child->name : $booking->user->name;
                                                                    $isCheckedIn = $booking->checkins->where('checkout_time', null)->count() > 0;
                                                                @endphp
                                                                <tr>
                                                                    <td>
                                                                        <div class="d-flex align-items-center">
                                                                            @if($booking->child)
                                                                                <div class="bg-warning rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 30px; height: 30px;">
                                                                                    <i class="fas fa-child text-white" style="font-size: 0.8rem;"></i>
                                                                                </div>
                                                                            @else
                                                                                <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 30px; height: 30px;">
                                                                                    <i class="fas fa-user text-white" style="font-size: 0.8rem;"></i>
                                                                                </div>
                                                                            @endif
                                                                            <div>
                                                                                <strong>{{ $studentName }}</strong>
                                                                                @if($booking->child)
                                                                                    <br><small class="text-muted">Parent: {{ $booking->user->name }}</small>
                                                                                @endif
                                                                            </div>
                                                                        </div>
                                                                    </td>
                                                                    <td>
                                                                        @if($isCheckedIn)
                                                                            <span class="badge bg-success">
                                                                                <i class="fas fa-check-circle"></i> Checked In
                                                                            </span>
                                                                        @else
                                                                            <span class="badge bg-secondary">
                                                                                <i class="fas fa-clock"></i> Not Checked In
                                                                            </span>
                                                                        @endif
                                                                    </td>
                                                                    <td>
                                                                        @if($isCheckedIn)
                                                                            <form action="{{ route('frontend.trainer.checkout.student') }}" method="POST" class="d-inline">
                                                                                @csrf
                                                                                <input type="hidden" name="booking_id" value="{{ $booking->id }}">
                                                                                <input type="hidden" name="schedule_id" value="{{ $schedule->id }}">
                                                                                <button type="submit" class="btn btn-outline-danger btn-sm" 
                                                                                        onclick="return confirm('Check out {{ $studentName }}?')">
                                                                                    <i class="fas fa-sign-out-alt"></i> Check Out
                                                                                </button>
                                                                            </form>
                                                                        @else
                                                                            <form action="{{ route('frontend.trainer.checkin.student') }}" method="POST" class="d-inline">
                                                                                @csrf
                                                                                <input type="hidden" name="booking_id" value="{{ $booking->id }}">
                                                                                <input type="hidden" name="schedule_id" value="{{ $schedule->id }}">
                                                                                <button type="submit" class="btn btn-success btn-sm">
                                                                                    <i class="fas fa-sign-in-alt"></i> Check In
                                                                                </button>
                                                                            </form>
                                                                        @endif
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            @endif
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
.card {
    transition: transform 0.2s;
}
.card:hover {
    transform: translateY(-2px);
}
.badge {
    font-size: 0.8rem;
}
.table th {
    font-size: 0.9rem;
    font-weight: 600;
}
.table td {
    font-size: 0.9rem;
    vertical-align: middle;
}
.btn-sm {
    font-size: 0.8rem;
}
</style>
@endsection 