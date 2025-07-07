@extends('layouts.admin')

@section('content')
<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h4 class="mb-0">
                <i class="fas fa-tags mr-2"></i>
                Schedule Types
            </h4>
            <a href="{{ route('admin.schedule-types.create') }}" class="btn btn-primary">
                <i class="fas fa-plus mr-2"></i>Add Schedule Type
            </a>
        </div>
    </div>

    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle mr-2"></i>
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle mr-2"></i>
                {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Icon</th>
                        <th>Color</th>
                        <th>Status</th>
                        <th>Sort Order</th>
                        <th>Schedules</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($scheduleTypes as $scheduleType)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    @if($scheduleType->icon)
                                        <i class="{{ $scheduleType->icon }} mr-2" style="color: {{ $scheduleType->color }}"></i>
                                    @endif
                                    <strong>{{ $scheduleType->name }}</strong>
                                </div>
                            </td>
                            <td>
                                @if($scheduleType->description)
                                    {{ Str::limit($scheduleType->description, 50) }}
                                @else
                                    <span class="text-muted">No description</span>
                                @endif
                            </td>
                            <td>
                                @if($scheduleType->icon)
                                    <i class="{{ $scheduleType->icon }}"></i>
                                    <small class="text-muted d-block">{{ $scheduleType->icon }}</small>
                                @else
                                    <span class="text-muted">No icon</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="color-preview mr-2" style="width: 20px; height: 20px; background-color: {{ $scheduleType->color }}; border-radius: 3px;"></div>
                                    <span>{{ $scheduleType->color }}</span>
                                </div>
                            </td>
                            <td>
                                @if($scheduleType->is_active)
                                    <span class="badge badge-success">Active</span>
                                @else
                                    <span class="badge badge-secondary">Inactive</span>
                                @endif
                            </td>
                            <td>{{ $scheduleType->sort_order }}</td>
                            <td>
                                <span class="badge badge-info">{{ $scheduleType->schedules()->count() }}</span>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('admin.schedule-types.show', $scheduleType->id) }}" class="btn btn-sm btn-info" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.schedule-types.edit', $scheduleType->id) }}" class="btn btn-sm btn-warning" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.schedule-types.toggle-status', $scheduleType->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-{{ $scheduleType->is_active ? 'warning' : 'success' }}" title="{{ $scheduleType->is_active ? 'Deactivate' : 'Activate' }}">
                                            <i class="fas fa-{{ $scheduleType->is_active ? 'pause' : 'play' }}"></i>
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.schedule-types.destroy', $scheduleType->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this schedule type?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" title="Delete" {{ $scheduleType->schedules()->count() > 0 ? 'disabled' : '' }}>
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted">
                                <i class="fas fa-info-circle mr-2"></i>
                                No schedule types found. <a href="{{ route('admin.schedule-types.create') }}">Create the first one</a>.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($scheduleTypes->hasPages())
            <div class="d-flex justify-content-center">
                {{ $scheduleTypes->links() }}
            </div>
        @endif
    </div>
</div>
@endsection 