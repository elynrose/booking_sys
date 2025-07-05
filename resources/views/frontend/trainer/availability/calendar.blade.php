@extends('layouts.frontend')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">
                    <i class="fas fa-calendar-alt text-primary"></i>
                    Availability Calendar
                </h2>
                <div class="btn-group" role="group">
                    <a href="{{ route('frontend.trainer.availability.index') }}" class="btn btn-secondary">
                        <i class="fas fa-list"></i> List View
                    </a>
                    <a href="{{ route('frontend.trainer.availability.create') }}" class="btn btn-success">
                        <i class="fas fa-plus"></i> Add Availability
                    </a>
                </div>
            </div>

            <!-- Month Navigation -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <a href="?month={{ $date->copy()->subMonth()->format('Y-m') }}" class="btn btn-outline-primary">
                            <i class="fas fa-chevron-left"></i> Previous Month
                        </a>
                        <h4 class="mb-0">{{ $date->format('F Y') }}</h4>
                        <a href="?month={{ $date->copy()->addMonth()->format('Y-m') }}" class="btn btn-outline-primary">
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
                                $firstDay = $date->copy()->startOfMonth();
                                $lastDay = $date->copy()->endOfMonth();
                                $startDate = $firstDay->copy()->startOfWeek();
                                $endDate = $lastDay->copy()->endOfWeek();
                                $currentDate = $startDate->copy();
                            @endphp

                            @while($currentDate <= $endDate)
                                @php
                                    $isCurrentMonth = $currentDate->month === $date->month;
                                    $isToday = $currentDate->isToday();
                                    $availability = $availabilities->get($currentDate->format('Y-m-d'));
                                @endphp

                                <div class="calendar-day {{ !$isCurrentMonth ? 'other-month' : '' }} {{ $isToday ? 'today' : '' }}">
                                    <div class="calendar-day-number">{{ $currentDate->day }}</div>
                                    
                                    @if($availability)
                                        <div class="availability-info">
                                            <small class="d-block">
                                                {{ \Carbon\Carbon::createFromFormat('H:i:s', $availability->start_time)->format('g:i A') }}
                                                -
                                                {{ \Carbon\Carbon::createFromFormat('H:i:s', $availability->end_time)->format('g:i A') }}
                                            </small>
                                            <span class="badge bg-{{ $availability->status === 'available' ? 'success' : ($availability->status === 'unavailable' ? 'danger' : 'warning') }}">
                                                {{ ucfirst($availability->status) }}
                                            </span>
                                        </div>
                                    @else
                                        @if($isCurrentMonth && !$currentDate->isPast())
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
                                <span>Available</span>
                            </div>
                        </div>
                        <div class="col-md-4 mb-2">
                            <div class="d-flex align-items-center">
                                <div class="availability-badge unavailable mr-2">
                                    <i class="fas fa-times-circle"></i>
                                </div>
                                <span>Unavailable</span>
                            </div>
                        </div>
                        <div class="col-md-4 mb-2">
                            <div class="d-flex align-items-center">
                                <div class="availability-badge busy mr-2">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <span>Busy</span>
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
}

.calendar-day {
    min-height: 100px;
    padding: 8px;
    border-right: 1px solid #dee2e6;
    border-bottom: 1px solid #dee2e6;
    position: relative;
    background-color: #fff;
    transition: background-color 0.2s ease;
}

.calendar-day:nth-child(7n) {
    border-right: none;
}

.calendar-day:hover {
    background-color: #f8f9fa;
}

.calendar-day.other-month {
    background-color: #f8f9fa;
    color: #6c757d;
}

.calendar-day.today {
    background-color: #e3f2fd;
    font-weight: bold;
    border: 2px solid #2196f3;
}

.calendar-day-number {
    font-size: 14px;
    font-weight: 500;
    margin-bottom: 8px;
    color: #212529;
}

.calendar-day.other-month .calendar-day-number {
    color: #6c757d;
}

.calendar-day.today .calendar-day-number {
    color: #2196f3;
    font-weight: bold;
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

.availability-indicator {
    position: absolute;
    top: 8px;
    right: 8px;
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
        min-height: 80px;
        padding: 6px;
    }
    
    .calendar-day-number {
        font-size: 12px;
        margin-bottom: 4px;
    }
    
    .availability-badge {
        width: 20px;
        height: 20px;
        font-size: 10px;
    }
    
    .calendar-day-header {
        padding: 8px 4px;
        font-size: 0.75rem;
    }
    
    .btn-group .btn {
        font-size: 0.875rem;
        padding: 0.375rem 0.75rem;
    }
}

@media (max-width: 576px) {
    .calendar-day {
        min-height: 60px;
        padding: 4px;
    }
    
    .calendar-day-number {
        font-size: 11px;
        margin-bottom: 2px;
    }
    
    .availability-badge {
        width: 16px;
        height: 16px;
        font-size: 8px;
    }
    
    .calendar-day-header {
        padding: 6px 2px;
        font-size: 0.7rem;
    }
    
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
}

/* Print styles */
@media print {
    .calendar-container {
        border: 1px solid #000;
    }
    
    .calendar-day {
        border: 1px solid #000;
    }
    
    .availability-badge {
        border: 1px solid #000;
    }
}
</style>
@endsection 