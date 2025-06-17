@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Schedules</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.schedules.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Add New Schedule
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Stat Cards -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card">
                                <div class="card-body bg-info text-white">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h3 class="mb-0">{{ $totalSchedules ?? 0 }}</h3>
                                            <p class="mb-0">Total Schedules</p>
                                        </div>
                                        <div class="icon">
                                            <i class="fas fa-calendar fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card">
                                <div class="card-body bg-success text-white">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h3 class="mb-0">{{ $activeSchedules ?? 0 }}</h3>
                                            <p class="mb-0">Active</p>
                                        </div>
                                        <div class="icon">
                                            <i class="fas fa-check-circle fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card">
                                <div class="card-body bg-danger text-white">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h3 class="mb-0">{{ $inactiveSchedules ?? 0 }}</h3>
                                            <p class="mb-0">Inactive</p>
                                        </div>
                                        <div class="icon">
                                            <i class="fas fa-times-circle fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card">
                                <div class="card-body bg-warning text-white">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h3 class="mb-0">{{ $upcomingSchedules ?? 0 }}</h3>
                                            <p class="mb-0">Upcoming</p>
                                        </div>
                                        <div class="icon">
                                            <i class="fas fa-clock fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Filters -->
                    <form action="{{ route('admin.schedules.index') }}" method="GET" class="mb-4">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="start_date">Start Date</label>
                                    <input type="date" class="form-control" id="start_date" name="start_date" value="{{ request('start_date') }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="end_date">End Date</label>
                                    <input type="date" class="form-control" id="end_date" name="end_date" value="{{ request('end_date') }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="trainer_id">Trainer</label>
                                    <select class="form-control" id="trainer_id" name="trainer_id">
                                        <option value="">All Trainers</option>
                                        @foreach($trainers ?? [] as $trainer)
                                            <option value="{{ $trainer->id }}" {{ request('trainer_id') == $trainer->id ? 'selected' : '' }}>
                                                {{ optional($trainer->user)->name ?? 'Unnamed Trainer' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="status">Status</label>
                                    <select class="form-control" id="status" name="status">
                                        <option value="">All Statuses</option>
                                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 text-right">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-filter"></i> Filter
                                </button>
                                <a href="{{ route('admin.schedules.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Clear
                                </a>
                            </div>
                        </div>
                    </form>

                    <!-- Schedule List -->
                    <div class="list-group">
                        @foreach($schedules as $schedule)
                            <div class="list-group-item list-group-item-action">
                                <div class="d-flex w-100 justify-content-between align-items-center">
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <h5 class="mb-1">{{ $schedule->title }}</h5>
                                            <span class="badge badge-{{ $schedule->status === 'active' ? 'success' : 'danger' }}">
                                                {{ ucfirst($schedule->status) }}
                                            </span>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <p class="mb-1">
                                                    <i class="fas fa-user-tie mr-2"></i>
                                                    <strong>Trainer:</strong> {{ optional($schedule->trainer)->user->name ?? 'No Trainer' }}
                                                </p>
                                                <p class="mb-1">
                                                    <i class="fas fa-calendar mr-2"></i>
                                                    <strong>Dates:</strong> {{ optional($schedule->start_date)->format('M d, Y') ?? 'N/A' }} - 
                                                    {{ optional($schedule->end_date)->format('M d, Y') ?? 'N/A' }}
                                                </p>
                                                <p class="mb-1">
                                                    <i class="fas fa-clock mr-2"></i>
                                                    <strong>Time:</strong> {{ $schedule->start_time ? Carbon\Carbon::parse($schedule->start_time)->format('h:i A') : 'N/A' }} - 
                                                    {{ $schedule->end_time ? Carbon\Carbon::parse($schedule->end_time)->format('h:i A') : 'N/A' }}
                                                </p>
                                            </div>
                                            <div class="col-md-6">
                                                <p class="mb-1">
                                                    <i class="fas fa-users mr-2"></i>
                                                    <strong>Participants:</strong> {{ $schedule->current_participants }} / {{ $schedule->max_participants }}
                                                </p>
                                                <p class="mb-1">
                                                    <i class="fas fa-dollar-sign mr-2"></i>
                                                    <strong>Price:</strong> ${{ number_format($schedule->price, 2) }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="ml-3">
                                        <div class="btn-group">
                                            <a href="{{ route('admin.schedules.edit', $schedule) }}" class="btn btn-sm btn-info">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <form action="{{ route('admin.schedules.destroy', $schedule) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this schedule?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-4">
                        {{ $schedules->withQueryString()->links('pagination::bootstrap-4') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 