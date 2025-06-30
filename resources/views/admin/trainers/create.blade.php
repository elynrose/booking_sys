@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Add New Trainer</h3>
                </div>
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    
                    <form action="{{ route('admin.trainers.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group">
                            <label for="user_id">User</label>
                            <select name="user_id" id="user_id" class="form-control @error('user_id') is-invalid @enderror" required>
                                <option value="">Select User</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                                @endforeach
                            </select>
                            @error('user_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="profile_picture">Profile Picture</label>
                            <input type="file" name="profile_picture" id="profile_picture" class="form-control-file @error('profile_picture') is-invalid @enderror">
                            <small class="form-text text-muted">Max file size: 1MB. Supported formats: JPEG, PNG, JPG, GIF.</small>
                            @error('profile_picture')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="bio">Bio</label>
                            <textarea name="bio" id="bio" class="form-control @error('bio') is-invalid @enderror" rows="3">{{ old('bio') }}</textarea>
                            @error('bio')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="payment_method">Payment Method</label>
                            <select name="payment_method" id="payment_method" class="form-control @error('payment_method') is-invalid @enderror" required>
                                <option value="">Select Payment Method</option>
                                <option value="check">Check</option>
                                <option value="paypal">PayPal</option>
                                <option value="venmo">Venmo</option>
                                <option value="cashapp">Cash App</option>
                            </select>
                            @error('payment_method')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="payment_details">Payment Details</label>
                            <input type="text" name="payment_details" id="payment_details" class="form-control @error('payment_details') is-invalid @enderror" value="{{ old('payment_details') }}" required>
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
                                    <option value="{{ $schedule->id }}">{{ $schedule->title }}</option>
                                @endforeach
                            </select>
                            @error('schedules')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary">Create Trainer</button>
                        <a href="{{ route('admin.trainers.index') }}" class="btn btn-secondary">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 