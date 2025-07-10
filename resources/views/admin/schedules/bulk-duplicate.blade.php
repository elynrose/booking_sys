@extends('layouts.admin')

@section('title', 'Bulk Duplicate Schedules')

@section('styles')
<style>
    /* Custom checkbox styling */
    .form-check-input {
        width: 18px;
        height: 18px;
        margin: 0;
        cursor: pointer;
    }
    
    .form-check-input:checked {
        background-color: #28a745;
        border-color: #28a745;
    }
    
    .form-check-input:focus {
        box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
        border-color: #28a745;
    }
    
    /* Table styling improvements */
    .table th {
        vertical-align: middle;
        font-weight: 600;
        background-color: #ffffff !important;
        color: #333333 !important;
        border-color: #dee2e6;
    }
    
    .table td {
        vertical-align: middle;
    }
    
    /* Checkbox column styling */
    .table th:first-child,
    .table td:first-child {
        width: 50px;
        text-align: center;
    }
    
    /* Hover effect for table rows */
    .table tbody tr:hover {
        background-color: rgba(40, 167, 69, 0.05);
    }
    
    /* Selected row styling */
    .table tbody tr.selected {
        background-color: rgba(40, 167, 69, 0.1);
    }
</style>
<!-- SweetAlert2 CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-copy"></i> Bulk Duplicate Schedules
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.schedules.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Back to Schedules
                        </a>
                    </div>
                </div>
                
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>How it works:</strong> Select the schedules you want to duplicate, choose the destination month(s), and the system will create new schedules for the same day of the week in the target month(s). You can also duplicate to multiple consecutive months.
                    </div>
                    
                    <form action="{{ route('admin.schedules.bulk-duplicate') }}" method="POST" id="bulkDuplicateForm">
                        @csrf
                        
                        <!-- Step 1: Select Schedules -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5><i class="fas fa-list"></i> Step 1: Select Schedules to Duplicate</h5>
                                <div class="form-group">
                                    <label for="source_month">Filter by Month</label>
                                    <select class="form-control" id="source_month" name="source_month">
                                        <option value="">All Months</option>
                                        @php
                                            $currentMonth = now()->month;
                                            $currentYear = now()->year;
                                            for ($i = -6; $i <= 6; $i++) {
                                                $date = now()->addMonths($i);
                                                $selected = ($date->month == $currentMonth && $date->year == $currentYear) ? 'selected' : '';
                                                echo "<option value=\"{$date->format('Y-m')}\" {$selected}>{$date->format('F Y')}</option>";
                                            }
                                        @endphp
                                    </select>
                                    <small class="form-text text-muted">Filter schedules by month to make selection easier.</small>
                                </div>
                                
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover" id="schedulesTable">
                                        <thead>
                                            <tr>
                                                <th width="50" class="text-center">
                                                    <input type="checkbox" id="selectAll" class="form-check-input">
                                                </th>
                                                <th>Schedule Name</th>
                                                <th>Category</th>
                                                <th>Trainer</th>
                                                <th>Day of Week</th>
                                                <th>Time</th>
                                                <th>Status</th>
                                                <th>Recurring</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($schedules as $schedule)
                                            <tr data-month="{{ \Carbon\Carbon::parse($schedule->start_date)->format('Y-m') }}">
                                                <td class="text-center">
                                                    <input type="checkbox" name="selected_schedules[]" value="{{ $schedule->id }}" 
                                                           class="form-check-input schedule-checkbox">
                                                </td>
                                                <td>{{ $schedule->title }}</td>
                                                <td>{{ $schedule->category->name ?? 'N/A' }}</td>
                                                <td>{{ optional($schedule->trainer)->user->name ?? 'N/A' }}</td>
                                                <td>{{ \Carbon\Carbon::parse($schedule->start_date)->format('l') }}</td>
                                                <td>
                                                    {{ \Carbon\Carbon::parse($schedule->start_time)->format('H:i') }} - 
                                                    {{ \Carbon\Carbon::parse($schedule->end_time)->format('H:i') }}
                                                </td>
                                                <td>
                                                    @if($schedule->is_active)
                                                        <span class="badge badge-success">Active</span>
                                                    @else
                                                        <span class="badge badge-secondary">Inactive</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($schedule->is_recurring)
                                                        <span class="badge badge-info">Recurring</span>
                                                    @else
                                                        <span class="badge badge-warning">One-time</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                
                                <div class="alert alert-warning mt-3">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    <strong>Selected:</strong> <span id="selectedCount">0</span> schedules
                                </div>
                            </div>
                        </div>
                        
                        <!-- Step 2: Configuration -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5><i class="fas fa-cog"></i> Step 2: Configuration</h5>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="target_month">Destination Month <span class="text-danger">*</span></label>
                                            <select class="form-control" id="target_month" name="target_month" required>
                                                <option value="">Select Month</option>
                                                @php
                                                    $currentMonth = now()->month;
                                                    $currentYear = now()->year;
                                                    for ($i = 1; $i <= 60; $i++) {
                                                        $date = now()->addMonths($i);
                                                        $selected = ($date->month == $currentMonth + 1 && $date->year == $currentYear) ? 'selected' : '';
                                                        echo "<option value=\"{$date->format('Y-m')}\" {$selected}>{$date->format('F Y')}</option>";
                                                    }
                                                @endphp
                                            </select>
                                            <small class="form-text text-muted">Select the month where you want to create the duplicated schedules.</small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="report_email">Report Email <span class="text-danger">*</span></label>
                                            <input type="email" class="form-control" id="report_email" name="report_email" 
                                                   value="{{ auth()->user()->email }}" required>
                                            <small class="form-text text-muted">A detailed report will be sent to this email address.</small>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="duplicate_multiple">Duplicate to Multiple Months</label>
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input" id="duplicate_multiple" name="duplicate_multiple" value="1">
                                                <label class="custom-control-label" for="duplicate_multiple">
                                                    Create schedules for multiple consecutive months
                                                </label>
                                            </div>
                                            <small class="form-text text-muted">If checked, will create schedules for the selected month and subsequent months.</small>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="include_inactive">Include Inactive Schedules</label>
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input" id="include_inactive" name="include_inactive" value="1">
                                                <label class="custom-control-label" for="include_inactive">
                                                    Also duplicate inactive schedules
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="skip_unavailable">Skip Unavailable Trainers</label>
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input" id="skip_unavailable" name="skip_unavailable" value="1" checked>
                                                <label class="custom-control-label" for="skip_unavailable">
                                                    Skip schedules where trainers are unavailable
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Step 3: Summary -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5><i class="fas fa-clipboard-list"></i> Step 3: Summary</h5>
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    <strong>Important:</strong> This process will:
                                    <ul class="mb-0 mt-2">
                                        <li>Create new schedules for the selected schedules</li>
                                        <li>Check trainer availability for the target month(s)</li>
                                        <li>Create schedules for the same day of the week in the target month(s)</li>
                                        <li>Mark schedules as unavailable if trainers are not available</li>
                                        <li>Send a detailed report to the specified email</li>
                                        <li><strong>📸 Automatically copy schedule photos to the new schedules</strong></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-12">
                                <button type="submit" class="btn btn-warning btn-lg" id="bulkDuplicateBtn" disabled>
                                    <i class="fas fa-copy"></i> Start Duplication Process
                                </button>
                                <a href="{{ route('admin.schedules.index') }}" class="btn btn-secondary btn-lg ml-2">
                                    <i class="fas fa-times"></i> Cancel
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@section('scripts')
<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    // Filter schedules by month
    $('#source_month').on('change', function() {
        var selectedMonth = $(this).val();
        if (selectedMonth) {
            $('#schedulesTable tbody tr').hide();
            $('#schedulesTable tbody tr[data-month="' + selectedMonth + '"]').show();
        } else {
            $('#schedulesTable tbody tr').show();
        }
        updateSelectedCount();
    });
    
    // Individual checkbox change
    $(document).on('change', '.schedule-checkbox', function() {
        var $row = $(this).closest('tr');
        
        if ($(this).is(':checked')) {
            $row.addClass('selected');
        } else {
            $row.removeClass('selected');
        }
        
        updateSelectedCount();
        updateSubmitButton();
        
        // Update select all checkbox
        var totalVisible = $('.schedule-checkbox:visible').length;
        var checkedVisible = $('.schedule-checkbox:visible:checked').length;
        
        if (checkedVisible === 0) {
            $('#selectAll').prop('indeterminate', false).prop('checked', false);
        } else if (checkedVisible === totalVisible) {
            $('#selectAll').prop('indeterminate', false).prop('checked', true);
        } else {
            $('#selectAll').prop('indeterminate', true);
        }
    });
    
    // Select all functionality with visual feedback
    $('#selectAll').on('change', function() {
        var isChecked = $(this).is(':checked');
        $('.schedule-checkbox:visible').prop('checked', isChecked);
        
        // Update visual feedback for all visible rows
        if (isChecked) {
            $('#schedulesTable tbody tr:visible').addClass('selected');
        } else {
            $('#schedulesTable tbody tr:visible').removeClass('selected');
        }
        
        updateSelectedCount();
        updateSubmitButton();
    });
    
    function updateSelectedCount() {
        var count = $('.schedule-checkbox:checked').length;
        $('#selectedCount').text(count);
    }
    
    function updateSubmitButton() {
        var hasSelection = $('.schedule-checkbox:checked').length > 0;
        var hasTargetMonth = $('#target_month').val() !== '';
        var hasEmail = $('#report_email').val() !== '';
        
        $('#bulkDuplicateBtn').prop('disabled', !(hasSelection && hasTargetMonth && hasEmail));
    }
    
    // Form validation
    $('#target_month, #report_email').on('change keyup', function() {
        updateSubmitButton();
    });
    
    $('#bulkDuplicateForm').on('submit', function(e) {
        e.preventDefault();
        
        var selectedSchedules = $('.schedule-checkbox:checked').length;
        if (selectedSchedules === 0) {
            Swal.fire('Error!', 'Please select at least one schedule to duplicate.', 'error');
            return;
        }
        
        // Show loading state
        $('#bulkDuplicateBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Processing...');
        
        // Submit form via AJAX
        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                if (response.success) {
                    // Show success message
                    Swal.fire({
                        title: 'Success!',
                        html: `
                            <div class="text-left">
                                <p><strong>Duplication completed!</strong></p>
                                <p>Created: <strong>${response.created}</strong> schedules</p>
                                <p>Skipped: <strong>${response.skipped}</strong> schedules</p>
                                <p>Unavailable: <strong>${response.unavailable}</strong> schedules</p>
                                <p>A detailed report has been sent to: <strong>${response.email}</strong></p>
                            </div>
                        `,
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        // Redirect to schedules index
                        window.location.href = '{{ route("admin.schedules.index") }}';
                    });
                } else {
                    Swal.fire('Error!', response.message || 'An error occurred during duplication.', 'error');
                }
            },
            error: function(xhr) {
                let message = 'An error occurred during duplication.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                Swal.fire('Error!', message, 'error');
            },
            complete: function() {
                // Reset button state
                $('#bulkDuplicateBtn').prop('disabled', false).html('<i class="fas fa-copy"></i> Start Duplication Process');
            }
        });
    });
    
    // Initialize
    updateSelectedCount();
    updateSubmitButton();
});
</script>
@endsection
@endsection 