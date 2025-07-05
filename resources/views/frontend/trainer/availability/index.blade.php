@extends('layouts.frontend')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">
                    <i class="fas fa-calendar-alt text-primary"></i>
                    My Availability
                </h2>
                <div class="btn-group" role="group">
                    <a href="{{ route('frontend.trainer.availability.create') }}" class="btn btn-success">
                        <i class="fas fa-plus"></i> Add Availability
                    </a>
                    <a href="{{ route('frontend.trainer.availability.calendar') }}" class="btn btn-info">
                        <i class="fas fa-calendar"></i> Calendar View
                    </a>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="fas fa-tools"></i> Quick Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <button type="button" class="btn btn-outline-primary btn-block" id="bulkUpdateBtn" data-toggle="modal" data-target="#bulkUpdateModal">
                                <i class="fas fa-edit"></i> Bulk Update
                            </button>
                        </div>
                        <div class="col-md-6 mb-2">
                            <button type="button" class="btn btn-outline-success btn-block" id="recurringBtn" data-toggle="modal" data-target="#recurringModal">
                                <i class="fas fa-redo"></i> Create Recurring
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Availability List -->
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">My Availability Schedule</h5>
                </div>
                <div class="card-body">
                    @if($availabilities->isEmpty())
                        <div class="text-center py-5">
                            <i class="fas fa-calendar-times fa-4x text-muted mb-4"></i>
                            <h4 class="text-muted mb-3">No availability set</h4>
                            <p class="text-muted mb-4">Start by adding your availability to let students know when you're available.</p>
                            <a href="{{ route('frontend.trainer.availability.create') }}" class="btn btn-primary btn-lg">
                                <i class="fas fa-plus"></i> Add First Availability
                            </a>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Date</th>
                                        <th>Time</th>
                                        <th>Status</th>
                                        <th>Schedule</th>
                                        <th>Notes</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($availabilities as $availability)
                                        <tr>
                                            <td>
                                                <div class="d-flex flex-column">
                                                    <strong class="text-primary">{{ \Carbon\Carbon::parse($availability->date)->format('M d, Y') }}</strong>
                                                    <small class="text-muted">{{ \Carbon\Carbon::parse($availability->date)->format('l') }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex flex-column">
                                                    <span class="font-weight-bold">{{ \Carbon\Carbon::parse($availability->start_time)->format('g:i A') }}</span>
                                                    <span class="text-muted">to {{ \Carbon\Carbon::parse($availability->end_time)->format('g:i A') }}</span>
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
                                                @else
                                                    <span class="badge badge-warning badge-pill">
                                                        <i class="fas fa-clock mr-1"></i>Busy
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($availability->schedule)
                                                    <span class="badge badge-info">{{ $availability->schedule->title }}</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($availability->notes)
                                                    <span class="text-muted" title="{{ $availability->notes }}">{{ Str::limit($availability->notes, 30) }}</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('frontend.trainer.availability.edit', $availability) }}" 
                                                       class="btn btn-sm btn-outline-primary" 
                                                       title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form action="{{ route('frontend.trainer.availability.destroy', $availability) }}" 
                                                          method="POST" 
                                                          class="d-inline"
                                                          onsubmit="return confirm('Are you sure you want to delete this availability?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" 
                                                                class="btn btn-sm btn-outline-danger" 
                                                                title="Delete">
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

                        <!-- Pagination -->
                        <div class="d-flex justify-content-center mt-4">
                            {{ $availabilities->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Update Modal -->
<div class="modal fade" id="bulkUpdateModal" tabindex="-1" role="dialog" aria-labelledby="bulkUpdateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form action="{{ route('frontend.trainer.availability.bulk-update') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="bulkUpdateModalLabel">
                        <i class="fas fa-edit"></i> Bulk Update Availability
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="bulk_start_date">Start Date</label>
                                <input type="date" class="form-control" id="bulk_start_date" name="start_date" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="bulk_end_date">End Date</label>
                                <input type="date" class="form-control" id="bulk_end_date" name="end_date" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="bulk_start_time">Start Time</label>
                                <input type="time" class="form-control" id="bulk_start_time" name="start_time" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="bulk_end_time">End Time</label>
                                <input type="time" class="form-control" id="bulk_end_time" name="end_time" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="bulk_status">Status</label>
                                <select class="form-control" id="bulk_status" name="status" required>
                                    <option value="available">Available</option>
                                    <option value="unavailable">Unavailable</option>
                                    <option value="busy">Busy</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="bulk_schedule_id">Schedule (Optional)</label>
                                <select class="form-control" id="bulk_schedule_id" name="schedule_id">
                                    <option value="">No specific schedule</option>
                                    @foreach($trainer->schedules as $schedule)
                                        <option value="{{ $schedule->id }}">{{ $schedule->title }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="bulk_notes">Notes (Optional)</label>
                        <textarea class="form-control" id="bulk_notes" name="notes" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Select Dates</label>
                        <div id="date-picker" class="border p-3 bg-light" style="max-height: 200px; overflow-y: auto;">
                            <!-- Dates will be populated by JavaScript -->
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Availability
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Recurring Availability Modal -->
<div class="modal fade" id="recurringModal" tabindex="-1" role="dialog" aria-labelledby="recurringModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form action="{{ route('frontend.trainer.availability.create-recurring') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="recurringModalLabel">
                        <i class="fas fa-redo"></i> Create Recurring Availability
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="recurring_start_date">Start Date</label>
                                <input type="date" class="form-control" id="recurring_start_date" name="start_date" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="recurring_end_date">End Date</label>
                                <input type="date" class="form-control" id="recurring_end_date" name="end_date" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="recurring_start_time">Start Time</label>
                                <input type="time" class="form-control" id="recurring_start_time" name="start_time" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="recurring_end_time">End Time</label>
                                <input type="time" class="form-control" id="recurring_end_time" name="end_time" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="recurring_status">Status</label>
                                <select class="form-control" id="recurring_status" name="status" required>
                                    <option value="available">Available</option>
                                    <option value="unavailable">Unavailable</option>
                                    <option value="busy">Busy</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="recurring_schedule_id">Schedule (Optional)</label>
                                <select class="form-control" id="recurring_schedule_id" name="schedule_id">
                                    <option value="">No specific schedule</option>
                                    @foreach($trainer->schedules as $schedule)
                                        <option value="{{ $schedule->id }}">{{ $schedule->title }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="recurring_notes">Notes (Optional)</label>
                        <textarea class="form-control" id="recurring_notes" name="notes" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Days of Week</label>
                        <div class="row">
                            @foreach(['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'] as $index => $day)
                                <div class="col-md-4 mb-2">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="day_{{ $index }}" name="days_of_week[]" value="{{ $index }}">
                                        <label class="custom-control-label" for="day_{{ $index }}">{{ $day }}</label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> Create Recurring
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, initializing trainer availability page...');
    
    // Debug: Check if modal elements exist
    const bulkModal = document.getElementById('bulkUpdateModal');
    const recurringModal = document.getElementById('recurringModal');
    const bulkBtn = document.getElementById('bulkUpdateBtn');
    const recurringBtn = document.getElementById('recurringBtn');
    
    console.log('Modal elements:', {
        bulkModal: bulkModal,
        recurringModal: recurringModal,
        bulkBtn: bulkBtn,
        recurringBtn: recurringBtn
    });

    // Initialize Bootstrap modals manually
    if (bulkModal) {
        $('#bulkUpdateModal').modal({
            show: false
        });
    }
    
    if (recurringModal) {
        $('#recurringModal').modal({
            show: false
        });
    }

    // Add click handlers for modal triggers
    if (bulkBtn) {
        bulkBtn.addEventListener('click', function() {
            console.log('Bulk update button clicked');
            try {
                $('#bulkUpdateModal').modal('show');
            } catch (error) {
                console.error('Error showing bulk modal:', error);
                // Fallback: manually show modal
                if (bulkModal) {
                    bulkModal.style.display = 'block';
                    bulkModal.classList.add('show');
                }
            }
        });
    }

    if (recurringBtn) {
        recurringBtn.addEventListener('click', function() {
            console.log('Recurring button clicked');
            try {
                $('#recurringModal').modal('show');
            } catch (error) {
                console.error('Error showing recurring modal:', error);
                // Fallback: manually show modal
                if (recurringModal) {
                    recurringModal.style.display = 'block';
                    recurringModal.classList.add('show');
                }
            }
        });
    }

    // Handle bulk update form submission
    const bulkUpdateForm = document.getElementById('bulkUpdateForm');
    if (bulkUpdateForm) {
        bulkUpdateForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const selectedDates = [];
            
            // Get selected checkboxes
            const checkboxes = document.querySelectorAll('input[name="dates[]"]:checked');
            checkboxes.forEach(checkbox => {
                selectedDates.push(checkbox.value);
            });
            
            if (selectedDates.length === 0) {
                alert('Please select at least one date to update');
                return;
            }
            
            // Add selected dates to form data
            selectedDates.forEach(date => {
                formData.append('dates[]', date);
            });
            
            // Submit form
            fetch('{{ route("frontend.trainer.availability.bulk-update") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error updating availability');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error updating availability');
            });
        });
    }

    // Handle recurring form submission
    const recurringForm = document.getElementById('recurringForm');
    if (recurringForm) {
        recurringForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('{{ route("frontend.trainer.availability.create-recurring") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error creating recurring availability');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error creating recurring availability');
            });
        });
    }

    // Set default dates for bulk update modal
    const today = new Date();
    const nextMonth = new Date(today.getFullYear(), today.getMonth() + 1, 1);
    
    const bulkStartDate = document.getElementById('bulk_start_date');
    const bulkEndDate = document.getElementById('bulk_end_date');
    const recurringStartDate = document.getElementById('recurring_start_date');
    const recurringEndDate = document.getElementById('recurring_end_date');
    
    if (bulkStartDate) {
        bulkStartDate.value = today.toISOString().split('T')[0];
    }
    if (bulkEndDate) {
        bulkEndDate.value = nextMonth.toISOString().split('T')[0];
    }
    if (recurringStartDate) {
        recurringStartDate.value = today.toISOString().split('T')[0];
    }
    if (recurringEndDate) {
        recurringEndDate.value = nextMonth.toISOString().split('T')[0];
    }
    
    // Generate date checkboxes for bulk update
    function generateDateCheckboxes() {
        const startDate = new Date(bulkStartDate ? bulkStartDate.value : today);
        const endDate = new Date(bulkEndDate ? bulkEndDate.value : nextMonth);
        const container = document.getElementById('date-picker');
        
        if (!container) {
            console.error('Date picker container not found');
            return;
        }
        
        container.innerHTML = '';
        
        const currentDate = new Date(startDate);
        while (currentDate <= endDate) {
            const dateStr = currentDate.toISOString().split('T')[0];
            const dayName = currentDate.toLocaleDateString('en-US', { weekday: 'short' });
            const monthDay = currentDate.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
            
            const div = document.createElement('div');
            div.className = 'custom-control custom-checkbox d-inline-block mr-3 mb-2';
            div.innerHTML = `
                <input type="checkbox" class="custom-control-input" id="date_${dateStr}" name="dates[]" value="${dateStr}" checked>
                <label class="custom-control-label" for="date_${dateStr}">${dayName} ${monthDay}</label>
            `;
            container.appendChild(div);
            
            currentDate.setDate(currentDate.getDate() + 1);
        }
    }
    
    // Generate checkboxes when dates change
    if (bulkStartDate) {
        bulkStartDate.addEventListener('change', generateDateCheckboxes);
    }
    if (bulkEndDate) {
        bulkEndDate.addEventListener('change', generateDateCheckboxes);
    }
    
    // Generate initial checkboxes
    generateDateCheckboxes();

    // Add form validation
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function(e) {
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.classList.add('is-invalid');
                } else {
                    field.classList.remove('is-invalid');
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                alert('Please fill in all required fields.');
            }
        });
    });

    // Add modal close handlers
    document.querySelectorAll('[data-dismiss="modal"]').forEach(button => {
        button.addEventListener('click', function() {
            const modal = this.closest('.modal');
            if (modal) {
                try {
                    $(modal).modal('hide');
                } catch (error) {
                    // Fallback: hide modal manually
                    modal.style.display = 'none';
                    modal.classList.remove('show');
                    document.body.classList.remove('modal-open');
                }
            }
        });
    });

    console.log('Trainer availability page initialized successfully');
});
</script>

<style>
@media (max-width: 768px) {
    .d-flex.justify-content-between {
        flex-direction: column;
        gap: 1rem;
    }
    
    .btn-group {
        flex-direction: column;
        width: 100%;
    }
    
    .btn-group .btn {
        margin-bottom: 0.5rem;
    }
    
    .table-responsive {
        font-size: 0.875rem;
    }
    
    .badge {
        font-size: 0.75rem;
    }
    
    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
    }
}

@media (max-width: 576px) {
    .table-responsive {
        font-size: 0.8rem;
    }
    
    .btn-group .btn {
        font-size: 0.8rem;
        padding: 0.375rem 0.75rem;
    }
}
</style>
@endsection 