@extends('layouts.frontend')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <!-- Class Header -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">{{ $schedule->title }}</h4>
                        <a href="{{ route('frontend.trainer.index') }}" class="btn btn-light btn-sm">
                            <i class="fas fa-arrow-left"></i> Back to Dashboard
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Class Information</h5>
                            <p><strong>Date:</strong> 
                                @if($schedule->start_date && $schedule->end_date)
                                    {{ \Carbon\Carbon::parse($schedule->start_date)->format('M d, Y') }} - 
                                    {{ \Carbon\Carbon::parse($schedule->end_date)->format('M d, Y') }}
                                @else
                                    Not set
                                @endif
                            </p>
                            <p><strong>Time:</strong> 
                                @if($schedule->start_time && $schedule->end_time)
                                    {{ \Carbon\Carbon::parse($schedule->start_time)->format('g:i A') }} - 
                                    {{ \Carbon\Carbon::parse($schedule->end_time)->format('g:i A') }}
                                @else
                                    Not set
                                @endif
                            </p>
                            <p><strong>Type:</strong> {{ ucfirst($schedule->type ?? 'Not specified') }}</p>
                            @if($schedule->location)
                            <p><strong>Location:</strong> {{ $schedule->location }}</p>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <h5>Enrollment Statistics</h5>
                            <p><strong>Total Students:</strong> {{ $bookingsWithStats->count() }}</p>
                            <p><strong>Max Participants:</strong> {{ $schedule->max_participants ?? 'Unlimited' }}</p>
                            <p><strong>Currently Checked In:</strong> 
                                {{ $bookingsWithStats->sum(function($booking) { return $booking->checkin_stats['currently_checked_in']; }) }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Students List -->
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">Enrolled Students</h5>
                </div>
                <div class="card-body">
                    @if($bookingsWithStats->isEmpty())
                        <p class="text-muted">No students enrolled in this class.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Student(s)</th>
                                        <th>Parent</th>
                                        <th>Payment Status</th>
                                        <th>Check-in History</th>
                                        <th>Last Activity</th>
                                        <th>Current Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($bookingsWithStats as $booking)
                                        <tr>
                                            <td>
                                                @if($booking->child)
                                                    <div class="mb-1">
                                                        <i class="fas fa-child mr-2"></i>
                                                        <a href="{{ route('frontend.trainer.student-details', ['child' => $booking->child->id, 'schedule' => $schedule->id]) }}" class="text-primary font-weight-bold">
                                                            {{ $booking->child->name }}
                                                        </a>
                                                        @if($booking->child->age)
                                                            <small class="text-muted">({{ $booking->child->age }} years)</small>
                                                        @endif
                                                    </div>
                                                @else
                                                    <i class="fas fa-user mr-2"></i>
                                                    <a href="{{ route('frontend.trainer.student-details', ['user' => $booking->user->id, 'schedule' => $schedule->id]) }}" class="text-primary font-weight-bold">
                                                        {{ $booking->user->name }}
                                                    </a>
                                                @endif
                                            </td>
                                            <td>
                                                <div>
                                                    <i class="fas fa-user mr-2"></i>
                                                    {{ $booking->user->name }}
                                                </div>
                                                <small class="text-muted">{{ $booking->user->email }}</small>
                                            </td>
                                            <td>
                                                <span class="badge badge-{{ $booking->payment_status === 'paid' ? 'success' : ($booking->payment_status === 'pending' ? 'warning' : 'danger') }}">
                                                    <i class="fas fa-credit-card mr-1"></i>
                                                    {{ ucfirst($booking->payment_status) }}
                                                </span>
                                                @if($booking->payment_method)
                                                    <br><small class="text-muted">{{ ucfirst($booking->payment_method) }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="text-center">
                                                    <div class="row">
                                                        <div class="col-4">
                                                            <div class="text-primary">
                                                                <strong>{{ $booking->checkin_stats['total_checkins'] }}</strong>
                                                            </div>
                                                            <small class="text-muted">Check-ins</small>
                                                        </div>
                                                        <div class="col-4">
                                                            <div class="text-success">
                                                                <strong>{{ $booking->checkin_stats['total_checkouts'] }}</strong>
                                                            </div>
                                                            <small class="text-muted">Check-outs</small>
                                                        </div>
                                                        <div class="col-4">
                                                            <div class="text-info">
                                                                <strong>{{ $booking->checkin_stats['currently_checked_in'] }}</strong>
                                                            </div>
                                                            <small class="text-muted">Active</small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                @if($booking->checkin_stats['last_checkin'])
                                                    <div>
                                                        <strong>Last Check-in:</strong><br>
                                                        <small>{{ \Carbon\Carbon::parse($booking->checkin_stats['last_checkin'])->format('M d, g:i A') }}</small>
                                                    </div>
                                                @endif
                                                @if($booking->checkin_stats['last_checkout'])
                                                    <div class="mt-1">
                                                        <strong>Last Check-out:</strong><br>
                                                        <small>{{ \Carbon\Carbon::parse($booking->checkin_stats['last_checkout'])->format('M d, g:i A') }}</small>
                                                    </div>
                                                @endif
                                                @if(!$booking->checkin_stats['last_checkin'] && !$booking->checkin_stats['last_checkout'])
                                                    <span class="text-muted">No activity yet</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($booking->checkin_stats['currently_checked_in'] > 0)
                                                    <span class="badge badge-success">
                                                        <i class="fas fa-sign-in-alt mr-1"></i>
                                                        Checked In
                                                    </span>
                                                @else
                                                    <span class="badge badge-secondary">
                                                        <i class="fas fa-sign-out-alt mr-1"></i>
                                                        Not Checked In
                                                    </span>
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
    </div>
</div>

<style>
.table th {
    background-color: #f8f9fa;
    border-top: none;
    font-weight: 600;
}

.badge {
    font-size: 0.8em;
}

.text-primary {
    color: #007bff !important;
}

.text-success {
    color: #28a745 !important;
}

.text-info {
    color: #17a2b8 !important;
}

.card-header {
    border-bottom: none;
}

.table-responsive {
    border-radius: 0.375rem;
}
</style>
@endsection 