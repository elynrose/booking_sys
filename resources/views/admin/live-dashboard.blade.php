@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <!-- Live Dashboard Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Live Dashboard</h1>
        <div class="d-flex align-items-center">
            <span class="badge badge-success mr-2" id="connection-status">● Live</span>
            <span class="text-muted small">Auto-refresh every 30 seconds</span>
        </div>
    </div>

    <!-- Filters Row -->
    <div class="row mb-3">
        <div class="col-md-3">
            <label for="filter-date">Date</label>
            <input type="date" id="filter-date" class="form-control" value="{{ now()->format('Y-m-d') }}">
        </div>
        <div class="col-md-3">
            <label for="filter-class">Class</label>
            <select id="filter-class" class="form-control">
                <option value="">All Classes</option>
            </select>
        </div>
        <div class="col-md-3">
            <label for="filter-trainer">Trainer</label>
            <select id="filter-trainer" class="form-control">
                <option value="">All Trainers</option>
            </select>
        </div>
        <div class="col-md-3">
            <label for="filter-status">Status</label>
            <select id="filter-status" class="form-control">
                <option value="">All Statuses</option>
                <option value="active">Active</option>
                <option value="upcoming">Upcoming</option>
                <option value="ended">Ended</option>
            </select>
        </div>
    </div>

    <!-- Current Time and Date -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center">
                    <h4 class="mb-0" id="current-time">{{ now()->format('l, F j, Y g:i:s A') }}</h4>
                    <small class="text-muted">Last updated: <span id="last-updated">{{ now()->format('g:i:s A') }}</span></small>
                </div>
            </div>
        </div>
    </div>

    <!-- Live Statistics -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Active Classes Today</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="active-classes-count">-</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chalkboard-teacher fa-2x text-gray-300"></i>
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
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Students Checked In</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="checked-in-count">-</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-sign-in-alt fa-2x text-gray-300"></i>
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
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Active Trainers</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="active-trainers-count">-</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-tie fa-2x text-gray-300"></i>
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
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Pending Checkouts</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="pending-checkouts-count">-</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Live Activity Feed -->
    <div class="row">
        <!-- Current Classes -->
        <div class="col-xl-6 col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Current Classes</h6>
                    <div class="dropdown no-arrow">
                        <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in" aria-labelledby="dropdownMenuLink">
                            <div class="dropdown-header">Filter:</div>
                            <a class="dropdown-item" href="#" onclick="filterClasses('all')">All Classes</a>
                            <a class="dropdown-item" href="#" onclick="filterClasses('active')">Active Only</a>
                            <a class="dropdown-item" href="#" onclick="filterClasses('upcoming')">Upcoming</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div id="current-classes-container">
                        <div class="text-center">
                            <div class="spinner-border text-primary" role="status">
                                <span class="sr-only">Loading...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Live Activity Feed -->
        <div class="col-xl-6 col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Live Activity Feed</h6>
                    <button class="btn btn-sm btn-outline-primary" onclick="clearActivityFeed()">Clear</button>
                </div>
                <div class="card-body">
                    <div id="activity-feed-container" style="max-height: 400px; overflow-y: auto;">
                        <div class="text-center">
                            <div class="spinner-border text-primary" role="status">
                                <span class="sr-only">Loading...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Trainer Assignments and Check-ins -->
    <div class="row">
        <!-- Trainer Assignments -->
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Trainer Assignments & Students</h6>
                </div>
                <div class="card-body">
                    <div id="trainer-assignments-container">
                        <div class="text-center">
                            <div class="spinner-border text-primary" role="status">
                                <span class="sr-only">Loading...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Check-ins/Check-outs -->
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Check-ins/Check-outs</h6>
                </div>
                <div class="card-body">
                    <div id="recent-checkins-container">
                        <div class="text-center">
                            <div class="spinner-border text-primary" role="status">
                                <span class="sr-only">Loading...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Activity Feed Item Template -->
<template id="activity-item-template">
    <div class="activity-item border-left border-primary pl-3 mb-3">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <strong class="activity-user"></strong>
                <span class="activity-action"></span>
                <span class="activity-details"></span>
            </div>
            <small class="text-muted activity-time"></small>
        </div>
    </div>
</template>

<!-- Class Item Template -->
<template id="class-item-template">
    <div class="class-item border rounded p-3 mb-3">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <h6 class="class-title mb-1"></h6>
                <p class="class-trainer mb-1 text-muted"></p>
                <p class="class-time mb-1"></p>
                <div class="class-status"></div>
            </div>
            <div class="class-stats text-right">
                <div class="class-capacity"></div>
                <div class="class-checkins"></div>
            </div>
        </div>
    </div>
</template>

@endsection

@push('scripts')
<script>
let refreshInterval;
let lastActivityId = 0;
let filterData = {};

$(document).ready(function() {
    // Populate class and trainer filters on first load
    loadFilterOptions();
    // Initialize dashboard
    loadDashboardData();
    // Set up auto-refresh every 30 seconds
    refreshInterval = setInterval(function() {
        loadDashboardData();
    }, 30000);
    // Update current time every second
    setInterval(function() {
        updateCurrentTime();
    }, 1000);
    // Filter change events
    $('#filter-date, #filter-class, #filter-trainer, #filter-status').on('change', function() {
        loadDashboardData();
    });
});

function loadFilterOptions() {
    $.ajax({
        url: '{{ route("admin.live-dashboard.data") }}',
        method: 'GET',
        data: { options_only: 1 },
        success: function(data) {
            // Populate class filter
            let classSelect = $('#filter-class');
            classSelect.empty().append('<option value="">All Classes</option>');
            data.classOptions.forEach(function(cls) {
                classSelect.append(`<option value="${cls.id}">${cls.title}</option>`);
            });
            // Populate trainer filter
            let trainerSelect = $('#filter-trainer');
            trainerSelect.empty().append('<option value="">All Trainers</option>');
            data.trainerOptions.forEach(function(tr) {
                trainerSelect.append(`<option value="${tr.id}">${tr.name}</option>`);
            });
        }
    });
}

function loadDashboardData() {
    filterData = {
        date: $('#filter-date').val(),
        class_id: $('#filter-class').val(),
        trainer_id: $('#filter-trainer').val(),
        status: $('#filter-status').val()
    };
    $.ajax({
        url: '{{ route("admin.live-dashboard.data") }}',
        method: 'GET',
        data: filterData,
        success: function(data) {
            updateStatistics(data.statistics);
            updateCurrentClasses(data.currentClasses);
            updateActivityFeed(data.activityFeed);
            updateTrainerAssignments(data.trainerAssignments);
            updateRecentCheckins(data.recentCheckins);
            updateLastUpdated();
        },
        error: function(xhr, status, error) {
            console.error('Error loading dashboard data:', error);
            $('#connection-status').removeClass('badge-success').addClass('badge-danger').text('● Offline');
        }
    });
}

function updateStatistics(stats) {
    $('#active-classes-count').text(stats.activeClasses);
    $('#checked-in-count').text(stats.checkedInStudents);
    $('#active-trainers-count').text(stats.activeTrainers);
    $('#pending-checkouts-count').text(stats.pendingCheckouts);
}

function updateCurrentClasses(classes) {
    const container = $('#current-classes-container');
    container.empty();
    
    if (classes.length === 0) {
        container.html('<p class="text-muted text-center">No classes currently running</p>');
        return;
    }
    
    classes.forEach(function(classData) {
        const template = document.getElementById('class-item-template');
        const clone = template.content.cloneNode(true);
        
        clone.querySelector('.class-title').textContent = classData.title;
        clone.querySelector('.class-trainer').textContent = `Trainer: ${classData.trainer}`;
        clone.querySelector('.class-time').textContent = `${classData.start_time} - ${classData.end_time}`;
        
        const statusElement = clone.querySelector('.class-status');
        if (classData.status === 'active') {
            statusElement.innerHTML = '<span class="badge badge-success">Active</span>';
        } else if (classData.status === 'upcoming') {
            statusElement.innerHTML = '<span class="badge badge-info">Starting Soon</span>';
        } else {
            statusElement.innerHTML = '<span class="badge badge-secondary">Ended</span>';
        }
        
        clone.querySelector('.class-capacity').textContent = `${classData.current_participants}/${classData.max_participants} students`;
        clone.querySelector('.class-checkins').textContent = `${classData.checked_in_count} checked in`;
        
        container.append(clone);
    });
}

function updateActivityFeed(activities) {
    const container = $('#activity-feed-container');
    
    activities.forEach(function(activity) {
        if (activity.id > lastActivityId) {
            const template = document.getElementById('activity-item-template');
            const clone = template.content.cloneNode(true);
            
            clone.querySelector('.activity-user').textContent = activity.user;
            clone.querySelector('.activity-action').textContent = activity.action;
            clone.querySelector('.activity-details').textContent = activity.details;
            clone.querySelector('.activity-time').textContent = activity.time;
            
            container.prepend(clone);
            lastActivityId = activity.id;
        }
    });
    
    // Keep only last 50 activities
    const items = container.find('.activity-item');
    if (items.length > 50) {
        items.slice(50).remove();
    }
}

function updateTrainerAssignments(assignments) {
    const container = $('#trainer-assignments-container');
    container.empty();
    
    assignments.forEach(function(trainer) {
        const trainerHtml = `
            <div class="trainer-card border rounded p-3 mb-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="mb-0">${trainer.name}</h6>
                    <span class="badge badge-${trainer.status === 'active' ? 'success' : 'secondary'}">${trainer.status}</span>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <strong>Current Class:</strong> ${trainer.current_class || 'None'}
                    </div>
                    <div class="col-md-6">
                        <strong>Students:</strong> ${trainer.student_count}
                    </div>
                </div>
                ${trainer.students.length > 0 ? `
                <div class="mt-2">
                    <strong>Assigned Students:</strong>
                    <div class="student-list mt-1">
                        ${trainer.students.map(student => `
                            <span class="badge badge-light mr-1 mb-1">${student.name}</span>
                        `).join('')}
                    </div>
                </div>
                ` : ''}
            </div>
        `;
        container.append(trainerHtml);
    });
}

function updateRecentCheckins(checkins) {
    const container = $('#recent-checkins-container');
    container.empty();
    
    checkins.forEach(function(checkin) {
        const checkinHtml = `
            <div class="checkin-item border-left border-${checkin.type === 'in' ? 'success' : 'warning'} pl-3 mb-2">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <strong>${checkin.student_name}</strong><br>
                        <small class="text-muted">${checkin.class_name}</small>
                    </div>
                    <div class="text-right">
                        <span class="badge badge-${checkin.type === 'in' ? 'success' : 'warning'}">${checkin.type.toUpperCase()}</span><br>
                        <small class="text-muted">${checkin.time}</small>
                    </div>
                </div>
            </div>
        `;
        container.append(checkinHtml);
    });
}

function updateCurrentTime() {
    const now = new Date();
    const timeString = now.toLocaleString('en-US', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
        second: '2-digit',
        hour12: true
    });
    $('#current-time').text(timeString);
}

function updateLastUpdated() {
    const now = new Date();
    const timeString = now.toLocaleTimeString('en-US', {
        hour: 'numeric',
        minute: '2-digit',
        second: '2-digit',
        hour12: true
    });
    $('#last-updated').text(timeString);
    $('#connection-status').removeClass('badge-danger').addClass('badge-success').text('● Live');
}

function filterClasses(filter) {
    // Implementation for filtering classes
    console.log('Filtering classes by:', filter);
}

function clearActivityFeed() {
    $('#activity-feed-container').empty();
    lastActivityId = 0;
}

// Clean up interval when page is unloaded
$(window).on('beforeunload', function() {
    if (refreshInterval) {
        clearInterval(refreshInterval);
    }
});
</script>
@endpush 