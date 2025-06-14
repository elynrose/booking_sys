@extends('layouts.frontend')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <!-- Today's Schedules Card -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Today's Classes</h5>
                </div>
                <div class="card-body">
                    @if($todaySchedules->isEmpty())
                        <p class="text-muted">No classes scheduled for today.</p>
                    @else
                        <div class="row">
                            @foreach($todaySchedules as $schedule)
                                <div class="col-md-6 mb-3">
                                    <div class="card h-100">
                                        <div class="card-body">
                                            <h5 class="card-title">{{ $schedule->title }}</h5>
                                            <p class="card-text mt-3">
                                                <small class="text-muted">
                                                    <i class="fas fa-clock"></i> {{ \Carbon\Carbon::parse($schedule->start_time)->format('g:i A') }} - 
                                                    {{ \Carbon\Carbon::parse($schedule->end_time)->format('g:i A') }}
                                                </small>
                                            </p>
                                            <p class="card-text text-muted small">
                                                <i class="fas fa-users"></i> {{ $schedule->bookings->count() }} of {{ $schedule->max_participants }} max
                                            </p>
                                            <a href="{{ route('frontend.schedules.show', $schedule) }}" class="btn btn-primary btn-sm">View Details</a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <!-- Pending Payments Card -->
            <div class="card mb-4">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">Pending Payments</h5>
                </div>
                <div class="card-body">
                    @if($pendingPayments->isEmpty())
                        <p class="text-muted">No pending payments to confirm.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Parent</th>
                                        <th>Child</th>
                                        <th>Class</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pendingPayments as $payment)
                                        <tr>
                                            <td>{{ $payment->created_at->format('M d, Y') }}</td>
                                            <td>{{ $payment->booking->user->name }}</td>
                                            <td>{{ $payment->booking->child->name }}</td>
                                            <td>{{ $payment->booking->schedule->title }}</td>
                                            <td>${{ number_format($payment->amount, 2) }}</td>
                                            <td>
                                                <span class="badge bg-warning text-dark">Pending</span>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#confirmPaymentModal{{ $payment->id }}">
                                                    <i class="fas fa-check"></i> Confirm
                                                </button>
                                            </td>
                                        </tr>

                                        <!-- Confirm Payment Modal -->
                                        <div class="modal fade" id="confirmPaymentModal{{ $payment->id }}" tabindex="-1" role="dialog" aria-labelledby="confirmPaymentModalLabel{{ $payment->id }}" aria-hidden="true">
                                            <div class="modal-dialog" role="document">
                                                <div class="modal-content">
                                                    <form action="{{ route('frontend.trainer.confirm-payment', $payment->id) }}" method="POST">
                                                        @csrf
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="confirmPaymentModalLabel{{ $payment->id }}">Confirm Payment</h5>
                                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                <span aria-hidden="true">&times;</span>
                                                            </button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="form-group">
                                                                <label for="description{{ $payment->id }}">Payment Description</label>
                                                                <textarea class="form-control" id="description{{ $payment->id }}" name="description" rows="3" required>{{ $payment->description }}</textarea>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                                            <button type="submit" class="btn btn-success">Confirm Payment</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Today's Check-ins Card -->
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">Today's Check-ins</h5>
                </div>
                <div class="card-body">
                    @if($todayCheckins->isEmpty())
                        <p class="text-muted">No check-ins recorded for today.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Time</th>
                                        <th>Child</th>
                                        <th>Parent</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($todayCheckins as $checkin)
                                        <tr>
                                            <td>{{ $checkin->created_at->format('g:i A') }}</td>
                                            <td>{{ $checkin->booking->child->name }}</td>
                                            <td>{{ $checkin->booking->user->name }}</td>
                                            <td>
                                                @if($checkin->checkout_time)
                                                    <span class="badge bg-success text-white">Checked Out</span>
                                                @else
                                                    <span class="badge bg-primary text-white">Checked In</span>
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

            <!-- Upcoming Schedules Card -->
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">Upcoming Classes</h5>
                </div>
                <div class="card-body">
                    @if($upcomingSchedules->isEmpty())
                        <p class="text-muted">No upcoming classes scheduled.</p>
                    @else
                        <div class="row">
                            @foreach($upcomingSchedules as $schedule)
                                <div class="col-md-6 mb-3">
                                    <div class="card h-100">
                                        <div class="card-body">
                                            <h6 class="card-title">{{ $schedule->title }}</h6>
                                            <p class="card-text">
                                                <small class="text-muted">
                                                    <i class="fas fa-calendar"></i> {{ \Carbon\Carbon::parse($schedule->start_date)->format('M d, Y') }}<br>
                                                    <i class="fas fa-clock"></i> {{ \Carbon\Carbon::parse($schedule->start_time)->format('g:i A') }} - 
                                                    {{ \Carbon\Carbon::parse($schedule->end_time)->format('g:i A') }}
                                                </small>
                                            </p>
                                            <p class="card-text">
                                                <i class="fas fa-users"></i> {{ $schedule->bookings->count() }} / {{ $schedule->max_participants }} participants
                                            </p>
                                            <a href="{{ route('frontend.schedules.show', $schedule) }}" class="btn btn-primary btn-sm">View Details</a>
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
@endsection 