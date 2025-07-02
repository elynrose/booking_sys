@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="card-title">Payment Details</h3>
                        <div>
                            <a href="{{ route('admin.payments.edit', $payment) }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <a href="{{ route('admin.payments.index') }}" class="btn btn-secondary btn-sm">
                                <i class="fas fa-arrow-left"></i> Back to Payments
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Payment Information</h5>
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Payment ID:</strong></td>
                                    <td>#{{ $payment->id }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Amount:</strong></td>
                                    <td>${{ number_format($payment->amount, 2) }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Status:</strong></td>
                                    <td>
                                        <span class="badge badge-{{
                                            $payment->status === 'paid' ? 'success' :
                                            ($payment->status === 'pending' ? 'warning' :
                                            ($payment->status === 'refunded' ? 'info' : 'danger'))
                                        }}">
                                            {{ ucfirst($payment->status) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Payment Method:</strong></td>
                                    <td>{{ ucfirst($payment->payment_method ?? 'N/A') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Created:</strong></td>
                                    <td>{{ $payment->created_at->format('M d, Y H:i') }}</td>
                                </tr>
                                @if($payment->paid_at)
                                <tr>
                                    <td><strong>Paid At:</strong></td>
                                    <td>{{ $payment->paid_at->format('M d, Y H:i') }}</td>
                                </tr>
                                @endif
                                @if($payment->description)
                                <tr>
                                    <td><strong>Description:</strong></td>
                                    <td>{{ $payment->description }}</td>
                                </tr>
                                @endif
                            </table>
                        </div>
                        
                        <div class="col-md-6">
                            <h5>Booking Information</h5>
                            @if($payment->booking)
                                <table class="table table-borderless">
                                    <tr>
                                        <td><strong>Booking ID:</strong></td>
                                        <td>#{{ $payment->booking->id }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Customer:</strong></td>
                                        <td>{{ $payment->booking->user->name }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Customer Email:</strong></td>
                                        <td>{{ $payment->booking->user->email }}</td>
                                    </tr>
                                    @if($payment->booking->child)
                                    <tr>
                                        <td><strong>Child:</strong></td>
                                        <td>{{ $payment->booking->child->name }}</td>
                                    </tr>
                                    @endif
                                    <tr>
                                        <td><strong>Schedule:</strong></td>
                                        <td>
                                            @if($payment->booking->schedule)
                                                {{ $payment->booking->schedule->title }}
                                                @if($payment->booking->schedule->deleted_at)
                                                    <span class="badge badge-warning ml-1">Deleted</span>
                                                @endif
                                            @else
                                                <span class="text-danger">Schedule Not Found</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>Trainer:</strong></td>
                                        <td>
                                            @if($payment->booking->schedule && $payment->booking->schedule->trainer)
                                                {{ optional($payment->booking->schedule->trainer->user)->name ?? 'Unnamed Trainer' }}
                                            @else
                                                No Trainer
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>Schedule Date:</strong></td>
                                        <td>
                                            @if($payment->booking->schedule)
                                                {{ optional($payment->booking->schedule->start_date)->format('M d, Y') ?? 'N/A' }}
                                            @else
                                                <span class="text-danger">N/A</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>Schedule Time:</strong></td>
                                        <td>
                                            @if($payment->booking->schedule)
                                                {{ optional($payment->booking->schedule->start_time)->format('h:i A') ?? 'N/A' }} - 
                                                {{ optional($payment->booking->schedule->end_time)->format('h:i A') ?? 'N/A' }}
                                            @else
                                                <span class="text-danger">N/A</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>Booking Status:</strong></td>
                                        <td>
                                            <span class="badge badge-{{ $payment->booking->status === 'confirmed' ? 'success' : ($payment->booking->status === 'cancelled' ? 'danger' : 'warning') }}">
                                                {{ ucfirst($payment->booking->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>Payment Status:</strong></td>
                                        <td>
                                            <span class="badge badge-{{ $payment->booking->payment_status === 'paid' ? 'success' : ($payment->booking->payment_status === 'refunded' ? 'danger' : 'warning') }}">
                                                {{ ucfirst($payment->booking->payment_status) }}
                                            </span>
                                        </td>
                                    </tr>
                                </table>
                            @else
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    No booking associated with this payment.
                                </div>
                            @endif
                        </div>
                    </div>

                    @if($payment->status !== 'paid')
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">Quick Actions</h5>
                                </div>
                                <div class="card-body">
                                    <form action="{{ route('admin.payments.mark-as-paid', $payment) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-success" onclick="return confirm('Are you sure you want to mark this payment as paid?')">
                                            <i class="fas fa-check"></i> Mark as Paid
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 