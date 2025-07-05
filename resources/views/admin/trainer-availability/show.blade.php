@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="card-title">
                            <i class="fas fa-calendar-alt me-2"></i>
                            Availability for {{ $schedule->title }}
                        </h3>
                        <div class="btn-group">
                            <a href="{{ route('admin.trainer-availability.calendar', $schedule) }}" class="btn btn-info">
                                <i class="fas fa-calendar me-1"></i> Calendar View
                            </a>
                            <a href="{{ route('admin.trainer-availability.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i> Back to List
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5>Schedule Details</h5>
                            <table class="table table-sm">
                                <tr>
                                    <td><strong>Title:</strong></td>
                                    <td>{{ $schedule->title }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Trainer:</strong></td>
                                    <td>{{ $trainer->user->name }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Category:</strong></td>
                                    <td>{{ $schedule->category->name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Status:</strong></td>
                                    <td>
                                        <span class="badge badge-{{ $schedule->status === 'active' ? 'success' : 'secondary' }}">
                                            {{ ucfirst($schedule->status) }}
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h5>Quick Actions</h5>
                            <div class="btn-group-vertical w-100">
                                <button class="btn btn-primary mb-2" data-bs-toggle="modal" data-bs-target="#bulkUpdateModal">
                                    <i class="fas fa-edit me-1"></i> Bulk Update
                                </button>
                                <button class="btn btn-success mb-2" data-bs-toggle="modal" data-bs-target="#recurringModal">
                                    <i class="fas fa-repeat me-1"></i> Create Recurring
                                </button>
                                <a href="{{ route('admin.trainer-availability.export', $schedule) }}" class="btn btn-info">
                                    <i class="fas fa-download me-1"></i> Export
                                </a>
                            </div>
                        </div>
                    </div>

                    @if($availabilities->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Date</th>
                                        <th>Time</th>
                                        <th>Status</th>
                                        <th>Notes</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($availabilities as $availability)
                                        <tr>
                                            <td>
                                                <div class="d-flex flex-column">
                                                    <strong class="text-primary">{{ $availability->date->format('M d, Y') }}</strong>
                                                    <small class="text-muted">{{ $availability->date->format('l') }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex flex-column">
                                                                                                    <span class="font-weight-bold">{{ $availability->start_time ? $availability->start_time->format('g:i A') : 'Time not set' }}</span>
                                                <span class="text-muted">to {{ $availability->end_time ? $availability->end_time->format('g:i A') : 'Time not set' }}</span>
                                                </div>
                                            </td>
                                            <td>
                                                @if($availability->status === 'available')
                                                    <span class="badge badge-success badge-pill">
                                                        <i class="fas fa-check-circle mr-1"></i>Available
                                                    </span>
                                                @elseif($availability->status === 'unavailable')
                                                    <span class="badge badge-danger badge-pill">
                                                        <i class="fas fa-times-circle mr-1"></i>Unavailable
                                                    </span>
                                                @elseif($availability->status === 'booked')
                                                    <span class="badge badge-warning badge-pill">
                                                        <i class="fas fa-bookmark mr-1"></i>Booked
                                                    </span>
                                                @else
                                                    <span class="badge badge-secondary badge-pill">
                                                        <i class="fas fa-ban mr-1"></i>Cancelled
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($availability->notes)
                                                    <span class="text-muted">{{ Str::limit($availability->notes, 50) }}</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <button class="btn btn-outline-primary edit-availability" 
                                                            data-availability="{{ $availability->id }}"
                                                            data-date="{{ $availability->date->format('Y-m-d') }}"
                                                            data-start-time="{{ $availability->start_time ? $availability->start_time->format('H:i') : '' }}"
                                                            data-end-time="{{ $availability->end_time ? $availability->end_time->format('H:i') : '' }}"
                                                            data-status="{{ $availability->status }}"
                                                            data-notes="{{ $availability->notes }}"
                                                            title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-outline-danger delete-availability" 
                                                            data-availability="{{ $availability->id }}"
                                                            title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No availability records found</h5>
                            <p class="text-muted">This schedule doesn't have any availability entries yet.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Availability Modal -->
<div class="modal fade" id="editAvailabilityModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Availability</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editAvailabilityForm">
                    <div class="mb-3">
                        <label class="form-label">Date</label>
                        <input type="date" id="editDate" name="date" class="form-control" required>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Start Time</label>
                                <input type="time" id="editStartTime" name="start_time" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">End Time</label>
                                <input type="time" id="editEndTime" name="end_time" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select id="editStatus" name="status" class="form-select" required>
                            <option value="available">Available</option>
                            <option value="unavailable">Unavailable</option>
                            <option value="booked">Booked</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea id="editNotes" name="notes" class="form-control" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="deleteAvailability">Delete</button>
                <button type="button" class="btn btn-primary" id="saveAvailability">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Update Modal -->
<div class="modal fade" id="bulkUpdateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Bulk Update Availability</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="bulkUpdateForm">
                    <div class="mb-3">
                        <label class="form-label">Select Dates</label>
                        <div class="calendar-mini" id="bulkDateSelector">
                            <!-- Mini calendar for date selection -->
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select" required>
                            <option value="available">Available</option>
                            <option value="unavailable">Unavailable</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveBulkUpdate">Update Selected Dates</button>
            </div>
        </div>
    </div>
</div>

<!-- Recurring Availability Modal -->
<div class="modal fade" id="recurringModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create Recurring Availability</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('admin.trainer-availability.create-recurring', $schedule) }}" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Start Date</label>
                                <input type="date" name="start_date" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">End Date</label>
                                <input type="date" name="end_date" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Days of Week</label>
                        <div class="row">
                            @foreach(['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'] as $index => $day)
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="days_of_week[]" value="{{ $index }}" id="day{{ $index }}">
                                        <label class="form-check-label" for="day{{ $index }}">
                                            {{ $day }}
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Start Time</label>
                                <input type="time" name="start_time" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">End Time</label>
                                <input type="time" name="end_time" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Create Recurring</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentAvailabilityId = null;
    
    // Edit availability
    document.querySelectorAll('.edit-availability').forEach(button => {
        button.addEventListener('click', function() {
            const data = this.dataset;
            currentAvailabilityId = data.availability;
            
            document.getElementById('editDate').value = data.date;
            document.getElementById('editStartTime').value = data.startTime;
            document.getElementById('editEndTime').value = data.endTime;
            document.getElementById('editStatus').value = data.status;
            document.getElementById('editNotes').value = data.notes || '';
            
            new bootstrap.Modal(document.getElementById('editAvailabilityModal')).show();
        });
    });
    
    // Save availability changes
    document.getElementById('saveAvailability').addEventListener('click', function() {
        const form = document.getElementById('editAvailabilityForm');
        const formData = new FormData(form);
        
        fetch(`/admin/trainer-availability/${currentAvailabilityId}`, {
            method: 'PUT',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(Object.fromEntries(formData))
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        });
    });
    
    // Delete availability
    document.getElementById('deleteAvailability').addEventListener('click', function() {
        if (confirm('Are you sure you want to delete this availability?')) {
            fetch(`/admin/trainer-availability/${currentAvailabilityId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                }
            });
        }
    });
    
    // Delete availability from table
    document.querySelectorAll('.delete-availability').forEach(button => {
        button.addEventListener('click', function() {
            const availabilityId = this.dataset.availability;
            
            if (confirm('Are you sure you want to delete this availability?')) {
                fetch(`/admin/trainer-availability/${availabilityId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    }
                });
            }
        });
    });
});
</script>
@endpush 