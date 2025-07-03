@extends('frontend.layouts.frontend')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Edit Profile</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('frontend.profile.update') }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $user->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control @error('email') is-invalid @error" id="email" name="email" value="{{ old('email', $user->email) }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="phone_number" class="form-label">Phone Number</label>
                            <input type="tel" class="form-control @error('phone_number') is-invalid @enderror" id="phone_number" name="phone_number" value="{{ old('phone_number', $user->phone_number) }}" placeholder="+1234567890">
                            <div class="form-text">Required for SMS notifications</div>
                            @error('phone_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="timezone" class="form-label">Timezone</label>
                            <select class="form-select @error('timezone') is-invalid @enderror" id="timezone" name="timezone" required>
                                @foreach(timezone_identifiers_list() as $timezone)
                                    <option value="{{ $timezone }}" {{ old('timezone', $user->timezone ?? 'UTC') == $timezone ? 'selected' : '' }}>
                                        {{ $timezone }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text">Select your local timezone for accurate check-in timing.</div>
                            @error('timezone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- SMS Notification Settings -->
                        <div class="mb-4">
                            <h6 class="mb-3">SMS Notification Settings</h6>
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="sms_notifications_enabled" name="sms_notifications_enabled" value="1" {{ $user->sms_notifications_enabled ? 'checked' : '' }}>
                                    <label class="form-check-label" for="sms_notifications_enabled">
                                        Enable SMS Notifications
                                    </label>
                                    <div class="form-text">Receive important updates via text message</div>
                                </div>
                            </div>

                            <div class="mb-3" id="sms-preferences" style="{{ $user->sms_notifications_enabled ? '' : 'display: none;' }}">
                                <h6 class="mb-2">Notification Types</h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="checkbox" id="booking_created" name="sms_notification_preferences[booking_created]" value="1" {{ $user->wantsSmsNotification('booking_created') ? 'checked' : '' }}>
                                            <label class="form-check-label" for="booking_created">Booking Created</label>
                                        </div>
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="checkbox" id="booking_confirmed" name="sms_notification_preferences[booking_confirmed]" value="1" {{ $user->wantsSmsNotification('booking_confirmed') ? 'checked' : '' }}>
                                            <label class="form-check-label" for="booking_confirmed">Booking Confirmed</label>
                                        </div>
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="checkbox" id="payment_received" name="sms_notification_preferences[payment_received]" value="1" {{ $user->wantsSmsNotification('payment_received') ? 'checked' : '' }}>
                                            <label class="form-check-label" for="payment_received">Payment Received</label>
                                        </div>
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="checkbox" id="class_reminder" name="sms_notification_preferences[class_reminder]" value="1" {{ $user->wantsSmsNotification('class_reminder') ? 'checked' : '' }}>
                                            <label class="form-check-label" for="class_reminder">Class Reminders</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="checkbox" id="booking_cancelled" name="sms_notification_preferences[booking_cancelled]" value="1" {{ $user->wantsSmsNotification('booking_cancelled') ? 'checked' : '' }}>
                                            <label class="form-check-label" for="booking_cancelled">Booking Cancelled</label>
                                        </div>
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="checkbox" id="payment_failed" name="sms_notification_preferences[payment_failed]" value="1" {{ $user->wantsSmsNotification('payment_failed') ? 'checked' : '' }}>
                                            <label class="form-check-label" for="payment_failed">Payment Failed</label>
                                        </div>
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="checkbox" id="class_cancelled" name="sms_notification_preferences[class_cancelled]" value="1" {{ $user->wantsSmsNotification('class_cancelled') ? 'checked' : '' }}>
                                            <label class="form-check-label" for="class_cancelled">Class Cancelled</label>
                                        </div>
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="checkbox" id="class_rescheduled" name="sms_notification_preferences[class_rescheduled]" value="1" {{ $user->wantsSmsNotification('class_rescheduled') ? 'checked' : '' }}>
                                            <label class="form-check-label" for="class_rescheduled">Class Rescheduled</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="current_password" class="form-label">Current Password</label>
                            <input type="password" class="form-control @error('current_password') is-invalid @enderror" id="current_password" name="current_password">
                            <div class="form-text">Required only if changing password.</div>
                            @error('current_password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">New Password</label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password">
                            <div class="form-text">Leave blank to keep current password.</div>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label">Confirm New Password</label>
                            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation">
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                Update Profile
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
    // Add search functionality to timezone select
    const timezoneSelect = document.getElementById('timezone');
    const timezoneOptions = Array.from(timezoneSelect.options);
    
    // Create a search input
    const searchInput = document.createElement('input');
    searchInput.type = 'text';
    searchInput.className = 'form-control mb-2';
    searchInput.placeholder = 'Search timezone...';
    timezoneSelect.parentNode.insertBefore(searchInput, timezoneSelect);
    
    // Add search functionality
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        timezoneOptions.forEach(option => {
            const text = option.text.toLowerCase();
            option.style.display = text.includes(searchTerm) ? '' : 'none';
        });
    });

    // SMS notification preferences toggle
    const smsToggle = document.getElementById('sms_notifications_enabled');
    const smsPreferences = document.getElementById('sms-preferences');

    if (smsToggle && smsPreferences) {
        smsToggle.addEventListener('change', function() {
            if (this.checked) {
                smsPreferences.style.display = 'block';
            } else {
                smsPreferences.style.display = 'none';
            }
        });
    }
});
</script>
@endpush
@endsection 