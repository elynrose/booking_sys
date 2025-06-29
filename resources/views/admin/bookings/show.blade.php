@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        Booking Details
        <a class="btn btn-default float-right" href="{{ route('admin.bookings.index') }}">
            Back to List
        </a>
    </div>

    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <h5 class="mb-3">Schedule Information</h5>
                <p><strong>Schedule:</strong> {{ $booking->schedule->title ?? '' }}</p>
                <p><strong>Status:</strong> {{ $booking->status }}</p>

                <h5 class="mb-3 mt-4">User Information</h5>
                <p><strong>User:</strong> {{ $booking->user->name ?? '' }}</p>
                <p><strong>Child:</strong> {{ $booking->child->name ?? '' }}</p>
            </div>

            <div class="col-md-6">
                <h5 class="mb-3">Payment Information</h5>
                <p><strong>Payment Status:</strong> {{ ucfirst($booking->payment_status) }}</p>
                <p><strong>Is Paid (Database):</strong> {{ $booking->is_paid ? 'Yes' : 'No' }}</p>
                <p><strong>Payment Method:</strong> {{ $booking->payment_method ?? 'None' }}</p>

                <h5 class="mb-3 mt-4">Timestamps</h5>
                <p><strong>Created:</strong> {{ $booking->created_at }}</p>
                <p><strong>Last Updated:</strong> {{ $booking->updated_at }}</p>
            </div>
        </div>
    </div>
</div>

@endsection 