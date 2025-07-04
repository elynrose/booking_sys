@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Edit Trainer</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.trainers.update', $trainer) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="form-group">
                            <label>User</label>
                            <input type="text" class="form-control" value="{{ $trainer->user->name }} ({{ $trainer->user->email }})" disabled>
                        </div>

                        <div class="form-group">
                            <label for="profile_picture">Profile Picture</label>
                            @if($trainer->profile_picture)
                                <div class="mb-2">
                                    <img src="{{ Storage::url($trainer->profile_picture) }}" alt="{{ $trainer->user->name }}" class="img-thumbnail trainer-image" style="max-width: 150px;">
                                </div>
                            @else
                                <div class="mb-2">
                                    <div class="img-thumbnail bg-light d-flex align-items-center justify-content-center" style="max-width: 150px; min-height: 150px;">
                                        <div class="text-center text-muted">
                                            <i class="fas fa-user-tie fa-2x mb-2"></i>
                                            <div class="small">No Photo</div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            <input type="file" name="profile_picture" id="profile_picture" class="form-control-file @error('profile_picture') is-invalid @enderror">
                            @error('profile_picture')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="bio">Bio</label>
                            <textarea name="bio" id="bio" class="form-control @error('bio') is-invalid @enderror" rows="3">{{ old('bio', $trainer->bio) }}</textarea>
                            @error('bio')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="payment_method">Payment Method</label>
                            <select name="payment_method" id="payment_method" class="form-control @error('payment_method') is-invalid @enderror" required>
                                <option value="">Select Payment Method</option>
                                <option value="check" {{ old('payment_method', $trainer->payment_method) == 'check' ? 'selected' : '' }}>Check</option>
                                <option value="paypal" {{ old('payment_method', $trainer->payment_method) == 'paypal' ? 'selected' : '' }}>PayPal</option>
                                <option value="venmo" {{ old('payment_method', $trainer->payment_method) == 'venmo' ? 'selected' : '' }}>Venmo</option>
                                <option value="cashapp" {{ old('payment_method', $trainer->payment_method) == 'cashapp' ? 'selected' : '' }}>Cash App</option>
                            </select>
                            @error('payment_method')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="payment_details">Payment Details</label>
                            <input type="text" name="payment_details" id="payment_details" class="form-control @error('payment_details') is-invalid @enderror" value="{{ old('payment_details', $trainer->payment_details) }}" required>
                            <small class="form-text text-muted">
                                For PayPal: Enter email address<br>
                                For Venmo/Cash App: Enter username<br>
                                For Check: Enter mailing address
                            </small>
                            @error('payment_details')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="schedules">Assign Schedules</label>
                            <select name="schedules[]" id="schedules" class="form-control @error('schedules') is-invalid @enderror" multiple>
                                @foreach($schedules as $schedule)
                                    <option value="{{ $schedule->id }}" {{ in_array($schedule->id, $trainer->schedules->pluck('id')->toArray()) ? 'selected' : '' }}>
                                        {{ $schedule->title }}
                                    </option>
                                @endforeach
                            </select>
                            @error('schedules')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" {{ old('is_active', $trainer->is_active) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="is_active">Active</label>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">Update Trainer</button>
                        <a href="{{ route('admin.trainers.index') }}" class="btn btn-secondary">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 