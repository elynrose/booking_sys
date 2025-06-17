@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Payments</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.payments.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Add New Payment
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Stat Cards -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card">
                                <div class="card-body bg-info text-white">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h3 class="mb-0">${{ number_format($totalAmount, 2) }}</h3>
                                            <p class="mb-0">Total Payments</p>
                                        </div>
                                        <div class="icon">
                                            <i class="fas fa-money-bill fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card">
                                <div class="card-body bg-warning text-white">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h3 class="mb-0">${{ number_format($pendingAmount, 2) }}</h3>
                                            <p class="mb-0">Pending</p>
                                        </div>
                                        <div class="icon">
                                            <i class="fas fa-clock fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card">
                                <div class="card-body bg-success text-white">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h3 class="mb-0">${{ number_format($refundedAmount, 2) }}</h3>
                                            <p class="mb-0">Refunded</p>
                                        </div>
                                        <div class="icon">
                                            <i class="fas fa-undo fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card">
                                <div class="card-body bg-danger text-white">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h3 class="mb-0">{{ $failedPayments ?? 0 }}</h3>
                                            <p class="mb-0">Failed</p>
                                        </div>
                                        <div class="icon">
                                            <i class="fas fa-times-circle fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Filters -->
                    <form action="{{ route('admin.payments.index') }}" method="GET" class="mb-4">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="start_date">Start Date</label>
                                    <input type="date" class="form-control" id="start_date" name="start_date" value="{{ request('start_date') }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="end_date">End Date</label>
                                    <input type="date" class="form-control" id="end_date" name="end_date" value="{{ request('end_date') }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="status">Status</label>
                                    <select class="form-control" id="status" name="status">
                                        <option value="">All Statuses</option>
                                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                                        <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <div>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-filter"></i> Filter
                                        </button>
                                        <a href="{{ route('admin.payments.index') }}" class="btn btn-secondary">
                                            <i class="fas fa-times"></i> Clear
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>

                    <!-- Payments List -->
                    <div class="list-group">
                        @forelse($payments as $payment)
                            <div class="list-group-item list-group-item-action">
                                <div class="d-flex w-100 justify-content-between align-items-center">
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <h5 class="mb-1">Payment #{{ $payment->id }}</h5>
                                            <span class="badge badge-{{ $payment->status === 'completed' ? 'success' : ($payment->status === 'pending' ? 'warning' : 'danger') }}">
                                                {{ ucfirst($payment->status) }}
                                            </span>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <p class="mb-1">
                                                    <i class="fas fa-calendar mr-2"></i>
                                                    <strong>Booking:</strong> {{ $payment->booking->schedule->title ?? 'N/A' }}
                                                </p>
                                                <p class="mb-1">
                                                    <i class="fas fa-money-bill mr-2"></i>
                                                    <strong>Amount:</strong> ${{ number_format($payment->amount, 2) }}
                                                </p>
                                            </div>
                                            <div class="col-md-6">
                                                <p class="mb-1">
                                                    <i class="fas fa-credit-card mr-2"></i>
                                                    <strong>Payment Method:</strong> {{ $payment->booking->payment_method ?? 'N/A' }}
                                                </p>
                                                <p class="mb-1">
                                                    <i class="fas fa-clock mr-2"></i>
                                                    <strong>Created:</strong> {{ $payment->created_at->format('M d, Y H:i') }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="ml-3">
                                        <div class="btn-group">
                                            <a href="{{ route('admin.payments.edit', $payment) }}" class="btn btn-sm btn-info">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <form action="{{ route('admin.payments.destroy', $payment) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this payment?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="list-group-item">
                                <div class="text-center">No payments found.</div>
                            </div>
                        @endforelse
                    </div>

                    <div class="mt-4">
                        {{ $payments->withQueryString()->links('pagination::bootstrap-4') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 