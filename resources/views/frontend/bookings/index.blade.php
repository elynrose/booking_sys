@extends('layouts.frontend')

@section('content')
<div class="container py-5">
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

    <div class="row justify-content-center">
        
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">My Bookings</h4>
                    <a href="{{ route('frontend.schedules.index') }}" class="btn btn-light btn-sm">
                        <i class="fas fa-plus me-2"></i> New Booking
                    </a>
                </div>
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

                    @if($bookings->isEmpty())
                        <div class="text-center py-5">
                            <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No bookings found</h5>
                            <p class="text-muted">Start by booking a class from our schedule.</p>
                            <a href="{{ route('frontend.schedules.index') }}" class="btn btn-primary mt-3">
                                <i class="fas fa-calendar-plus me-2"></i>View Schedule
                            </a>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Class</th>
                                        <th>Schedule</th>
                                        <th>Status</th>
                                        <th>Payment</th>
                                        <th>Sessions</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($bookings as $booking)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                   
                                                    <div class="flex-grow-1 ms-3">
                                                        <h6 class="mb-0">{{ $booking->schedule->title }}</h6>
                                                        <small class="text-muted">
                                                            @if($booking->child)
                                                                {{ $booking->child->name }} ({{ $booking->child->age }} years)
                                                            @else
                                                                No child assigned
                                                            @endif
                                                        </small>
                                                        <br>
                                                        <small class="text-muted">#{{ $booking->id }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div>
                                                    @if($booking->schedule->start_date && $booking->schedule->end_date)
                                                        <div>{{ $booking->schedule->start_date->format('Y-m-d') }} to {{ $booking->schedule->end_date->format('Y-m-d') }}</div>
                                                    @else
                                                        <div class="text-muted">No dates set</div>
                                                    @endif
                                                    <small class="text-muted">
                                                        {{ $booking->schedule->start_time->format('h:i A') }} - {{ $booking->schedule->end_time->format('h:i A') }}
                                                    </small>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $booking->status === 'confirmed' ? 'success' : 'warning' }}">
                                                    {{ ucfirst($booking->status) }}
                                                </span>
                                            </td>
                                            <td>
                                              
                                                @if($booking->is_paid)
                                                    <div class="small text-muted mt-1">
                                                        <i class="fas fa-check-circle text-success"></i>
                                                        {{ $booking->payment_method ? ucfirst($booking->payment_method) : 'Payment'  }} completed
                                                        @if($booking->payment_date)
                                                            <br>
                                                            <small>{{ $booking->payment_date->format('M d, Y') }}</small>
                                                        @endif
                                                    </div>
                                                @else
                                                    <div class="small text-muted mt-1">
                                                        <i class="fas fa-times-circle text-danger"></i>
                                                        {{ $booking->payment_method ? ucfirst($booking->payment_method) : 'Payment'  }} not completed
                                                    </div>
                                                @endif
                                            </td>
                                            <td>
                                                @if($booking->sessions_remaining > 0 && $booking->checkins->count() < $booking->sessions_remaining)
                                                    <span class="badge bg-info">
                                                        {{ $booking->sessions_remaining }} remaining
                                                    </span>
                                                @else
                                                    <span class="badge bg-secondary">
                                                        <i class="fas fa-check-circle me-1"></i> Sessions Completed
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="{{ route('frontend.bookings.show', $booking) }}" 
                                                       class="btn btn-sm btn-light" 
                                                       title="View Details">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    @if(!$booking->is_paid)
                                                        <a href="{{ route('frontend.payments.index', ['booking_id' => $booking->id]) }}" 
                                                           class="btn btn-sm btn-primary" 
                                                           title="Complete Payment">
                                                            <i class="fas fa-credit-card"></i>
                                                        </a>
                                                    @endif
                                                    @if($booking->status === 'pending' && !$booking->is_paid)
                                                        <form action="{{ route('frontend.bookings.destroy', $booking) }}" 
                                                              method="POST" 
                                                              class="d-inline"
                                                              onsubmit="return confirm('Are you sure you want to cancel this booking?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" 
                                                                    class="btn btn-sm btn-danger" 
                                                                    title="Cancel Booking">
                                                                <i class="fas fa-times"></i>
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-center mt-4">
                            {{ $bookings->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.table > :not(caption) > * > * {
    padding: 1rem;
}
.btn-group .btn {
    margin: 0 2px;
}
.badge {
    font-size: 0.85em;
    padding: 0.5em 0.75em;
}
</style>
@endsection 