@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">
                        <i class="fas fa-calendar-alt me-2"></i>
                        Trainer Availability Management
                    </h4>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Schedule</th>
                                    <th>Trainer</th>
                                    <th>Category</th>
                                    <th>Schedule Period</th>
                                    <th>Next Session</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($schedules as $schedule)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                @if($schedule->photo)
                                                    <img src="{{ $schedule->photo_url }}" alt="{{ $schedule->title }}" 
                                                         class="rounded me-3" style="width: 50px; height: 50px; object-fit: cover;">
                                                @else
                                                    <div class="bg-secondary rounded me-3 d-flex align-items-center justify-content-center" 
                                                         style="width: 50px; height: 50px;">
                                                        <i class="fas fa-calendar text-white"></i>
                                                    </div>
                                                @endif
                                                <div>
                                                    <h6 class="mb-0">{{ $schedule->title }}</h6>
                                                    <small class="text-muted">
                                                        @if($schedule->allow_unlimited_bookings)
                                                            <span class="badge bg-success">Unlimited</span>
                                                        @else
                                                            <span class="badge bg-info">Limited Sessions</span>
                                                        @endif
                                                    </small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            @if($schedule->trainer && $schedule->trainer->user)
                                                <div class="d-flex align-items-center">
                                                    @if($schedule->trainer->user->photo)
                                                        <img src="{{ $schedule->trainer->user->photo_url }}" 
                                                             alt="{{ $schedule->trainer->user->name }}" 
                                                             class="rounded-circle me-2" style="width: 30px; height: 30px; object-fit: cover;">
                                                    @else
                                                        <div class="bg-secondary rounded-circle me-2 d-flex align-items-center justify-content-center" 
                                                             style="width: 30px; height: 30px;">
                                                            <i class="fas fa-user text-white"></i>
                                                        </div>
                                                    @endif
                                                    <span>{{ $schedule->trainer->user->name }}</span>
                                                </div>
                                            @else
                                                <span class="text-muted">No trainer assigned</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($schedule->category)
                                                <span class="badge bg-primary">{{ $schedule->category->name }}</span>
                                            @else
                                                <span class="text-muted">No category</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div>
                                                <small class="text-muted">From:</small><br>
                                                {{ $schedule->start_date->format('M d, Y') }}
                                            </div>
                                            <div class="mt-1">
                                                <small class="text-muted">To:</small><br>
                                                {{ $schedule->end_date->format('M d, Y') }}
                                            </div>
                                        </td>
                                        <td>
                                            @if($schedule->getNextSessionDate())
                                                <span class="badge bg-success">
                                                    {{ $schedule->getNextSessionDate()->format('M d, Y') }}
                                                </span>
                                            @else
                                                <span class="badge bg-secondary">No upcoming sessions</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="{{ route('admin.trainer-availability.calendar', $schedule) }}" 
                                                   class="btn btn-sm btn-primary" title="Calendar View">
                                                    <i class="fas fa-calendar-alt"></i>
                                                </a>
                                                <a href="{{ route('admin.trainer-availability.show', $schedule) }}" 
                                                   class="btn btn-sm btn-secondary" title="List View">
                                                    <i class="fas fa-list"></i>
                                                </a>
                                                <a href="{{ route('admin.trainer-availability.export', $schedule) }}" 
                                                   class="btn btn-sm btn-outline-success" title="Export">
                                                    <i class="fas fa-download"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4">
                                            <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                                            <h5 class="text-muted">No schedules found</h5>
                                            <p class="text-muted">Create schedules to manage trainer availability.</p>
                                            <a href="{{ route('admin.schedules.create') }}" class="btn btn-primary">
                                                <i class="fas fa-plus me-1"></i> Create Schedule
                                            </a>
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

<style>
.table th {
    font-weight: 600;
    color: #666;
}

.badge {
    font-size: 0.85em;
}

.btn-group .btn {
    margin: 0 2px;
}
</style>
@endsection 