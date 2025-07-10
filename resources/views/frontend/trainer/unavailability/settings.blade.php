@extends('layouts.frontend')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-cog"></i>
                        Default Availability Settings
                    </h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>New System:</strong> Configure when you're available by default. You only need to mark times when you're unavailable.
                    </div>

                    <form action="{{ route('frontend.trainer.unavailability.update-settings') }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="is_available_by_default" 
                                       name="is_available_by_default" value="1" 
                                       {{ $trainer->is_available_by_default ? 'checked' : '' }}>
                                <label class="custom-control-label" for="is_available_by_default">
                                    <strong>Available by Default</strong>
                                </label>
                                <small class="form-text text-muted">
                                    When enabled, you're available unless you mark specific times as unavailable.
                                </small>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="default_start_time">Default Start Time</label>
                                    <input type="time" class="form-control @error('default_start_time') is-invalid @enderror" 
                                           id="default_start_time" name="default_start_time" 
                                           value="{{ $trainer->default_start_time ? $trainer->default_start_time->format('H:i') : '09:00' }}">
                                    <small class="form-text text-muted">Leave empty for all day availability.</small>
                                    @error('default_start_time')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="default_end_time">Default End Time</label>
                                    <input type="time" class="form-control @error('default_end_time') is-invalid @enderror" 
                                           id="default_end_time" name="default_end_time" 
                                           value="{{ $trainer->default_end_time ? $trainer->default_end_time->format('H:i') : '17:00' }}">
                                    <small class="form-text text-muted">Leave empty for all day availability.</small>
                                    @error('default_end_time')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label><strong>Available Days</strong></label>
                            <div class="row">
                                @php
                                    $availableDays = $trainer->default_available_days ?? [0, 1, 2, 3, 4, 5, 6];
                                    $days = [
                                        0 => 'Sunday',
                                        1 => 'Monday', 
                                        2 => 'Tuesday',
                                        3 => 'Wednesday',
                                        4 => 'Thursday',
                                        5 => 'Friday',
                                        6 => 'Saturday'
                                    ];
                                @endphp
                                @foreach($days as $dayNum => $dayName)
                                    <div class="col-md-6">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" 
                                                   id="day_{{ $dayNum }}" 
                                                   name="default_available_days[]" 
                                                   value="{{ $dayNum }}"
                                                   {{ in_array($dayNum, $availableDays) ? 'checked' : '' }}>
                                            <label class="custom-control-label" for="day_{{ $dayNum }}">
                                                {{ $dayName }}
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            @error('default_available_days')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Current Settings:</strong><br>
                            @if($trainer->is_available_by_default)
                                ✅ You are <strong>available by default</strong><br>
                                @if($trainer->default_start_time && $trainer->default_end_time)
                                    ⏰ Available hours: {{ $trainer->default_start_time->format('g:i A') }} - {{ $trainer->default_end_time->format('g:i A') }}<br>
                                @else
                                    ⏰ Available hours: <strong>All day</strong><br>
                                @endif
                                📅 Available days: 
                                @php
                                    $availableDayNames = [];
                                    foreach($availableDays as $dayNum) {
                                        $availableDayNames[] = $days[$dayNum];
                                    }
                                @endphp
                                <strong>{{ implode(', ', $availableDayNames) }}</strong>
                            @else
                                ❌ You are <strong>not available by default</strong>. You need to mark specific times as available.
                            @endif
                        </div>

                        <div class="form-group mb-0">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Settings
                            </button>
                            <a href="{{ route('frontend.trainer.unavailability.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Back to Unavailability
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Validate end time is after start time
    function validateTimes() {
        const startTime = document.getElementById('default_start_time').value;
        const endTime = document.getElementById('default_end_time').value;
        
        if (startTime && endTime && startTime >= endTime) {
            document.getElementById('default_end_time').setCustomValidity('End time must be after start time');
        } else {
            document.getElementById('default_end_time').setCustomValidity('');
        }
    }
    
    document.getElementById('default_start_time').addEventListener('change', validateTimes);
    document.getElementById('default_end_time').addEventListener('change', validateTimes);
    
    // Initial validation
    validateTimes();
});
</script>
@endpush
@endsection 