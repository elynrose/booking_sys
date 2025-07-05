@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Create Schedule</h1>
        <a href="{{ route('admin.schedules.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Schedules
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.schedules.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="title" class="form-label">Title</label>
                            <input type="text" 
                                   class="form-control @error('title') is-invalid @enderror" 
                                   id="title" 
                                   name="title" 
                                   value="{{ old('title') }}" 
                                   required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" 
                                      name="description" 
                                      rows="3" 
                                      required>{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="location" class="form-label">Location</label>
                            <input type="text" 
                                   class="form-control @error('location') is-invalid @enderror" 
                                   id="location" 
                                   name="location" 
                                   value="{{ old('location') }}" 
                                   placeholder="e.g., Main Gym, Studio A, Outdoor Field">
                            @error('location')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="photo" class="form-label">Schedule Photo</label>
                            <input type="file" 
                                   class="form-control @error('photo') is-invalid @enderror" 
                                   id="photo" 
                                   name="photo"
                                   accept="image/*">
                            <div class="form-text">Upload a photo for this schedule (max 2MB, jpeg, png, jpg, gif)</div>
                            @error('photo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="trainer_id" class="form-label">Trainer</label>
                            <select class="form-select form-control @error('trainer_id') is-invalid @enderror" 
                                    id="trainer_id" 
                                    name="trainer_id" 
                                    required>
                                <option value="">Select Trainer</option>
                                @foreach($trainers as $trainer)
                                    <option value="{{ $trainer->user->id }}" 
                                            {{ old('trainer_id') == $trainer->user->id ? 'selected' : '' }}>
                                        {{ $trainer->user->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('trainer_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="category_id" class="form-label">Category</label>
                            <select class="form-select form-control @error('category_id') is-invalid @enderror" 
                                    id="category_id" 
                                    name="category_id" 
                                    required>
                                <option value="">Select Category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" 
                                            {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="type" class="form-label">Class Type</label>
                            <select class="form-select form-control @error('type') is-invalid @enderror" 
                                    id="type" 
                                    name="type" 
                                    required>
                                <option value="group" {{ old('type', 'group') == 'group' ? 'selected' : '' }}>Group Class</option>
                                <option value="private" {{ old('type') == 'private' ? 'selected' : '' }}>Private/Individual Training</option>
                            </select>
                            @error('type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        {{-- Trainer Availability Dropdown (hidden by default) --}}
                        <div class="mb-3" id="trainer-availability-group" style="display:none;">
                            <label for="trainer-availability-select" class="form-label">Trainer Available Dates</label>
                            <select class="form-select form-control" id="trainer-availability-select">
                                <option value="">Select an available date...</option>
                            </select>
                            <div class="form-text">Selecting a date will auto-fill the date and time fields below.</div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="start_date" class="form-label">Start Date</label>
                                    <input type="date" 
                                           class="form-control @error('start_date') is-invalid @enderror" 
                                           id="start_date" 
                                           name="start_date" 
                                           value="{{ old('start_date') }}" 
                                           required>
                                    @error('start_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="end_date" class="form-label">End Date</label>
                                    <input type="date" 
                                           class="form-control @error('end_date') is-invalid @enderror" 
                                           id="end_date" 
                                           name="end_date" 
                                           value="{{ old('end_date') }}" 
                                           required>
                                    @error('end_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="start_time" class="form-label">Start Time</label>
                                    <input type="time" 
                                           class="form-control @error('start_time') is-invalid @enderror" 
                                           id="start_time" 
                                           name="start_time" 
                                           value="{{ old('start_time') }}" 
                                           required>
                                    @error('start_time')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="end_time" class="form-label">End Time</label>
                                    <input type="time" 
                                           class="form-control @error('end_time') is-invalid @enderror" 
                                           id="end_time" 
                                           name="end_time" 
                                           value="{{ old('end_time') }}" 
                                           required>
                                    @error('end_time')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="max_participants" class="form-label">Max Participants</label>
                                    <input type="number" 
                                           class="form-control @error('max_participants') is-invalid @enderror" 
                                           id="max_participants" 
                                           name="max_participants" 
                                           value="{{ old('max_participants') }}" 
                                           min="1" 
                                           required>
                                    @error('max_participants')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="price" class="form-label">Price</label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" 
                                               class="form-control @error('price') is-invalid @enderror" 
                                               id="price" 
                                               name="price" 
                                               value="{{ old('price') }}" 
                                               step="0.01" 
                                               min="0" 
                                               required>
                                    </div>
                                    @error('price')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input @error('is_discounted') is-invalid @enderror" 
                                               type="checkbox" 
                                               id="is_discounted" 
                                               name="is_discounted" 
                                               value="1" 
                                               {{ old('is_discounted') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_discounted">
                                            Apply Discount
                                        </label>
                                        <div class="form-text">Check this to enable discount pricing for this schedule.</div>
                                        @error('is_discounted')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="discount_percentage" class="form-label">Discount Percentage</label>
                                    <div class="input-group">
                                        <input type="number" 
                                               class="form-control @error('discount_percentage') is-invalid @enderror" 
                                               id="discount_percentage" 
                                               name="discount_percentage" 
                                               value="{{ old('discount_percentage') }}" 
                                               step="0.01" 
                                               min="0" 
                                               max="100">
                                        <span class="input-group-text">%</span>
                                    </div>
                                    <div class="form-text">Enter the discount percentage (e.g., 20 for 20% off)</div>
                                    @error('discount_percentage')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select form-control @error('status') is-invalid @enderror" 
                                    id="status" 
                                    name="status" 
                                    required>
                                <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input @error('allow_unlimited_bookings') is-invalid @enderror" 
                                       type="checkbox" 
                                       id="allow_unlimited_bookings" 
                                       name="allow_unlimited_bookings" 
                                       value="1" 
                                       {{ old('allow_unlimited_bookings') ? 'checked' : '' }}>
                                <label class="form-check-label" for="allow_unlimited_bookings">
                                    Allow Unlimited Bookings
                                </label>
                                <div class="form-text">When checked, users can check in unlimited times for this schedule without session limits.</div>
                                @error('allow_unlimited_bookings')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Create Schedule
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@section('scripts')
<script>
$(document).ready(function() {
    console.log('Trainer availability script loaded');
    
    function resetAvailabilityDropdown() {
        console.log('Resetting availability dropdown');
        $('#trainer-availability-group').hide();
        $('#trainer-availability-select').empty().append('<option value="">Select an available date...</option>');
    }
    
    function populateAvailabilityDropdown(availabilities) {
        console.log('Populating dropdown with availabilities:', availabilities);
        resetAvailabilityDropdown();
        if (availabilities.length === 0) {
            console.log('No availabilities found');
            return;
        }
        availabilities.forEach(function(a) {
            var label = moment(a.date).format('MMM D, YYYY (dddd)') + ' ' +
                moment(a.start_time, 'HH:mm:ss').format('h:mm A') + ' - ' +
                moment(a.end_time, 'HH:mm:ss').format('h:mm A');
            $('#trainer-availability-select').append('<option value="'+a.id+'" data-date="'+a.date+'" data-start_time="'+a.start_time+'" data-end_time="'+a.end_time+'">'+label+'</option>');
        });
        $('#trainer-availability-group').show();
        console.log('Dropdown populated and shown');
    }
    
    $('#trainer_id').on('change', function() {
        var trainerId = $(this).val();
        console.log('Trainer selected:', trainerId);
        resetAvailabilityDropdown();
        if (!trainerId) {
            console.log('No trainer selected');
            return;
        }
        
        console.log('Making AJAX request to fetch availabilities...');
        $.get('/admin/trainer-availability/ajax/trainer-availabilities', {trainer_id: trainerId})
            .done(function(data) {
                console.log('AJAX response received:', data);
                if (data && data.availabilities && Array.isArray(data.availabilities)) {
                    populateAvailabilityDropdown(data.availabilities);
                } else if (data && Array.isArray(data)) {
                    populateAvailabilityDropdown(data);
                } else {
                    console.log('Invalid data format received');
                }
            })
            .fail(function(xhr, status, error) {
                console.error('AJAX request failed:', status, error);
                console.error('Response:', xhr.responseText);
            });
    });
    
    $('#trainer-availability-select').on('change', function() {
        var selected = $(this).find('option:selected');
        console.log('Availability selected:', selected.val());
        if (!selected.val()) return;
        var date = selected.data('date');
        var startTime = selected.data('start_time');
        var endTime = selected.data('end_time');
        
        // Format the date to YYYY-MM-DD format
        var formattedDate = moment(date).format('YYYY-MM-DD');
        
        console.log('Filling fields with:', {date: date, formattedDate: formattedDate, startTime: startTime, endTime: endTime});
        $('#start_date').val(formattedDate);
        $('#end_date').val(formattedDate);
        $('#start_time').val(startTime);
        $('#end_time').val(endTime);
    });
});
</script>
@endsection
@endsection 