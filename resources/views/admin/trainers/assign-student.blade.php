@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="card-title">{{ __('app.trainers.assign_student', ['name' => $trainer->user->name]) }}</h3>
        <a href="{{ route('admin.trainers.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i>{{ __('app.actions.back') }}
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.trainers.assign-student.store') }}" method="POST">
                @csrf
                <input type="hidden" name="trainer_id" value="{{ $trainer->id }}">

                <div class="mb-3">
                    <label for="schedule_id" class="form-label">{{ __('app.trainers.select_schedule') }}</label>
                    <select class="form-control @error('schedule_id') is-invalid @enderror" 
                            id="schedule_id" name="schedule_id" required>
                        <option value="">{{ __('app.trainers.select_a_schedule') }}</option>
                        @foreach($schedules as $schedule)
                            <option value="{{ $schedule->id }}" {{ old('schedule_id') == $schedule->id ? 'selected' : '' }}>
                                {{ $schedule->title }} - {{ $schedule->start_time }} - {{ $schedule->end_time }}
                            </option>
                        @endforeach
                    </select>
                    @error('schedule_id')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="user_id" class="form-label">{{ __('app.trainers.select_user') }}</label>
                    <select class="form-control @error('user_id') is-invalid @enderror" 
                            id="user_id" name="user_id" required>
                        <option value="">{{ __('app.trainers.select_a_user') }}</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }} ({{ $user->email }})
                            </option>
                        @endforeach
                    </select>
                    @error('user_id')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="child_ids" class="form-label">{{ __('app.trainers.select_children') }}</label>
                    <select class="form-control @error('child_ids') is-invalid @enderror" 
                            id="child_ids" name="child_ids[]" multiple required>
                    </select>
                    @error('child_ids')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="payment_status" class="form-label">{{ __('app.trainers.payment_status') }}</label>
                    <select class="form-control @error('payment_status') is-invalid @enderror" 
                            id="payment_status" name="payment_status" required>
                        <option value="paid" {{ old('payment_status') == 'paid' ? 'selected' : '' }}>{{ __('app.trainers.paid') }}</option>
                        <option value="pending" {{ old('payment_status') == 'pending' ? 'selected' : '' }}>{{ __('app.trainers.pending') }}</option>
                    </select>
                    @error('payment_status')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>{{ __('app.actions.save') }}
                    </button>
                    <a href="{{ route('admin.trainers.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times me-1"></i>{{ __('app.actions.cancel') }}
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize Select2 for child selection
    $('#child_ids').select2({
        placeholder: '{{ __("app.trainers.select_children") }}',
        allowClear: true
    });

    // Load children when user is selected
    $('#user_id').change(function() {
        var userId = $(this).val();
        var childSelect = $('#child_ids');
        
        // Clear current options
        childSelect.empty();
        
        if (userId) {
            // Fetch children for selected user
            $.ajax({
                url: '/admin/users/' + userId + '/children',
                method: 'GET',
                success: function(data) {
                    data.forEach(function(child) {
                        var ageText = child.date_of_birth ? ' (' + child.age + ' {{ __("app.time.years") }})' : '';
                        childSelect.append(`<option value="${child.id}">${child.name}${ageText}</option>`);
                    });
                },
                error: function() {
                    console.error('Failed to load children');
                }
            });
        }
    });
});
</script>
@endpush
@endsection 