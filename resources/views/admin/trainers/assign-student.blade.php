@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Assign Children to {{ $trainer->user->name }}</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.trainers.index') }}" class="btn btn-default btn-sm">
                            <i class="fas fa-arrow-left"></i> Back to Trainers
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.trainers.assign-student.store', $trainer) }}" method="POST" id="assignForm">
                        @csrf
                        
                        <div class="form-group">
                            <label for="schedule_id">Select Schedule</label>
                            <select name="schedule_id" id="schedule_id" class="form-control @error('schedule_id') is-invalid @enderror" required>
                                <option value="">Select a schedule</option>
                                @foreach($schedules as $schedule)
                                    <option value="{{ $schedule->id }}">
                                        {{ $schedule->title }} - 
                                        @if($schedule->start_date && $schedule->end_date)
                                            {{ $schedule->start_date->format('M d, Y') }} to 
                                            {{ $schedule->end_date->format('M d, Y') }}
                                        @endif
                                        @if($schedule->start_time && $schedule->end_time)
                                            ({{ $schedule->start_time->format('h:i A') }} - {{ $schedule->end_time->format('h:i A') }})
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('schedule_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="user_id">Select User (who has paid for this trainer's sessions)</label>
                            <select name="user_id" id="user_id" class="form-control select2 @error('user_id') is-invalid @enderror" required>
                                <option value="">Select a user</option>
                                @foreach($paidUsers as $user)
                                    <option value="{{ $user->id }}" data-children="{{ $user->children->toJson() }}">
                                        {{ $user->name }} ({{ $user->email }}) - {{ $user->children->count() }} children
                                    </option>
                                @endforeach
                            </select>
                            @error('user_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group" id="childrenSelectionGroup" style="display: none;">
                            <label for="child_ids">Select Children</label>
                            <select name="child_ids[]" id="child_ids" class="form-control select2 @error('child_ids') is-invalid @enderror" multiple required>
                                <!-- Children options will be dynamically added here -->
                            </select>
                            @error('child_ids')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="payment_status">Payment Status</label>
                            <select name="payment_status" id="payment_status" class="form-control @error('payment_status') is-invalid @enderror" required>
                                <option value="paid">Paid</option>
                                <option value="pending">Pending</option>
                            </select>
                            @error('payment_status')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Assign Children
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
.select2-container--default .select2-selection--multiple {
    border: 1px solid #ced4da;
    border-radius: 0.375rem;
}
.select2-container--default .select2-selection--single {
    border: 1px solid #ced4da;
    border-radius: 0.375rem;
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Select2 for user selection
    $('#user_id').select2({
        placeholder: 'Select a user...',
        allowClear: true,
        width: '100%'
    });

    // Initialize Select2 for children selection
    $('#child_ids').select2({
        placeholder: 'Select children...',
        allowClear: true,
        width: '100%'
    });

    // Handle user selection changes
    $('#user_id').on('change', function() {
        updateChildrenSelection();
    });

    function updateChildrenSelection() {
        const userId = $('#user_id').val();
        const childrenGroup = $('#childrenSelectionGroup');
        const childSelect = $('#child_ids');

        if (!userId) {
            childrenGroup.hide();
            childSelect.empty();
            return;
        }

        const option = $(`#user_id option[value="${userId}"]`);
        const children = JSON.parse(option.data('children'));

        childSelect.empty();

        if (children.length === 0) {
            childrenGroup.hide();
            alert('This user has no children registered.');
            return;
        }

        children.forEach(function(child) {
            const ageText = child.age ? ` (${child.age} years old)` : '';
            childSelect.append(`<option value="${child.id}">${child.name}${ageText}</option>`);
        });

        childrenGroup.show();
    }

    // Form validation
    $('#assignForm').on('submit', function(e) {
        const userId = $('#user_id').val();
        const selectedChildren = $('#child_ids').val();
        
        if (!userId) {
            e.preventDefault();
            alert('Please select a user.');
            return;
        }

        if (!selectedChildren || selectedChildren.length === 0) {
            e.preventDefault();
            alert('Please select at least one child.');
            return;
        }
    });
});
</script>
@endpush
@endsection 