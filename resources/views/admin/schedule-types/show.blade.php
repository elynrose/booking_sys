@extends('layouts.admin')

@section('content')
<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h4 class="mb-0">
                <i class="fas fa-tag mr-2"></i>
                Schedule Type: {{ $scheduleType->name }}
            </h4>
            <div>
                <a href="{{ route('admin.schedule-types.edit', $scheduleType->id) }}" class="btn btn-warning">
                    <i class="fas fa-edit mr-2"></i>Edit
                </a>
                <a href="{{ route('admin.schedule-types.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left mr-2"></i>Back
                </a>
            </div>
        </div>
    </div>

    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <h5>Details</h5>
                <table class="table table-borderless">
                    <tr>
                        <td><strong>Name:</strong></td>
                        <td>
                            @if($scheduleType->icon)
                                <i class="{{ $scheduleType->icon }} mr-2" style="color: {{ $scheduleType->color }}"></i>
                            @endif
                            {{ $scheduleType->name }}
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Slug:</strong></td>
                        <td><code>{{ $scheduleType->slug }}</code></td>
                    </tr>
                    <tr>
                        <td><strong>Description:</strong></td>
                        <td>
                            @if($scheduleType->description)
                                {{ $scheduleType->description }}
                            @else
                                <span class="text-muted">No description</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Icon:</strong></td>
                        <td>
                            @if($scheduleType->icon)
                                <i class="{{ $scheduleType->icon }}"></i>
                                <code>{{ $scheduleType->icon }}</code>
                            @else
                                <span class="text-muted">No icon</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Color:</strong></td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="color-preview mr-2" style="width: 20px; height: 20px; background-color: {{ $scheduleType->color }}; border-radius: 3px;"></div>
                                <span>{{ $scheduleType->color }}</span>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Status:</strong></td>
                        <td>
                            @if($scheduleType->is_active)
                                <span class="badge badge-success">Active</span>
                            @else
                                <span class="badge badge-secondary">Inactive</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Sort Order:</strong></td>
                        <td>{{ $scheduleType->sort_order }}</td>
                    </tr>
                    <tr>
                        <td><strong>Created:</strong></td>
                        <td>{{ $scheduleType->created_at->format('M d, Y H:i') }}</td>
                    </tr>
                    <tr>
                        <td><strong>Updated:</strong></td>
                        <td>{{ $scheduleType->updated_at->format('M d, Y H:i') }}</td>
                    </tr>
                </table>
            </div>

            <div class="col-md-6">
                <h5>Statistics</h5>
                <div class="row">
                    <div class="col-6">
                        <div class="card bg-primary text-white">
                            <div class="card-body text-center">
                                <h3>{{ $scheduleType->schedules()->count() }}</h3>
                                <p class="mb-0">Total Schedules</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="card bg-success text-white">
                            <div class="card-body text-center">
                                <h3>{{ $scheduleType->schedules()->where('status', 'active')->count() }}</h3>
                                <p class="mb-0">Active Schedules</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <hr>

        <h5>Schedules Using This Type</h5>
        @if($schedules->count() > 0)
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Trainer</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($schedules as $schedule)
                            <tr>
                                <td>
                                    <strong>{{ $schedule->title }}</strong>
                                    @if($schedule->is_featured)
                                        <span class="badge badge-warning">Featured</span>
                                    @endif
                                </td>
                                <td>
                                    @if($schedule->trainer && $schedule->trainer->user)
                                        {{ $schedule->trainer->user->name }}
                                    @else
                                        <span class="text-muted">No trainer</span>
                                    @endif
                                </td>
                                <td>
                                    @if($schedule->start_date)
                                        {{ $schedule->start_date->format('M d, Y') }}
                                    @else
                                        <span class="text-muted">No date</span>
                                    @endif
                                </td>
                                <td>
                                    @if($schedule->start_time && $schedule->end_time)
                                        {{ \Carbon\Carbon::parse($schedule->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($schedule->end_time)->format('H:i') }}
                                    @else
                                        <span class="text-muted">No time</span>
                                    @endif
                                </td>
                                <td>
                                    @if($schedule->status === 'active')
                                        <span class="badge badge-success">Active</span>
                                    @else
                                        <span class="badge badge-secondary">{{ ucfirst($schedule->status) }}</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.schedules.show', $schedule->id) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.schedules.edit', $schedule->id) }}" class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($schedules->hasPages())
                <div class="d-flex justify-content-center">
                    {{ $schedules->links() }}
                </div>
            @endif
        @else
            <div class="text-center text-muted">
                <i class="fas fa-info-circle mr-2"></i>
                No schedules found using this type.
            </div>
        @endif
    </div>
</div>
@endsection 