@extends('layouts.frontend')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-check-circle me-2"></i>Payment Successful!
                    </h4>
                </div>
                <div class="card-body text-center">
                    <div class="mb-4">
                        <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>
                    </div>
                    
                    <h5 class="text-success mb-3">Thank you for your payment!</h5>
                    
                    <div class="alert alert-info">
                        <p class="mb-2"><strong>Booking ID:</strong> {{ $booking->id ?? 'N/A' }}</p>
                        <p class="mb-2"><strong>Class:</strong> {{ $booking->schedule->title ?? $booking->schedule->name ?? 'N/A' }}</p>
                        <p class="mb-2"><strong>Child:</strong> {{ $booking->child->name ?? 'N/A' }}</p>
                        <p class="mb-2"><strong>Amount Paid:</strong> ${{ number_format($payment->amount ?? 0, 2) }}</p>
                        <p class="mb-2"><strong>Payment Method:</strong> {{ ucfirst($payment->payment_method ?? 'N/A') }}</p>
                        <p class="mb-0"><strong>Date:</strong> {{ $payment->created_at ? $payment->created_at->format('M d, Y g:i A') : 'N/A' }}</p>
                    </div>
                    
                    <p class="text-muted mb-4">
                        You will receive a confirmation email shortly. Your booking is now confirmed and you can check in for your class.
                    </p>
                    
                    <div class="d-flex justify-content-center gap-2">
                        <a href="{{ route('bookings.index') }}" class="btn btn-primary">
                            <i class="fas fa-calendar me-2"></i>View My Bookings
                        </a>
                        <a href="{{ route('frontend.schedules.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-calendar-alt me-2"></i>Browse More Classes
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 