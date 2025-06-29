@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="card-title">{{ __('app.schedules.title') }}</h3>
        <a href="{{ route('admin.schedules.create') }}" class="btn btn-primary">{{ __('app.actions.create') }} {{ __('app.schedules.title') }}</a>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">{{ __('app.schedules.total_schedules') }}</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalSchedules ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">{{ __('app.schedules.status.active') }}</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $activeSchedules ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">{{ __('app.schedules.status.inactive') }}</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $inactiveSchedules ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-pause-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">{{ __('app.schedules.upcoming_schedules') }}</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $upcomingSchedules ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.schedules.index') }}" class="row">
                <div class="col-md-3">
                    <label for="start_date">{{ __('app.dashboard.start_date') }}</label>
                    <input type="date" class="form-control" id="start_date" name="start_date" value="{{ request('start_date') }}">
                </div>
                <div class="col-md-3">
                    <label for="end_date">{{ __('app.dashboard.end_date') }}</label>
                    <input type="date" class="form-control" id="end_date" name="end_date" value="{{ request('end_date') }}">
                </div>
                <div class="col-md-2">
                    <label for="trainer_id">{{ __('app.dashboard.trainer') }}</label>
                    <select class="form-control" id="trainer_id" name="trainer_id">
                        <option value="">{{ __('app.schedules.all_categories') }}</option>
                        @foreach($trainers as $trainer)
                            <option value="{{ $trainer->id }}" {{ request('trainer_id') == $trainer->id ? 'selected' : '' }}>
                                {{ $trainer->user->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="status">{{ __('app.dashboard.status') }}</label>
                    <select class="form-control" id="status" name="status">
                        <option value="">{{ __('app.schedules.all_types') }}</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>{{ __('app.schedules.status.active') }}</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>{{ __('app.schedules.status.inactive') }}</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="type">{{ __('app.schedules.class_type') }}</label>
                    <select class="form-control" id="type" name="type">
                        <option value="">{{ __('app.schedules.all_types') }}</option>
                        <option value="group" {{ request('type') == 'group' ? 'selected' : '' }}>{{ __('app.schedules.group_classes') }}</option>
                        <option value="private" {{ request('type') == 'private' ? 'selected' : '' }}>{{ __('app.schedules.private_individual_training') }}</option>
                    </select>
                </div>
            </form>
        </div>
    </div>

    <!-- Schedules List -->
    <div class="row">
        @forelse($schedules as $schedule)
            <div class="col-lg-6 col-xl-4 mb-4">
                <div class="card shadow h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h5 class="mb-1">{{ $schedule->title }}</h5>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-toggle="dropdown">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item" href="{{ route('admin.schedules.show', $schedule->id) }}">
                                        <i class="fas fa-eye"></i> {{ __('app.actions.view') }}
                                    </a>
                                    <a class="dropdown-item" href="{{ route('admin.schedules.edit', $schedule->id) }}">
                                        <i class="fas fa-edit"></i> {{ __('app.actions.edit') }}
                                    </a>
                                    <form action="{{ route('admin.schedules.destroy', $schedule->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="dropdown-item text-danger" onclick="return confirm('{{ __('app.alerts.confirm_delete') }}')">
                                            <i class="fas fa-trash"></i> {{ __('app.actions.delete') }}
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <p class="text-muted small mb-3">{{ Str::limit($schedule->description, 100) }}</p>

                        <div class="row text-muted small">
                            <div class="col-6">
                                <strong>{{ __('app.dashboard.trainer') }}:</strong> {{ optional($schedule->trainer)->user->name ?? __('app.schedules.no_trainer') }}
                            </div>
                            <div class="col-6">
                                <strong>{{ __('app.dashboard.category') }}:</strong> {{ optional($schedule->category)->name ?? __('app.dashboard.uncategorized') }}
                            </div>
                        </div>

                        <div class="row text-muted small">
                            <div class="col-6">
                                <strong>{{ __('app.schedules.dates') }}:</strong> {{ optional($schedule->start_date)->format('M d, Y') ?? __('app.status.n_a') }} -
                                {{ optional($schedule->end_date)->format('M d, Y') ?? __('app.status.n_a') }}
                            </div>
                            <div class="col-6">
                                <strong>{{ __('app.schedules.time_range') }}:</strong> {{ optional($schedule->start_time)->format('h:i A') ?? __('app.status.n_a') }} -
                                {{ optional($schedule->end_time)->format('h:i A') ?? __('app.status.n_a') }}
                            </div>
                        </div>

                        <div class="row text-muted small">
                            <div class="col-6">
                                <strong>{{ __('app.schedules.participants') }}:</strong> {{ $schedule->current_participants }} / {{ $schedule->max_participants }}
                            </div>
                            <div class="col-6">
                                <strong>{{ __('app.dashboard.price') }}:</strong> ${{ number_format($schedule->price, 2) }}
                            </div>
                        </div>

                        <div class="mt-3">
                            <span class="badge badge-{{ $schedule->status == 'active' ? 'success' : 'warning' }}">
                                {{ __('app.schedules.status.' . $schedule->status) }}
                            </span>
                            <span class="badge badge-info">{{ __('app.schedules.class_type.' . $schedule->type) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center">
                        <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">{{ __('app.alerts.no_records_found') }}</h5>
                        <p class="text-muted">{{ __('app.schedules.title') }}</p>
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="d-flex justify-content-center">
        {{ $schedules->links() }}
    </div>
</div>
@endsection 