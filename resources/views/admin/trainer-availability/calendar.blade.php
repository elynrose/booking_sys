@extends('layouts.admin')

@section('styles')
<style>
.calendar-container {
    width: 100%;
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
    overflow: hidden;
    background: #fff;
}
.calendar-header {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    background: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
}
.calendar-day-header {
    padding: 12px 8px;
    text-align: center;
    font-weight: 600;
    color: #495057;
    font-size: 0.875rem;
    border-right: 1px solid #dee2e6;
}
.calendar-day-header:last-child { border-right: none; }
.calendar-grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    background: #fff;
}
.calendar-day {
    min-height: 100px;
    padding: 8px;
    border-right: 1px solid #dee2e6;
    border-bottom: 1px solid #dee2e6;
    background: #fff;
    position: relative;
    transition: background 0.2s;
}
.calendar-day:nth-child(7n) { border-right: none; }
.calendar-day.other-month { background: #f8f9fa; color: #6c757d; }
.calendar-day.today { background: #e3f2fd; border: 2px solid #2196f3; }
.calendar-day-number { font-size: 14px; font-weight: 500; margin-bottom: 8px; }
.availability-info { margin-top: 4px; }
.availability-info small { font-size: 0.75rem; color: #6c757d; }
.availability-info .badge { font-size: 0.7rem; padding: 0.25rem 0.5rem; }
.calendar-day.cancelled {
    background-color: #e2e3e5;
    border-color: #d6d8db;
}
.calendar-day.selected {
    background-color: #007bff !important;
    color: white !important;
    border: 2px solid #0056b3 !important;
    transform: scale(1.05);
}

/* Availability Badge Styles */
.availability-badge {
    width: 24px;
    height: 24px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    transition: transform 0.2s ease;
}

.availability-badge:hover {
    transform: scale(1.1);
}

.availability-badge.available {
    background-color: #28a745;
    color: white;
}

.availability-badge.unavailable {
    background-color: #dc3545;
    color: white;
}

.availability-badge.booked {
    background-color: #ffc107;
    color: #212529;
}

.availability-badge.cancelled {
    background-color: #6c757d;
    color: white;
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .calendar-day { min-height: 60px; padding: 4px; }
    .calendar-day-header { padding: 8px 4px; font-size: 0.75rem; }
    .availability-badge {
        width: 20px;
        height: 20px;
        font-size: 10px;
    }
}

@media (max-width: 576px) {
    .calendar-day { min-height: 50px; padding: 2px; }
    .calendar-day-header { padding: 6px 2px; font-size: 0.7rem; }
    .availability-badge {
        width: 16px;
        height: 16px;
        font-size: 8px;
    }
}
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">
                        <i class="fas fa-calendar-alt me-2"></i>
                        Availability Calendar - {{ $schedule->title }}
                    </h4>
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.trainer-availability.show', $schedule) }}" class="btn btn-secondary">
                            <i class="fas fa-list me-1"></i> List View
                        </a>
                        <a href="{{ route('admin.trainer-availability.export', $schedule) }}" class="btn btn-outline-primary">
                            <i class="fas fa-download me-1"></i> Export
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Debug Information -->
                    <div class="alert alert-info mb-3">
                        <strong>Debug Info:</strong><br>
                        Current Month: {{ $startOfMonth->format('F Y') }}<br>
                        Available Dates: 
                        @foreach($availabilities as $date => $availability)
                            {{ $date }} ({{ $availability->status }})
                            @if($availability->notes)
                                - Notes: "{{ $availability->notes }}"
                            @endif
                            @if(!$loop->last), @endif
                        @endforeach<br>
                        Total Availabilities: {{ $availabilities->count() }}
                    </div>

                    <!-- Calendar Navigation -->
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="d-flex gap-2">
                            <a href="?month={{ Carbon\Carbon::parse($month)->subMonth()->format('Y-m') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-chevron-left"></i> Previous
                            </a>
                            <h5 class="mb-0">{{ $startOfMonth->format('F Y') }}</h5>
                            <a href="?month={{ Carbon\Carbon::parse($month)->addMonth()->format('Y-m') }}" class="btn btn-outline-secondary">
                                Next <i class="fas fa-chevron-right"></i>
                            </a>
                        </div>
                        <div class="d-flex gap-2">
                            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#recurringModal">
                                <i class="fas fa-repeat me-1"></i> Set Recurring
                            </button>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#bulkUpdateModal">
                                <i class="fas fa-edit me-1"></i> Bulk Update
                            </button>
                        </div>
                    </div>

                    <!-- Calendar Grid -->
                    <div class="calendar-container">
                        <!-- Day Headers -->
                        <div class="calendar-header">
                            <div class="calendar-day-header">Sun</div>
                            <div class="calendar-day-header">Mon</div>
                            <div class="calendar-day-header">Tue</div>
                            <div class="calendar-day-header">Wed</div>
                            <div class="calendar-day-header">Thu</div>
                            <div class="calendar-day-header">Fri</div>
                            <div class="calendar-day-header">Sat</div>
                        </div>

                        <!-- Calendar Days -->
                        <div class="calendar-grid">
                            @php
                                $firstDayOfMonth = $startOfMonth->copy()->startOfMonth();
                                $lastDayOfMonth = $startOfMonth->copy()->endOfMonth();
                                $firstDayOfWeek = $firstDayOfMonth->copy()->startOfWeek();
                                $lastDayOfWeek = $lastDayOfMonth->copy()->endOfWeek();
                                $currentDate = $firstDayOfWeek->copy();
                            @endphp

                            @while($currentDate <= $lastDayOfWeek)
                                @php
                                    $isCurrentMonth = $currentDate->month === $startOfMonth->month;
                                    $isToday = $currentDate->isToday();
                                    $availability = $availabilities->get($currentDate->format('Y-m-d'));
                                    $statusClass = $availability ? "availability-{$availability->status}" : '';
                                    $isPast = $currentDate->isPast();
                                @endphp

                                <div class="calendar-day {{ $isCurrentMonth ? '' : 'other-month' }} {{ $isToday ? 'today' : '' }} {{ $statusClass }} {{ $isPast ? 'past' : '' }}"
                                     data-date="{{ $currentDate->format('Y-m-d') }}"
                                     data-availability-id="{{ $availability ? $availability->id : '' }}"
                                     data-status="{{ $availability ? $availability->status : '' }}"
                                     data-start-time="{{ $availability ? $availability->start_time->format('H:i') : '' }}"
                                     data-end-time="{{ $availability ? $availability->end_time->format('H:i') : '' }}"
                                     data-notes="{{ $availability ? $availability->notes : '' }}">
                                    
                                    <div class="calendar-day-number">{{ $currentDate->day }}</div>
                                    
                                    @if($availability)
                                        <div class="availability-info">
                                            <small class="d-block">{{ $availability->start_time->format('H:i') }} - {{ $availability->end_time->format('H:i') }}</small>
                                            <span class="badge bg-{{ $availability->status === 'available' ? 'success' : ($availability->status === 'unavailable' ? 'danger' : ($availability->status === 'booked' ? 'warning' : 'secondary')) }}">
                                                {{ ucfirst($availability->status) }}
                                            </span>
                                        </div>
                                    @else
                                        @if($isCurrentMonth && !$isPast)
                                            <div class="availability-info">
                                                <small class="text-muted">No availability</small>
                                            </div>
                                        @endif
                                    @endif
                                </div>

                                @php
                                    $currentDate->addDay();
                                @endphp
                            @endwhile
                        </div>
                    </div>

                    <!-- Legend -->
                    <div class="card mt-4">
                        <div class="card-header">
                            <h6 class="mb-0">Legend</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3 mb-2">
                                    <div class="d-flex align-items-center">
                                        <div class="availability-badge available mr-2">
                                            <i class="fas fa-check-circle"></i>
                                        </div>
                                        <span>Available</span>
                                    </div>
                                </div>
                                <div class="col-md-3 mb-2">
                                    <div class="d-flex align-items-center">
                                        <div class="availability-badge unavailable mr-2">
                                            <i class="fas fa-times-circle"></i>
                                        </div>
                                        <span>Unavailable</span>
                                    </div>
                                </div>
                                <div class="col-md-3 mb-2">
                                    <div class="d-flex align-items-center">
                                        <div class="availability-badge booked mr-2">
                                            <i class="fas fa-clock"></i>
                                        </div>
                                        <span>Booked</span>
                                    </div>
                                </div>
                                <div class="col-md-3 mb-2">
                                    <div class="d-flex align-items-center">
                                        <div class="availability-badge cancelled mr-2">
                                            <i class="fas fa-ban"></i>
                                        </div>
                                        <span>Cancelled</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Individual Day Edit Modal -->
<div class="modal fade" id="dayEditModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Availability</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="dayEditForm">
                    <input type="hidden" id="editDate" name="date">
                    <input type="hidden" id="editAvailabilityId" name="availability_id">
                    
                    <div class="mb-3">
                        <label class="form-label">Date</label>
                        <input type="text" id="editDateDisplay" class="form-control" readonly>
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
                <h5 class="modal-title">Set Recurring Availability</h5>
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
                        <button type="submit" class="btn btn-primary">Create Recurring Availability</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle calendar day clicks
    document.querySelectorAll('.calendar-day').forEach(function(day) {
        day.addEventListener('click', function() {
            const date = this.dataset.date;
            const availabilityId = this.dataset.availabilityId;
            const status = this.dataset.status;
            const startTime = this.dataset.startTime;
            const endTime = this.dataset.endTime;
            const notes = this.dataset.notes;
            
            // Populate modal
            document.getElementById('editDate').value = date;
            
            // Fix date display - parse the date properly to avoid timezone issues
            const [year, month, day] = date.split('-');
            const displayDate = new Date(year, month - 1, day); // month is 0-indexed
            document.getElementById('editDateDisplay').value = displayDate.toLocaleDateString();
            
            document.getElementById('editAvailabilityId').value = availabilityId || '';
            document.getElementById('editStartTime').value = startTime || '09:00';
            document.getElementById('editEndTime').value = endTime || '10:00';
            
            // Set status - if empty or 'none', default to 'available'
            const statusValue = (status && status !== 'none') ? status : 'available';
            document.getElementById('editStatus').value = statusValue;
            document.getElementById('editNotes').value = notes || '';
            
            // Show modal
            new bootstrap.Modal(document.getElementById('dayEditModal')).show();
        });
    });
    
    // Handle save availability
    document.getElementById('saveAvailability').addEventListener('click', function() {
        const availabilityId = document.getElementById('editAvailabilityId').value;
        const date = document.getElementById('editDate').value;
        const status = document.getElementById('editStatus').value;
        const startTime = document.getElementById('editStartTime').value;
        const endTime = document.getElementById('editEndTime').value;
        const notes = document.getElementById('editNotes').value;
        
        // Validate time fields
        if (!startTime || !endTime) {
            alert('Please enter both start and end times');
            return;
        }
        
        let url, method, data;
        
        if (availabilityId) {
            // Update existing availability
            url = `/admin/trainer-availability/${availabilityId}`;
            method = 'PUT';
            data = {
                status: status,
                start_time: startTime,
                end_time: endTime,
                notes: notes
            };
        } else {
            // Create new availability
            url = '{{ route("admin.trainer-availability.store", $schedule) }}';
            method = 'POST';
            data = {
                dates: [date],
                start_time: startTime,
                end_time: endTime,
                status: status,
                notes: notes
            };
        }
        
        fetch(url, {
            method: method,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                setTimeout(() => {
                    location.reload();
                }, 5000);
            } else {
                alert('Error saving availability');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error saving availability: ' + error.message);
        });
    });
    
    // Handle bulk update
    document.getElementById('saveBulkUpdate').addEventListener('click', function() {
        const selectedDates = [];
        const status = document.querySelector('#bulkUpdateForm select[name="status"]').value;
        
        // Get selected dates from the mini calendar or allow manual selection
        // For now, we'll use a simple approach - let users select dates from the main calendar
        const selectedDays = document.querySelectorAll('.calendar-day.selected');
        selectedDays.forEach(day => {
            selectedDates.push(day.dataset.date);
        });
        
        if (selectedDates.length === 0) {
            alert('Please select at least one date to update');
            return;
        }
        
        const formData = {
            dates: selectedDates,
            status: status,
            _token: document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        };
        
        fetch('{{ route("admin.trainer-availability.bulk-update", $schedule) }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(formData)
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
    
    // Handle delete availability
    document.getElementById('deleteAvailability').addEventListener('click', function() {
        const availabilityId = document.getElementById('editAvailabilityId').value;
        
        if (!availabilityId) {
            alert('No availability to delete. This day has no existing availability.');
            return;
        }
        
        if (confirm('Are you sure you want to delete this availability?')) {
            fetch(`{{ route("admin.trainer-availability.destroy", ":availability") }}`.replace(':availability', availabilityId), {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error deleting availability');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error deleting availability');
            });
        }
    });
    
    // Handle modal close buttons
    document.querySelectorAll('[data-bs-dismiss="modal"]').forEach(function(button) {
        button.addEventListener('click', function() {
            const modal = this.closest('.modal');
            if (modal) {
                // Simple approach - just hide the modal
                modal.classList.remove('show');
                modal.style.display = 'none';
                document.body.classList.remove('modal-open');
                const backdrop = document.querySelector('.modal-backdrop');
                if (backdrop) {
                    backdrop.remove();
                }
            }
        });
    });
    
    // Add date selection functionality for bulk update
    let isSelectingDates = false;
    let selectedDates = [];
    
    // Handle bulk update modal open
    document.querySelector('[data-bs-target="#bulkUpdateModal"]').addEventListener('click', function() {
        isSelectingDates = true;
        selectedDates = [];
        document.querySelectorAll('.calendar-day').forEach(day => {
            day.classList.remove('selected');
        });
        alert('Click on calendar dates to select them, then click "Update Selected Dates"');
    });
    
    // Handle date selection for bulk update
    document.querySelectorAll('.calendar-day').forEach(function(day) {
        day.addEventListener('click', function() {
            if (isSelectingDates && !this.classList.contains('past')) {
                const date = this.dataset.date;
                if (this.classList.contains('selected')) {
                    this.classList.remove('selected');
                    selectedDates = selectedDates.filter(d => d !== date);
                } else {
                    this.classList.add('selected');
                    selectedDates.push(date);
                }
            }
        });
    });
    
    // Handle bulk update modal close
    document.getElementById('bulkUpdateModal').addEventListener('hidden.bs.modal', function() {
        isSelectingDates = false;
        selectedDates = [];
        document.querySelectorAll('.calendar-day').forEach(day => {
            day.classList.remove('selected');
        });
    });
});
</script>
@endsection 