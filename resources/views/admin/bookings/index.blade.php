@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Bookings</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.bookings.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Add New Booking
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Counters -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card">
                                <div class="card-body bg-info text-white">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h3 class="mb-0">{{ $totalBookings }}</h3>
                                            <p class="mb-0">Total Bookings</p>
                                        </div>
                                        <div class="icon">
                                            <i class="fas fa-calendar-check fa-2x"></i>
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
                                            <h3 class="mb-0">{{ $confirmedBookings }}</h3>
                                            <p class="mb-0">Confirmed Bookings</p>
                                        </div>
                                        <div class="icon">
                                            <i class="fas fa-check-circle fa-2x"></i>
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
                                            <h3 class="mb-0">{{ $unpaidBookings }}</h3>
                                            <p class="mb-0">Unpaid Bookings</p>
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
                                <div class="card-body bg-danger text-white">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h3 class="mb-0">{{ $cancelledBookings }}</h3>
                                            <p class="mb-0">Cancelled Bookings</p>
                                        </div>
                                        <div class="icon">
                                            <i class="fas fa-times-circle fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Date Filters -->
                    <form action="{{ route('admin.bookings.index') }}" method="GET" class="mb-4">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="start_date">Start Date</label>
                                    <input type="date" class="form-control" id="start_date" name="start_date" 
                                           value="{{ request('start_date') }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="end_date">End Date</label>
                                    <input type="date" class="form-control" id="end_date" name="end_date" 
                                           value="{{ request('end_date') }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="trainer_id">Trainer</label>
                                    <select class="form-control" id="trainer_id" name="trainer_id">
                                        <option value="">All Trainers</option>
                                        @foreach($trainers as $trainer)
                                            <option value="{{ $trainer->id }}" {{ request('trainer_id') == $trainer->id ? 'selected' : '' }}>
                                                {{ $trainer->user->name }}
                                            </option>
                                        @endforeach
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
                                        <a href="{{ route('admin.bookings.index') }}" class="btn btn-secondary">
                                            <i class="fas fa-times"></i> Clear
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>

                    <!-- Bookings List -->
                    <div class="list-group">
                        @forelse($bookings as $booking)
                            <div class="list-group-item list-group-item-action">
                                <div class="d-flex w-100 justify-content-between align-items-center">
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <h5 class="mb-1">Booking #{{ $booking->id }}</h5>
                                            <div>
                                                <span class="badge badge-{{ $booking->status === 'confirmed' ? 'success' : ($booking->status === 'cancelled' ? 'danger' : 'warning') }} mr-2">
                                                    {{ ucfirst($booking->status) }}
                                                </span>
                                                <span class="badge badge-{{ $booking->payments->where('status', 'refunded')->count() > 0 ? 'danger' : ($booking->is_paid ? 'success' : 'warning') }}">
                                                    {{ $booking->payments->where('status', 'refunded')->count() > 0 ? 'Refunded' : ($booking->is_paid ? 'Paid' : 'Unpaid') }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <p class="mb-1">
                                                    <i class="fas fa-user mr-2"></i>
                                                    <strong>User:</strong> {{ $booking->user->name }}
                                                </p>
                                                <p class="mb-1">
                                                    <i class="fas fa-calendar mr-2"></i>
                                                    <strong>Schedule:</strong> {{ $booking->schedule->title }}
                                                </p>
                                                <p class="mb-1">
                                                    <i class="fas fa-user-tie mr-2"></i>
                                                    <strong>Trainer:</strong> 
                                                    @if($booking->schedule && $booking->schedule->trainer)
                                                        {{ optional($booking->schedule->trainer->user)->name ?? 'Unnamed Trainer' }}
                                                    @else
                                                        No Trainer
                                                    @endif
                                                </p>
                                            </div>
                                            <div class="col-md-6">
                                                <p class="mb-1">
                                                    <i class="fas fa-calendar-day mr-2"></i>
                                                    <strong>Date:</strong> {{ optional($booking->schedule->start_date)->format('M d, Y') ?? 'N/A' }}
                                                </p>
                                                <p class="mb-1">
                                                    <i class="fas fa-clock mr-2"></i>
                                                    <strong>Time:</strong> {{ optional($booking->schedule->start_time)->format('h:i A') ?? 'N/A' }} - 
                                                    {{ optional($booking->schedule->end_time)->format('h:i A') ?? 'N/A' }}
                                                </p>
                                                <p class="mb-1">
                                                    <i class="fas fa-dollar-sign mr-2"></i>
                                                    <strong>Price:</strong> ${{ number_format($booking->schedule->price, 2) }}
                                                </p>
                                                <p class="mb-1">
                                                    <i class="fas fa-clock mr-2"></i>
                                                    <strong>Method:</strong> {{ $booking->payment_method ?? 'None' }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="ml-3">
                                        <div>
                                        @if(!$booking->is_paid)
                                                <form action="{{ route('admin.bookings.mark-as-paid', $booking) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Are you sure you want to mark this booking as paid?')">
                                                        <i class="fas fa-check"></i> Paid
                                                    </button>
                                                </form>
                                            @endif
                                            <a href="{{ route('admin.bookings.show', $booking) }}" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.bookings.edit', $booking) }}" class="btn btn-sm btn-primary">
                                                <i class="fas fa-edit"></i> 
                                            </a>
                                          
                                            <form action="{{ route('admin.bookings.destroy', $booking) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this booking?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="list-group-item">
                                <div class="text-center">No bookings found.</div>
                            </div>
                        @endforelse
                    </div>

                    <div class="mt-4">
                        {{ $bookings->withQueryString()->links('pagination::bootstrap-4') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 