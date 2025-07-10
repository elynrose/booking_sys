@extends('layouts.frontend')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-calendar-times"></i>
                        My Unavailable Times
                    </h5>
                    <div>
                        <a href="{{ route('frontend.trainer.unavailability.create') }}" class="btn btn-outline-dark btn-sm">
                            <i class="fas fa-plus"></i> Mark Unavailable
                        </a>
                        <a href="{{ route('frontend.trainer.unavailability.settings') }}" class="btn btn-outline-dark btn-sm">
                            <i class="fas fa-cog"></i> Settings
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>New System:</strong> You are available by default. This page shows only the times when you've marked yourself as unavailable.
                    </div>

                    @if($unavailabilities->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Time</th>
                                        <th>Reason</th>
                                        <th>Schedule</th>
                                        <th>Notes</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($unavailabilities as $unavailability)
                                        <tr>
                                            <td>
                                                <strong>{{ \Carbon\Carbon::parse($unavailability->date)->format('M d, Y') }}</strong>
                                                <br>
                                                <small class="text-muted">{{ \Carbon\Carbon::parse($unavailability->date)->format('l') }}</small>
                                            </td>
                                            <td>
                                                @if($unavailability->start_time && $unavailability->end_time)
                                                    {{ \Carbon\Carbon::parse($unavailability->start_time)->format('g:i A') }} - 
                                                    {{ \Carbon\Carbon::parse($unavailability->end_time)->format('g:i A') }}
                                                @else
                                                    <span class="badge badge-warning">All Day</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge badge-{{ $unavailability->reason === 'sick' ? 'danger' : ($unavailability->reason === 'vacation' ? 'info' : 'secondary') }}">
                                                    {{ ucfirst($unavailability->reason) }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($unavailability->schedule)
                                                    {{ $unavailability->schedule->title }}
                                                @else
                                                    <span class="text-muted">All Schedules</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($unavailability->notes)
                                                    <small class="text-muted">{{ Str::limit($unavailability->notes, 50) }}</small>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="{{ route('frontend.trainer.unavailability.edit', $unavailability->id) }}" 
                                                       class="btn btn-outline-primary">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form action="{{ route('frontend.trainer.unavailability.destroy', $unavailability->id) }}" 
                                                          method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-outline-danger" 
                                                                onclick="return confirm('Are you sure you want to delete this unavailability period?')">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-center">
                            {{ $unavailabilities->links() }}
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-calendar-check fa-3x text-success mb-3"></i>
                            <h5>No Unavailable Times Set</h5>
                            <p class="text-muted">You haven't marked any times as unavailable. You're available by default!</p>
                            <a href="{{ route('frontend.trainer.unavailability.create') }}" class="btn btn-warning">
                                <i class="fas fa-plus"></i> Mark Unavailable Time
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 