@extends('layouts.frontend')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">
                        <i class="fas fa-calendar-times"></i>
                        Mark Unavailable Time
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('frontend.trainer.unavailability.store') }}" method="POST">
                        @csrf
                        
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            <strong>New System:</strong> You are now available by default. Only mark times when you're <strong>unavailable</strong>.
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="date">Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control @error('date') is-invalid @enderror" 
                                           id="date" name="date" value="{{ old('date', date('Y-m-d')) }}" required>
                                    @error('date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="reason">Reason <span class="text-danger">*</span></label>
                                    <select class="form-control @error('reason') is-invalid @enderror" id="reason" name="reason" required>
                                        <option value="">Select Reason</option>
                                        <option value="personal" {{ old('reason') == 'personal' ? 'selected' : '' }}>Personal</option>
                                        <option value="sick" {{ old('reason') == 'sick' ? 'selected' : '' }}>Sick</option>
                                        <option value="vacation" {{ old('reason') == 'vacation' ? 'selected' : '' }}>Vacation</option>
                                        <option value="other" {{ old('reason') == 'other' ? 'selected' : '' }}>Other</option>
                                    </select>
                                    @error('reason')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="is_all_day" name="is_all_day" value="1" {{ old('is_all_day') ? 'checked' : '' }}>
                                <label class="custom-control-label" for="is_all_day">
                                    <strong>All Day Unavailable</strong>
                                </label>
                            </div>
                        </div>

                        <div id="time_fields" class="row" style="{{ old('is_all_day') ? 'display: none;' : '' }}">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="start_time">Start Time</label>
                                    <input type="time" class="form-control @error('start_time') is-invalid @enderror" 
                                           id="start_time" name="start_time" value="{{ old('start_time', '09:00') }}">
                                    @error('start_time')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="end_time">End Time</label>
                                    <input type="time" class="form-control @error('end_time') is-invalid @enderror" 
                                           id="end_time" name="end_time" value="{{ old('end_time', '10:00') }}">
                                    @error('end_time')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="schedule_id">Specific Schedule (Optional)</label>
                            <select class="form-control @error('schedule_id') is-invalid @enderror" id="schedule_id" name="schedule_id">
                                <option value="">All schedules</option>
                                @foreach($schedules as $schedule)
                                    <option value="{{ $schedule->id }}" {{ old('schedule_id') == $schedule->id ? 'selected' : '' }}>
                                        {{ $schedule->title }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">Leave blank to mark unavailable for all schedules on this date.</small>
                            @error('schedule_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="notes">Notes (Optional)</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" 
                                      id="notes" name="notes" rows="3" 
                                      placeholder="Add any notes about this unavailability...">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-0">
                            <button type="submit" class="btn btn-warning">
                                <i class="fas fa-save"></i> Mark Unavailable
                            </button>
                            <a href="{{ route('frontend.trainer.unavailability.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Back to List
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
    // Set default date to today
    document.getElementById('date').value = new Date().toISOString().split('T')[0];
    
    // Toggle time fields based on all day checkbox
    document.getElementById('is_all_day').addEventListener('change', function() {
        const timeFields = document.getElementById('time_fields');
        if (this.checked) {
            timeFields.style.display = 'none';
            // Clear time values when all day is selected
            document.getElementById('start_time').value = '';
            document.getElementById('end_time').value = '';
        } else {
            timeFields.style.display = 'block';
            // Set default times when all day is unchecked
            if (!document.getElementById('start_time').value) {
                document.getElementById('start_time').value = '09:00';
            }
            if (!document.getElementById('end_time').value) {
                document.getElementById('end_time').value = '10:00';
            }
        }
    });
    
    // Validate end time is after start time
    function validateTimes() {
        const startTime = document.getElementById('start_time').value;
        const endTime = document.getElementById('end_time').value;
        const isAllDay = document.getElementById('is_all_day').checked;
        
        if (!isAllDay && startTime && endTime && startTime >= endTime) {
            document.getElementById('end_time').setCustomValidity('End time must be after start time');
        } else {
            document.getElementById('end_time').setCustomValidity('');
        }
    }
    
    document.getElementById('start_time').addEventListener('change', validateTimes);
    document.getElementById('end_time').addEventListener('change', validateTimes);
    
    // Initial validation
    validateTimes();
});
</script>
@endpush
@endsection 