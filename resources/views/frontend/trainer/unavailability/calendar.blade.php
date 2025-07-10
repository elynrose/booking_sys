@extends('layouts.frontend')

@section('styles')
<script>
function markUnavailable(date) {
    if (confirm('Are you sure you want to mark this date as unavailable?')) {
        const formData = new FormData();
        formData.append('date', date);
        formData.append('start_time', '09:00');
        formData.append('end_time', '17:00');
        formData.append('reason', 'personal');
        formData.append('notes', 'Marked unavailable from calendar');
        
        fetch('{{ route("frontend.trainer.unavailability.store") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error marking unavailable: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            alert('Error marking unavailable');
        });
    }
}

function deleteUnavailability(unavailabilityId) {
    if (confirm('Are you sure you want to remove this unavailability?')) {
        const url = `/trainer/unavailability/${unavailabilityId}`;
        
        fetch(url, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error removing unavailability: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            alert('Error removing unavailability');
        });
    }
}
</script>
@endsection

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">
                    <i class="fas fa-calendar-alt text-primary"></i>
                    Unavailability Calendar
                </h2>
                <div class="btn-group" role="group">
                    <a href="{{ route('frontend.trainer.availability.index') }}" class="btn btn-secondary">
                        <i class="fas fa-list"></i> Back to Schedules
                    </a>
                    <a href="{{ route('frontend.trainer.unavailability.create') }}" class="btn btn-warning">
                        <i class="fas fa-plus"></i> Mark Unavailable
                    </a>
                    <a href="{{ route('frontend.trainer.unavailability.settings') }}" class="btn btn-info">
                        <i class="fas fa-cog"></i> Settings
                    </a>
                </div>
            </div>

            <!-- Month Navigation -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <a href="?month={{ $startOfMonth->copy()->subMonth()->format('Y-m') }}" class="btn btn-outline-primary">
                            <i class="fas fa-chevron-left"></i> Previous Month
                        </a>
                        <h4 class="mb-0">{{ $startOfMonth->format('F Y') }}</h4>
                        <a href="?month={{ $startOfMonth->copy()->addMonth()->format('Y-m') }}" class="btn btn-outline-primary">
                            Next Month <i class="fas fa-chevron-right"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Calendar -->
            <div class="card">
                <div class="card-body p-0">
                    <div class="calendar-container">
                        <!-- Calendar Header -->
                        <div class="calendar-header">
                            <div class="calendar-day-header">Sun</div>
                            <div class="calendar-day-header">Mon</div>
                            <div class="calendar-day-header">Tue</div>
                            <div class="calendar-day-header">Wed</div>
                            <div class="calendar-day-header">Thu</div>
                            <div class="calendar-day-header">Fri</div>
                            <div class="calendar-day-header">Sat</div>
                        </div>

                        <!-- Calendar Body -->
                        <div class="calendar-body">
                            @php
                                $firstDay = $startOfMonth->copy()->startOfMonth();
                                $lastDay = $startOfMonth->copy()->endOfMonth();
                                $startDate = $firstDay->copy()->startOfWeek(\Carbon\Carbon::SUNDAY);
                                $endDate = $lastDay->copy()->endOfWeek(\Carbon\Carbon::SUNDAY);
                                $currentDate = $startDate->copy();
                            @endphp

                            @while($currentDate <= $endDate)
                                @php
                                    $isCurrentMonth = $currentDate->month === $startOfMonth->month;
                                    $isToday = $currentDate->isToday();
                                    $unavailability = $unavailabilities->get($currentDate->format('Y-m-d'));
                                    
                                    // Check if trainer is available by default
                                    $isAvailableByDefault = $trainer->is_available_by_default ?? false;
                                    $dayOfWeek = $currentDate->dayOfWeek;
                                    $availableDays = $trainer->default_available_days ?? [0, 1, 2, 3, 4, 5, 6];
                                    $isDayAvailable = in_array($dayOfWeek, $availableDays);
                                    $isAvailable = $isAvailableByDefault && $isDayAvailable && !$unavailability;
                                @endphp

                                <div class="calendar-day {{ !$isCurrentMonth ? 'other-month' : '' }} {{ $isToday ? 'today' : '' }} {{ $isAvailable ? 'available' : 'unavailable' }}">
                                    <div class="calendar-day-number">{{ $currentDate->day }}</div>
                                    
                                    @if($isAvailable)
                                        <div class="availability-info">
                                            <small class="d-block">
                                                @if($trainer->default_start_time && $trainer->default_end_time)
                                                    {{ $trainer->default_start_time->format('g:i A') }}
                                                    -
                                                    {{ $trainer->default_end_time->format('g:i A') }}
                                                @else
                                                    9:00 AM - 5:00 PM
                                                @endif
                                            </small>
                                            <span class="badge bg-success">
                                                Available
                                            </span>
                                        </div>
                                    @elseif($unavailability)
                                        <div class="availability-info">
                                            <small class="d-block">
                                                @if($unavailability->start_time && $unavailability->end_time)
                                                    {{ $unavailability->start_time->format('g:i A') }}
                                                    -
                                                    {{ $unavailability->end_time->format('g:i A') }}
                                                @else
                                                    All day
                                                @endif
                                            </small>
                                            <span class="badge bg-danger">
                                                Unavailable
                                            </span>
                                        </div>
                                    @else
                                        @if($isCurrentMonth && !$currentDate->isPast())
                                            <div class="availability-info">
                                                <small class="text-muted">
                                                    @if(!$isAvailableByDefault)
                                                        Not available by default
                                                    @elseif(!$isDayAvailable)
                                                        Not available on {{ $currentDate->format('l') }}
                                                    @else
                                                        No availability
                                                    @endif
                                                </small>
                                            </div>
                                        @endif
                                    @endif

                                    <!-- Action Buttons -->
                                    @if($isCurrentMonth && !$currentDate->isPast())
                                        @if($unavailability)
                                            <!-- Delete button for unavailable days -->
                                            <div class="action-button" onclick="deleteUnavailability('{{ $unavailability->id }}')" title="Remove Unavailability" style="background: #dc3545; color: white;">
                                                <i class="fas fa-trash"></i>
                                            </div>
                                        @else
                                            <!-- Add button for available days -->
                                            <div class="action-button" onclick="markUnavailable('{{ $currentDate->format('Y-m-d') }}')" title="Mark Unavailable" style="background: #ffc107; color: #212529;">
                                                <i class="fas fa-ban"></i>
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
                </div>
            </div>

            <!-- Legend -->
            <div class="card mt-4">
                <div class="card-header">
                    <h6 class="mb-0">Legend</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-2">
                            <div class="d-flex align-items-center">
                                <div class="availability-badge available mr-2">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                                <span>Available (Default)</span>
                            </div>
                        </div>
                        <div class="col-md-4 mb-2">
                            <div class="d-flex align-items-center">
                                <div class="availability-badge unavailable mr-2">
                                    <i class="fas fa-times-circle"></i>
                                </div>
                                <span>Marked Unavailable</span>
                            </div>
                        </div>
                        <div class="col-md-4 mb-2">
                            <div class="d-flex align-items-center">
                                <div class="availability-badge busy mr-2">
                                    <i class="fas fa-calendar-times"></i>
                                </div>
                                <span>Not Available by Default</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.calendar-container {
    width: 100%;
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
    padding: 12px 8px;
    text-align: center;
    font-weight: 600;
    color: #495057;
    font-size: 0.875rem;
    border-right: 1px solid #dee2e6;
}

.calendar-day-header:last-child {
    border-right: none;
}

.calendar-body {
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

.calendar-day:nth-child(7n) {
    border-right: none;
}

.calendar-day.other-month {
    background: #f8f9fa;
    color: #6c757d;
}

.calendar-day.today {
    background: #e3f2fd;
    border: 2px solid #2196f3;
}

.calendar-day.available {
    background-color: #d4edda;
    border-color: #c3e6cb;
}

.calendar-day.unavailable {
    background-color: #f8d7da;
    border-color: #f5c6cb;
}

.calendar-day-number {
    font-size: 14px;
    font-weight: 500;
    margin-bottom: 8px;
}

.availability-info {
    margin-top: 4px;
}

.availability-info small {
    font-size: 0.75rem;
    color: #6c757d;
}

.availability-info .badge {
    font-size: 0.7rem;
    padding: 0.25rem 0.5rem;
}

.action-button {
    position: absolute;
    top: 5px;
    right: 5px;
    width: 28px;
    height: 28px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.95);
    border: 1px solid #dee2e6;
    cursor: pointer;
    transition: all 0.2s ease;
    z-index: 10;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.action-button:hover {
    transform: scale(1.2);
    background: rgba(255, 255, 255, 1);
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
}

.action-button i {
    font-size: 14px;
    pointer-events: none;
}

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

.availability-badge.busy {
    background-color: #ffc107;
    color: #212529;
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .calendar-day {
        min-height: 60px;
        padding: 4px;
    }
    .calendar-day-header {
        padding: 8px 4px;
        font-size: 0.75rem;
    }
    .availability-badge {
        width: 20px;
        height: 20px;
        font-size: 10px;
    }
}

@media (max-width: 576px) {
    .calendar-day {
        min-height: 50px;
        padding: 2px;
    }
    .calendar-day-header {
        padding: 6px 2px;
        font-size: 0.7rem;
    }
    .availability-badge {
        width: 16px;
        height: 16px;
        font-size: 8px;
    }
}

/* Card header styling */
.card-header {
    background-color: white !important;
    border-bottom: 1px solid #dee2e6;
}
</style>
@endsection 