@extends('layouts.admin')

@section('content')
<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h4 class="mb-0">
                <i class="fas fa-calendar-alt mr-2"></i>
                Trainer Calendar - {{ $currentDate->format('F Y') }}
            </h4>
            <div class="btn-group" role="group">
                <a href="{{ route('admin.trainer-calendar.index', ['year' => $previousMonth->year, 'month' => $previousMonth->month]) }}" 
                   class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-chevron-left"></i> Previous
                </a>
                <a href="{{ route('admin.trainer-calendar.index') }}" 
                   class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-calendar-day"></i> Today
                </a>
                <a href="{{ route('admin.trainer-calendar.index', ['year' => $nextMonth->year, 'month' => $nextMonth->month]) }}" 
                   class="btn btn-outline-secondary btn-sm">
                    Next <i class="fas fa-chevron-right"></i>
                </a>
            </div>
        </div>
    </div>
    
    <div class="card-body">
        <!-- Calendar Legend -->
        <div class="mb-3">
            <div class="row">
                <div class="col-md-6">
                    <h6 class="text-muted mb-2">Legend:</h6>
                    <div class="d-flex flex-wrap">
                        <span class="badge badge-success mr-2 mb-1">
                            <i class="fas fa-user-tie"></i> Available
                        </span>
                        <span class="badge badge-secondary mr-2 mb-1">
                            <i class="fas fa-calendar-times"></i> No Availability
                        </span>
                    </div>
                </div>
                <div class="col-md-6 text-right">
                    <div class="form-group mb-0">
                        <label for="trainerFilter" class="form-label-sm">Filter by Trainer:</label>
                        <select id="trainerFilter" class="form-control form-control-sm" style="width: auto; display: inline-block;">
                            <option value="">All Trainers</option>
                            @foreach($trainers as $trainer)
                                <option value="{{ $trainer->id }}">{{ $trainer->user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Calendar Grid -->
        <div class="calendar-container">
            <div class="calendar-header">
                <div class="calendar-day-header">Sun</div>
                <div class="calendar-day-header">Mon</div>
                <div class="calendar-day-header">Tue</div>
                <div class="calendar-day-header">Wed</div>
                <div class="calendar-day-header">Thu</div>
                <div class="calendar-day-header">Fri</div>
                <div class="calendar-day-header">Sat</div>
            </div>
            
            @foreach($calendarData as $week)
                <div class="calendar-week">
                    @foreach($week as $day)
                        <div class="calendar-day {{ $day['isCurrentMonth'] ? '' : 'other-month' }} {{ $day['isToday'] ? 'today' : '' }}">
                            <div class="calendar-date">
                                {{ $day['date']->format('j') }}
                                @if($day['isToday'])
                                    <span class="today-indicator">•</span>
                                @endif
                            </div>
                            
                            @if(count($day['trainers']) > 0)
                                <div class="trainer-availability">
                                    @foreach($day['trainers'] as $trainerData)
                                        @php
                                            $availabilityCount = $trainerData['availabilities'] ? $trainerData['availabilities']->count() : 0;
                                        @endphp
                                        <div class="trainer-slot" 
                                             data-trainer-id="{{ $trainerData['trainer']->id }}"
                                             data-date="{{ $day['date']->format('Y-m-d') }}"
                                             data-toggle="tooltip" 
                                             title="{{ $trainerData['trainer']->user->name }} - {{ $availabilityCount }} slot(s)">
                                            <small class="trainer-name">{{ $trainerData['trainer']->user->name }}</small>
                                            <small class="slot-count">({{ $availabilityCount }})</small>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="no-availability">
                                    <small class="text-muted">No availability</small>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endforeach
        </div>
    </div>
</div>

<!-- Availability Details Modal -->
<div class="modal fade" id="availabilityModal" tabindex="-1" role="dialog" aria-labelledby="availabilityModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="availabilityModalLabel">Availability Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="availabilityDetails">
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('styles')
<style>
.calendar-container {
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
    overflow: hidden;
}

.calendar-header {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
}

.calendar-day-header {
    padding: 0.75rem;
    text-align: center;
    font-weight: 600;
    color: #495057;
    border-right: 1px solid #dee2e6;
}

.calendar-day-header:last-child {
    border-right: none;
}

.calendar-week {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    border-bottom: 1px solid #dee2e6;
}

.calendar-week:last-child {
    border-bottom: none;
}

.calendar-day {
    min-height: 120px;
    padding: 0.5rem;
    border-right: 1px solid #dee2e6;
    background-color: #fff;
    position: relative;
}

.calendar-day:last-child {
    border-right: none;
}

.calendar-day.other-month {
    background-color: #f8f9fa;
    color: #6c757d;
}

.calendar-day.today {
    background-color: #e3f2fd;
}

.calendar-date {
    font-weight: 600;
    margin-bottom: 0.5rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.today-indicator {
    color: #007bff;
    font-size: 1.2em;
}

.trainer-availability {
    max-height: 80px;
    overflow-y: auto;
}

.trainer-slot {
    background-color: #d4edda;
    border: 1px solid #c3e6cb;
    border-radius: 0.25rem;
    padding: 0.25rem 0.5rem;
    margin-bottom: 0.25rem;
    cursor: pointer;
    transition: all 0.2s ease;
}

.trainer-slot:hover {
    background-color: #c3e6cb;
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.trainer-name {
    font-weight: 500;
    color: #155724;
    display: block;
}

.slot-count {
    color: #28a745;
    font-size: 0.75em;
}

.no-availability {
    color: #6c757d;
    font-style: italic;
    text-align: center;
    padding-top: 1rem;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .calendar-day {
        min-height: 100px;
        padding: 0.25rem;
    }
    
    .trainer-name {
        font-size: 0.75em;
    }
    
    .slot-count {
        font-size: 0.65em;
    }
}

/* Modal styles */
.modal-body {
    max-height: 70vh;
    overflow-y: auto;
}

.availability-item {
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
    padding: 1rem;
    margin-bottom: 1rem;
    background-color: #f8f9fa;
}

.availability-item:last-child {
    margin-bottom: 0;
}

.availability-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.5rem;
}

.availability-time {
    font-weight: 600;
    color: #495057;
}

.availability-location {
    color: #6c757d;
    font-size: 0.875em;
}
</style>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Initialize tooltips
    $('[data-toggle="tooltip"]').tooltip();
    
    // Handle trainer slot clicks
    $('.trainer-slot').click(function() {
        const trainerId = $(this).data('trainer-id');
        const date = $(this).data('date');
        
        // Show modal with loading
        $('#availabilityModal').modal('show');
        $('#availabilityDetails').html(`
            <div class="text-center">
                <div class="spinner-border text-primary" role="status">
                    <span class="sr-only">Loading...</span>
                </div>
            </div>
        `);
        
        // Fetch availability data
        $.ajax({
            url: '{{ route("admin.trainer-calendar.get-availability") }}',
            method: 'GET',
            data: {
                date: date,
                trainer_id: trainerId
            },
            success: function(response) {
                let html = `<h6>Availability for ${new Date(date).toLocaleDateString()}</h6>`;
                
                if (response.availabilities.length > 0) {
                    response.availabilities.forEach(function(availability) {
                        html += `
                            <div class="availability-item">
                                <div class="availability-header">
                                    <span class="availability-trainer">${availability.trainer_name}</span>
                                    <span class="availability-time">${availability.start_time} - ${availability.end_time}</span>
                                </div>
                                <div class="availability-schedule">${availability.schedule_title}</div>
                                <div class="availability-location">${availability.location}</div>
                            </div>
                        `;
                    });
                } else {
                    html += '<p class="text-muted">No availability found for this date.</p>';
                }
                
                $('#availabilityDetails').html(html);
            },
            error: function() {
                $('#availabilityDetails').html(`
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i>
                        Error loading availability data. Please try again.
                    </div>
                `);
            }
        });
    });
    
    // Handle trainer filter
    $('#trainerFilter').change(function() {
        const selectedTrainer = $(this).val();
        
        if (selectedTrainer) {
            $('.trainer-slot').hide();
            $(`.trainer-slot[data-trainer-id="${selectedTrainer}"]`).show();
            $('.no-availability').show();
        } else {
            $('.trainer-slot').show();
            $('.no-availability').show();
        }
    });
});
</script>
@endsection 