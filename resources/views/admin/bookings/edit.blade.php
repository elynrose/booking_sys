@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Edit Booking</h1>
        <a href="{{ route('admin.bookings.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Bookings
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.bookings.update', $booking) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="user_id" class="form-label">User</label>
                            <select class="form-select form-control @error('user_id') is-invalid @enderror" 
                                    id="user_id" 
                                    name="user_id" 
                                    required>
                                <option value="">Select User</option>
                                @foreach($users as $user)
                                    @if($user && $user->name)
                                        <option value="{{ $user->id }}" 
                                                {{ old('user_id', $booking->user_id) == $user->id ? 'selected' : '' }}>
                                            {{ $user->name }}
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                            @error('user_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="schedule_id" class="form-label">Schedule</label>
                            <select class="form-select form-control @error('schedule_id') is-invalid @enderror" 
                                    id="schedule_id" 
                                    name="schedule_id" 
                                    required>
                                <option value="">Select Schedule</option>
                                @foreach($schedules as $schedule)
                                    @if($schedule && $schedule->trainer && $schedule->trainer->user && $schedule->trainer->user->name)
                                        <option value="{{ $schedule->id }}" 
                                                {{ old('schedule_id', $booking->schedule_id) == $schedule->id ? 'selected' : '' }}>
                                            {{ $schedule->title }} ({{ $schedule->trainer->user->name }})
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                            @error('schedule_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select form-control @error('status') is-invalid @enderror" 
                                    id="status" 
                                    name="status" 
                                    required>
                                <option value="pending" {{ old('status', $booking->status) == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="confirmed" {{ old('status', $booking->status) == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                                <option value="cancelled" {{ old('status', $booking->status) == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="payment_status" class="form-label">Payment Status</label>
                            <select class="form-select form-control @error('payment_status') is-invalid @enderror" 
                                    id="payment_status" 
                                    name="payment_status" 
                                    required>
                                <option value="pending" {{ old('payment_status', $booking->payment_status) == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="paid" {{ old('payment_status', $booking->payment_status) == 'paid' ? 'selected' : '' }}>Paid</option>
                                <option value="refunded" {{ old('payment_status', $booking->payment_status) == 'refunded' ? 'selected' : '' }}>Refunded</option>
                            </select>
                            @error('payment_status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="is_paid" class="form-label">Payment Status</label>
                            <div class="form-check">
                                <input type="checkbox" 
                                       class="form-check-input @error('is_paid') is-invalid @enderror" 
                                       id="is_paid" 
                                       name="is_paid" 
                                       value="1" 
                                       {{ old('is_paid', $booking->is_paid) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_paid">Mark as Paid</label>
                                @error('is_paid')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3 payment-method-field" style="display: none;">
                            <label for="payment_method" class="form-label">Payment Method</label>
                            <select class="form-select form-control @error('payment_method') is-invalid @enderror" 
                                    id="payment_method" 
                                    name="payment_method">
                                <option value="">Select Payment Method</option>
                                <option value="zelle" {{ old('payment_method', $booking->payment_method) == 'zelle' ? 'selected' : '' }}>Zelle</option>
                                <option value="cash" {{ old('payment_method', $booking->payment_method) == 'cash' ? 'selected' : '' }}>Cash</option>
                                <option value="credit_card" {{ old('payment_method', $booking->payment_method) == 'credit_card' ? 'selected' : '' }}>Credit Card</option>
                            </select>
                            @error('payment_method')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" 
                                      id="notes" 
                                      name="notes" 
                                      rows="3">{{ old('notes', $booking->notes) }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Booking
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const isPaidCheckbox = document.getElementById('is_paid');
    const paymentMethodField = document.querySelector('.payment-method-field');
    const paymentMethodSelect = document.getElementById('payment_method');

    function togglePaymentMethod() {
        if (isPaidCheckbox.checked) {
            paymentMethodField.style.display = 'block';
            paymentMethodSelect.required = true;
        } else {
            paymentMethodField.style.display = 'none';
            paymentMethodSelect.required = false;
        }
    }

    isPaidCheckbox.addEventListener('change', togglePaymentMethod);
    togglePaymentMethod(); // Initial state
});
</script>
@endpush
@endsection 