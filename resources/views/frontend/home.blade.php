@extends('layouts.frontend')

@section('content')
<div class="container py-4">
    <!-- Welcome Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-1">Welcome back, {{ Auth::user()->name }}!</h2>
                            <p class="mb-0">Here's an overview of your fitness journey</p>
                            @if(Auth::user()->member_id)
                                <div class="mt-2">
                                    <span class="badge bg-light text-dark fs-6">
                                        <i class="fas fa-id-card me-2"></i>
                                        Member ID: {{ Auth::user()->member_id }}
                                    </span>
                                </div>
                            @endif
                        </div>
                        <div class="text-end">
                            <h3 class="mb-0">{{ now()->format('l, F j, Y') }}</h3>
                            <small>Today's Date</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Overview -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3 mb-md-0">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="px-3">
                                <i class="far fa-calendar-check fa-2x text-primary"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Active Schedules</h6>
                            <h3 class="mb-0">{{ $activeSchedules->count() }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3 mb-md-0">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="px-3">
                                <i class="fas fa-clock fa-2x text-warning"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Pending Check-ins</h6>
                            <h3 class="mb-0">{{ $pendingCheckins->count() }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3 mb-md-0">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="px-3">
                                <i class="fas fa-dollar-sign fa-2x text-danger"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Pending Payments</h6>
                            <h3 class="mb-0">${{ number_format($pendingPayments, 2) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="px-3">
                                <i class="fas fa-check-circle fa-2x text-success"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Total Paid</h6>
                            <h3 class="mb-0">${{ number_format($paidBookingsTotal, 2) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Active Schedules -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">Your Active Schedules</h5>
                </div>
                <div class="card-body">
                    @forelse($activeSchedules as $booking)
                    <div class="schedule-item mb-3 p-3 border rounded">
                        <div class="row align-items-center">
                            <div class="col-md-2">
                                <img src="{{ $booking->schedule->photo_url }}" alt="{{ $booking->schedule->title }}" class="img-fluid rounded" style="max-height: 100px; width: 100%; object-fit: cover;">
                            </div>
                            <div class="col-md-3">
                                <h6 class="mb-1">{{ $booking->schedule->title }}</h6>
                                <small class="text-muted">{{ $booking->schedule->trainer ? $booking->schedule->trainer->name : 'No trainer assigned' }}</small>
                                <br>
                                <small class="text-muted">
                                    <i class="far fa-calendar me-2"></i>
                                    {{ $booking->schedule->start_date->format('M d') }} - {{ $booking->schedule->end_date->format('M d, Y') }}
                                </small>
                            </div>
                            <div class="col-md-3">
                                <div class="d-flex align-items-center">
                                    <div>
                                        <small class="d-block text-muted">Next Session for {{ $booking->child->name }}</small>
                                        <span>{{ $booking->schedule->getNextSessionDate() ? $booking->schedule->getNextSessionDate()->format('M d, Y') : 'No upcoming sessions' }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="d-flex align-items-center">
                                    <div>
                                        <small class="d-block text-muted">Sessions</small>
                                        @if($booking->sessions_remaining > 0 && $booking->checkins->count() < $booking->sessions_remaining)
                                            <span>{{ $booking->sessions_remaining }} remaining</span>
                                        @else
                                            <span class="text-secondary">
                                                <i class="fas fa-check-circle me-1"></i> Sessions Completed
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2 text-end">
                                @if($booking->checkins->where('checkout_time', null)->isNotEmpty())
                                    <span class="badge bg-success">Checked In</span>
                                @else
                                    <a href="{{ route('frontend.checkins.verify') }}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-qrcode me-1"></i> Check In
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-4">
                        <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                        <p class="text-muted mb-0">No active schedules found</p>
                        <a href="{{ route('frontend.schedules.index') }}" class="btn btn-primary mt-3">
                            <i class="fas fa-plus me-1"></i> Find Classes
                        </a>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Payment History -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">Recent Payments</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Description</th>
                                    @if(Auth::user()->hasRole('Trainer'))
                                        <th>Parent</th>
                                        <th>Child</th>
                                    @else
                                        <th>Trainer</th>
                                        <th>Child</th>
                                    @endif
                                    <th>Amount</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($paymentHistory as $payment)
                                <tr>
                                    <td>{{ $payment->created_at->format('M d, Y') }}</td>
                                    <td>
                                        @if($payment->booking && $payment->booking->schedule)
                                            {{ $payment->booking->schedule->title }}
                                        @else
                                            {{ $payment->description ?? 'Payment' }}
                                        @endif
                                    </td>
                                    <td>
                                        @if(Auth::user()->hasRole('Trainer'))
                                            @if($payment->booking && $payment->booking->user)
                                                <i class="fas fa-user me-1"></i>
                                                {{ $payment->booking->user->name }}
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        @else
                                            @if($payment->booking && $payment->booking->schedule && $payment->booking->schedule->trainer)
                                                <i class="fas fa-user-tie me-1"></i>
                                                {{ $payment->booking->schedule->trainer->user->name ?? 'N/A' }}
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        @endif
                                    </td>
                                    <td>
                                        @if($payment->booking && $payment->booking->child)
                                            <i class="fas fa-child me-1"></i>
                                            {{ $payment->booking->child->name }}
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>${{ number_format($payment->amount, 2) }}</td>
                                    <td>
                                        <span class="badge bg-{{ $payment->status === 'completed' ? 'success' : ($payment->status === 'refunded' ? 'refunded' : 'warning') }}">
                                            {{ ucfirst($payment->status) }}
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4">
                                        <i class="fas fa-receipt fa-3x text-muted mb-3"></i>
                                        <p class="text-muted mb-0">No payment history found</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.schedule-item {
    transition: all 0.3s ease;
}

.schedule-item:hover {
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.bg-opacity-10 {
    opacity: 0.1;
}

.table th {
    font-weight: 600;
    color: #666;
}

.badge {
    padding: 0.5em 0.75em;
}
</style>
@endpush
@endsection