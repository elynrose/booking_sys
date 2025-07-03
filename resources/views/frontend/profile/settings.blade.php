@extends('layouts.frontend')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-cog me-2"></i>
                        Profile Settings
                    </h4>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <!-- Profile Information -->
                    <form action="{{ route('frontend.profile.settings.update') }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <h5 class="mb-3">Personal Information</h5>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label">Full Name</label>
                                <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $user->name) }}" required>
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="email" name="email" value="{{ old('email', $user->email) }}" required>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="phone_number" class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" id="phone_number" name="phone_number" value="{{ old('phone_number', $user->phone_number) }}" placeholder="+1234567890">
                                <div class="form-text">Required for SMS notifications</div>
                            </div>
                        </div>

                        <!-- SMS Notification Settings -->
                        <h5 class="mb-3">SMS Notification Settings</h5>
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="sms_notifications_enabled" name="sms_notifications_enabled" value="1" {{ $user->sms_notifications_enabled ? 'checked' : '' }}>
                                <label class="form-check-label" for="sms_notifications_enabled">
                                    Enable SMS Notifications
                                </label>
                                <div class="form-text">Receive important updates via text message</div>
                            </div>
                        </div>

                        <div class="mb-4" id="sms-preferences" style="{{ $user->sms_notifications_enabled ? '' : 'display: none;' }}">
                            <h6 class="mb-3">Notification Types</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="booking_created" name="sms_notification_preferences[booking_created]" value="1" {{ $user->wantsSmsNotification('booking_created') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="booking_created">
                                            Booking Created
                                        </label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="booking_confirmed" name="sms_notification_preferences[booking_confirmed]" value="1" {{ $user->wantsSmsNotification('booking_confirmed') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="booking_confirmed">
                                            Booking Confirmed
                                        </label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="booking_cancelled" name="sms_notification_preferences[booking_cancelled]" value="1" {{ $user->wantsSmsNotification('booking_cancelled') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="booking_cancelled">
                                            Booking Cancelled
                                        </label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="payment_received" name="sms_notification_preferences[payment_received]" value="1" {{ $user->wantsSmsNotification('payment_received') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="payment_received">
                                            Payment Received
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="payment_failed" name="sms_notification_preferences[payment_failed]" value="1" {{ $user->wantsSmsNotification('payment_failed') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="payment_failed">
                                            Payment Failed
                                        </label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="class_reminder" name="sms_notification_preferences[class_reminder]" value="1" {{ $user->wantsSmsNotification('class_reminder') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="class_reminder">
                                            Class Reminders
                                        </label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="class_cancelled" name="sms_notification_preferences[class_cancelled]" value="1" {{ $user->wantsSmsNotification('class_cancelled') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="class_cancelled">
                                            Class Cancelled
                                        </label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="class_rescheduled" name="sms_notification_preferences[class_rescheduled]" value="1" {{ $user->wantsSmsNotification('class_rescheduled') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="class_rescheduled">
                                            Class Rescheduled
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>
                                Update Profile Settings
                            </button>
                        </div>
                    </form>

                    <hr class="my-4">

                    <!-- Change Password -->
                    <h5 class="mb-3">Change Password</h5>
                    <form action="{{ route('frontend.profile.settings.password') }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label for="current_password" class="form-label">Current Password</label>
                                <input type="password" class="form-control" id="current_password" name="current_password" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="password" class="form-label">New Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <div class="col-md-6">
                                <label for="password_confirmation" class="form-label">Confirm New Password</label>
                                <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
                            </div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-outline-primary">
                                <i class="fas fa-key me-2"></i>
                                Change Password
                            </button>
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
    const smsToggle = document.getElementById('sms_notifications_enabled');
    const smsPreferences = document.getElementById('sms-preferences');

    smsToggle.addEventListener('change', function() {
        if (this.checked) {
            smsPreferences.style.display = 'block';
        } else {
            smsPreferences.style.display = 'none';
        }
    });
});
</script>
@endpush
@endsection 