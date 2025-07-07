@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <!-- Date Range Filter -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.home') }}" class="row align-items-end">
                <div class="col-md-4">
                    <label for="start_date">Start Date</label>
                    <input type="date" class="form-control" id="start_date" name="start_date" value="{{ $startDate->format('Y-m-d') }}">
                </div>
                <div class="col-md-4">
                    <label for="end_date">End Date</label>
                    <input type="date" class="form-control" id="end_date" name="end_date" value="{{ $endDate->format('Y-m-d') }}">
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="{{ route('admin.live-dashboard') }}" class="btn btn-outline-success ml-2 live-dashboard-link" title="Live Dashboard">
                        <span class="live-flash-icon" style="display:inline-block;vertical-align:middle;margin-right:4px;">
                            <i class="fas fa-broadcast-tower"></i>
                        </span>
                        Live Dashboard
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Revenue</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">${{ number_format($totalRevenue, 2) }}</div>
                            <div class="text-xs text-muted mt-1">
                                Last 30 days: ${{ number_format($dateRangeRevenue, 2) }}
                                @if($totalDiscounts > 0)
                                    <br><span class="text-danger">Discounts: -${{ number_format($totalDiscounts, 2) }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Bookings</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalBookings }}</div>
                            <div class="text-xs text-muted mt-1">Last 30 days: {{ $dateRangeBookings }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-check fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Total Users</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalUsers }}</div>
                            <div class="text-xs text-muted mt-1">Last 30 days: {{ $dateRangeUsers }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Total Trainers</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalTrainers }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-tie fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Revenue Statistics Row -->
    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Potential Revenue</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">${{ number_format($potentialRevenue, 2) }}</div>
                            <div class="text-xs text-muted mt-1">Without discounts</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chart-line fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Total Discounts</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">${{ number_format($totalDiscounts, 2) }}</div>
                            <div class="text-xs text-muted mt-1">Last 30 days: ${{ number_format($dateRangeDiscounts, 2) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-tags fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Realized Revenue</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">${{ number_format($realizedRevenue, 2) }}</div>
                            <div class="text-xs text-muted mt-1">Already received</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Unrealized Revenue</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">${{ number_format($unrealizedRevenue, 2) }}</div>
                            <div class="text-xs text-muted mt-1">Future payments</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row">
        <!-- Revenue Chart -->
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Revenue Overview</h6>
                </div>
                <div class="card-body">
                    <div class="chart-area">
                        <canvas id="revenueChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bookings Chart -->
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Booking Status</h6>
                </div>
                <div class="card-body">
                    <div class="chart-pie">
                        <canvas id="bookingStatusChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Row -->
    <div class="row">
        <!-- Revenue by Category -->
        <div class="col-md-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Revenue by Category</h6>
                    <a href="{{ route('admin.categories.index') }}" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-external-link-alt me-1"></i>View More
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Category</th>
                                    <th>Actual Revenue</th>
                                    <th>Potential Revenue</th>
                                    <th>Discounts</th>
                                    <th>Payments</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($revenueByCategory->take(5) as $category)
                                <tr>
                                    <td>{{ $category->name ?? 'Uncategorized' }}</td>
                                    <td>${{ number_format($category->actual_revenue, 2) }}</td>
                                    <td>${{ number_format($category->potential_revenue, 2) }}</td>
                                    <td>
                                        @if($category->total_discounts > 0)
                                            <span class="text-danger">-${{ number_format($category->total_discounts, 2) }}</span>
                                        @else
                                            <span class="text-muted">$0.00</span>
                                        @endif
                                    </td>
                                    <td>{{ $category->payment_count }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        @if($revenueByCategory->count() > 5)
                            <div class="text-center mt-3">
                                <small class="text-muted">Showing 5 of {{ $revenueByCategory->count() }} categories</small>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Revenue by Trainer -->
        <div class="col-md-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Revenue by Trainer</h6>
                    <a href="{{ route('admin.trainers.index') }}" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-external-link-alt me-1"></i>View More
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Trainer</th>
                                    <th>Actual Revenue</th>
                                    <th>Potential Revenue</th>
                                    <th>Discounts</th>
                                    <th>Payments</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($revenueByTrainer->take(5) as $trainer)
                                <tr>
                                    <td>{{ $trainer->name ?? 'Unnamed Trainer' }}</td>
                                    <td>${{ number_format($trainer->actual_revenue, 2) }}</td>
                                    <td>${{ number_format($trainer->potential_revenue, 2) }}</td>
                                    <td>
                                        @if($trainer->total_discounts > 0)
                                            <span class="text-danger">-${{ number_format($trainer->total_discounts, 2) }}</span>
                                        @else
                                            <span class="text-muted">$0.00</span>
                                        @endif
                                    </td>
                                    <td>{{ $trainer->payment_count }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        @if($revenueByTrainer->count() > 5)
                            <div class="text-center mt-3">
                                <small class="text-muted">Showing 5 of {{ $revenueByTrainer->count() }} trainers</small>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activities Row -->
    <div class="row">
        <!-- Recent Bookings -->
        <div class="col-md-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Bookings</h6>
                    <a href="{{ route('admin.bookings.index') }}" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-external-link-alt me-1"></i>View More
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Schedule</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentBookings->take(5) as $booking)
                                <tr>
                                    <td>{{ $booking->user->name ?? 'Unknown User' }}</td>
                                    <td>
                                        @if($booking->schedule)
                                            {{ $booking->schedule->category->name ?? 'Uncategorized' }} 
                                            with {{ optional($booking->schedule->trainer)->name ?? 'Unnamed Trainer' }}
                                        @else
                                            Deleted Schedule
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ $booking->status === 'confirmed' ? 'success' : ($booking->status === 'pending' ? 'warning' : 'danger') }}">
                                            {{ ucfirst($booking->status) }}
                                        </span>
                                    </td>
                                    <td>{{ $booking->created_at->format('M d, Y') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        @if($recentBookings->count() > 5)
                            <div class="text-center mt-3">
                                <small class="text-muted">Showing 5 of {{ $recentBookings->count() }} recent bookings</small>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Payments -->
        <div class="col-md-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Payments</h6>
                    <a href="{{ route('admin.payments.index') }}" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-external-link-alt me-1"></i>View More
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentPayments->take(5) as $payment)
                                <tr>
                                    <td>{{ optional($payment->booking->user)->name ?? 'Unknown User' }}</td>
                                    <td>${{ number_format($payment->amount, 2) }}</td>
                                    <td>
                                        <span class="badge badge-{{ $payment->status === 'completed' ? 'success' : ($payment->status === 'pending' ? 'warning' : 'danger') }}">
                                            {{ ucfirst($payment->status) }}
                                        </span>
                                    </td>
                                    <td>{{ $payment->created_at->format('M d, Y') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        @if($recentPayments->count() > 5)
                            <div class="text-center mt-3">
                                <small class="text-muted">Showing 5 of {{ $recentPayments->count() }} recent payments</small>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Recommendations and Responses Row -->
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Recommendations & Responses</h6>
                    <a href="{{ route('admin.recommendations.index') }}" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-external-link-alt me-1"></i>View More
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Child</th>
                                    <th>Trainer</th>
                                    <th>Recommendation</th>
                                    <th>Parent Response</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(isset($recentRecommendations) && $recentRecommendations->count() > 0)
                                    @foreach($recentRecommendations->take(5) as $recommendation)
                                    <tr>
                                        <td>{{ $recommendation->child->name ?? 'Unknown Child' }}</td>
                                        <td>{{ optional($recommendation->trainer)->name ?? 'Unknown Trainer' }}</td>
                                        <td>
                                            <div class="text-truncate" style="max-width: 200px;" title="{{ $recommendation->content }}">
                                                {{ Str::limit($recommendation->content, 50) }}
                                            </div>
                                        </td>
                                        <td>
                                            @if($recommendation->responses->count() > 0)
                                                <span class="badge badge-success">{{ $recommendation->responses->count() }} response(s)</span>
                                            @else
                                                <span class="badge badge-secondary">No response</span>
                                            @endif
                                        </td>
                                        <td>{{ $recommendation->created_at->format('M d, Y') }}</td>
                                    </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">No recommendations found</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                        @if(isset($recentRecommendations) && $recentRecommendations->count() > 5)
                            <div class="text-center mt-3">
                                <small class="text-muted">Showing 5 of {{ $recentRecommendations->count() }} recent recommendations</small>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Debug data
console.log('Daily Revenue Data:', {!! json_encode($dailyRevenue) !!});
console.log('Booking Stats:', {!! json_encode($bookingStats) !!});

// Revenue Chart
const revenueCtx = document.getElementById('revenueChart');
if (!revenueCtx) {
    console.error('Revenue chart canvas not found');
} else {
    const revenueData = {!! json_encode($dailyRevenue) !!};
    console.log('Revenue Data:', revenueData);

    const revenueChart = new Chart(revenueCtx, {
        type: 'line',
        data: {
            labels: revenueData.map(item => item.date),
            datasets: [
                {
                    label: 'Actual Revenue',
                    data: revenueData.map(item => item.actual_revenue),
                    borderColor: 'rgb(75, 192, 192)',
                    backgroundColor: 'rgba(75, 192, 192, 0.1)',
                    tension: 0.1,
                    fill: false
                },
                {
                    label: 'Potential Revenue',
                    data: revenueData.map(item => item.potential_revenue),
                    borderColor: 'rgb(255, 99, 132)',
                    backgroundColor: 'rgba(255, 99, 132, 0.1)',
                    tension: 0.1,
                    fill: false,
                    borderDash: [5, 5]
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                title: {
                    display: true,
                    text: 'Daily Revenue (with Discounts)'
                },
                tooltip: {
                    callbacks: {
                        afterBody: function(context) {
                            const dataIndex = context[0].dataIndex;
                            const item = revenueData[dataIndex];
                            if (item.total_discounts > 0) {
                                return `Discounts: -$${item.total_discounts.toLocaleString()}`;
                            }
                            return '';
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '$' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });
}

// Booking Status Chart
const bookingCtx = document.getElementById('bookingStatusChart');
if (!bookingCtx) {
    console.error('Booking status chart canvas not found');
} else {
    const bookingData = {!! json_encode($bookingStats) !!};
    console.log('Booking Data:', bookingData);

    const bookingChart = new Chart(bookingCtx, {
        type: 'doughnut',
        data: {
            labels: ['Confirmed', 'Pending', 'Cancelled', 'Completed'],
            datasets: [{
                data: [
                    bookingData.confirmed,
                    bookingData.pending,
                    bookingData.cancelled,
                    bookingData.completed
                ],
                backgroundColor: [
                    'rgb(75, 192, 192)',  // Confirmed - Green
                    'rgb(255, 205, 86)',  // Pending - Yellow
                    'rgb(255, 99, 132)',  // Cancelled - Red
                    'rgb(54, 162, 235)'   // Completed - Blue
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                title: {
                    display: true,
                    text: 'Booking Status Distribution'
                }
            }
        }
    });
}
</script>
@endsection

@push('styles')
<style>
.live-flash-icon {
    animation: flash 1s infinite alternate;
}
@keyframes flash {
    0% { color: #28a745; opacity: 1; }
    100% { color: #fff; opacity: 0.3; }
}
</style>
@endpush 