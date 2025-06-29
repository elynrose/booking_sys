@extends('layouts.frontend')

@section('content')
<div class="container py-5">
    <!-- Date Range Filter -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('home') }}" class="row align-items-end">
                <div class="col-md-4">
                    <label for="start_date">{{ __('app.dashboard.start_date') }}</label>
                    <input type="date" class="form-control" id="start_date" name="start_date" value="{{ request('start_date', now()->subDays(30)->format('Y-m-d')) }}">
                </div>
                <div class="col-md-4">
                    <label for="end_date">{{ __('app.dashboard.end_date') }}</label>
                    <input type="date" class="form-control" id="end_date" name="end_date" value="{{ request('end_date', now()->format('Y-m-d')) }}">
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary">{{ __('app.dashboard.filter') }}</button>
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
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">{{ __('app.dashboard.total_revenue') }}</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">${{ number_format($totalRevenue, 2) }}</div>
                            <div class="text-xs text-muted mt-1">{{ __('app.dashboard.last_30_days') }}: ${{ number_format($dateRangeRevenue, 2) }}</div>
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
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">{{ __('app.dashboard.total_bookings') }}</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalBookings }}</div>
                            <div class="text-xs text-muted mt-1">{{ __('app.dashboard.last_30_days') }}: {{ $dateRangeBookings }}</div>
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
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">{{ __('app.dashboard.total_users') }}</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalUsers }}</div>
                            <div class="text-xs text-muted mt-1">{{ __('app.dashboard.last_30_days') }}: {{ $dateRangeUsers }}</div>
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
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">{{ __('app.dashboard.total_trainers') }}</div>
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

    <!-- Charts Row -->
    <div class="row">
        <!-- Revenue Chart -->
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">{{ __('app.dashboard.revenue_overview') }}</h6>
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
                    <h6 class="m-0 font-weight-bold text-primary">{{ __('app.dashboard.booking_status') }}</h6>
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
        <div class="col-xl-6 col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">{{ __('app.dashboard.revenue_by_category') }}</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>{{ __('app.dashboard.category') }}</th>
                                    <th>{{ __('app.dashboard.revenue') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($revenueByCategory as $category)
                                <tr>
                                    <td>{{ $category->name ?? __('app.dashboard.uncategorized') }}</td>
                                    <td>${{ number_format($category->total, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Revenue by Trainer -->
        <div class="col-xl-6 col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">{{ __('app.dashboard.revenue_by_trainer') }}</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>{{ __('app.dashboard.trainer') }}</th>
                                    <th>{{ __('app.dashboard.revenue') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($revenueByTrainer as $trainer)
                                <tr>
                                    <td>{{ $trainer->name ?? __('app.dashboard.unnamed_trainer') }}</td>
                                    <td>${{ number_format($trainer->total, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activities Row -->
    <div class="row">
        <!-- Recent Bookings -->
        <div class="col-xl-6 col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">{{ __('app.dashboard.recent_bookings') }}</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>{{ __('app.dashboard.user') }}</th>
                                    <th>{{ __('app.dashboard.schedule') }}</th>
                                    <th>{{ __('app.dashboard.status') }}</th>
                                    <th>{{ __('app.dashboard.date') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentBookings as $booking)
                                <tr>
                                    <td>{{ $booking->user->name ?? __('app.dashboard.unknown_user') }}</td>
                                    <td>
                                        @if($booking->schedule)
                                            {{ $booking->schedule->title }}
                                        @else
                                            <span class="text-danger">{{ __('app.status.not_found') }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ $booking->status == 'confirmed' ? 'success' : ($booking->status == 'pending' ? 'warning' : 'danger') }}">
                                            {{ __('app.bookings.status.' . $booking->status) }}
                                        </span>
                                    </td>
                                    <td>{{ $booking->created_at->format('M d, Y') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Payments -->
        <div class="col-xl-6 col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">{{ __('app.dashboard.recent_payments') }}</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>{{ __('app.dashboard.user') }}</th>
                                    <th>{{ __('app.dashboard.amount') }}</th>
                                    <th>{{ __('app.dashboard.status') }}</th>
                                    <th>{{ __('app.dashboard.date') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentPayments as $payment)
                                <tr>
                                    <td>{{ optional($payment->booking->user)->name ?? __('app.dashboard.unknown_user') }}</td>
                                    <td>${{ number_format($payment->amount, 2) }}</td>
                                    <td>
                                        <span class="badge badge-{{ $payment->status == 'paid' ? 'success' : ($payment->status == 'pending' ? 'warning' : 'danger') }}">
                                            {{ __('app.bookings.payment_status.' . $payment->status) }}
                                        </span>
                                    </td>
                                    <td>{{ $payment->created_at->format('M d, Y') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Revenue Chart
var ctx = document.getElementById("revenueChart");
var myLineChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: {!! json_encode($dailyRevenue->pluck('date')) !!},
        datasets: [{
            label: '{{ __("app.dashboard.revenue") }}',
            lineTension: 0.3,
            backgroundColor: "rgba(78, 115, 223, 0.05)",
            borderColor: "rgba(78, 115, 223, 1)",
            pointRadius: 3,
            pointBackgroundColor: "rgba(78, 115, 223, 1)",
            pointBorderColor: "rgba(78, 115, 223, 1)",
            pointHoverRadius: 3,
            pointHoverBackgroundColor: "rgba(78, 115, 223, 1)",
            pointHoverBorderColor: "rgba(78, 115, 223, 1)",
            pointHitRadius: 10,
            pointBorderWidth: 2,
            data: {!! json_encode($dailyRevenue->pluck('total')) !!}
        }],
    },
    options: {
        maintainAspectRatio: false,
        layout: {
            padding: {
                left: 10,
                right: 25,
                top: 25,
                bottom: 0
            }
        },
        scales: {
            xAxes: [{
                time: {
                    unit: 'date'
                },
                gridLines: {
                    display: false,
                    drawBorder: false
                },
                ticks: {
                    maxTicksLimit: 7
                }
            }],
            yAxes: [{
                ticks: {
                    maxTicksLimit: 5,
                    padding: 10,
                    callback: function(value, index, values) {
                        return '$' + number_format(value);
                    }
                },
                gridLines: {
                    color: "rgb(234, 236, 244)",
                    zeroLineColor: "rgb(234, 236, 244)",
                    drawBorder: false,
                    borderDash: [2],
                    zeroLineBorderDash: [2]
                }
            }],
        },
        legend: {
            display: false
        },
        tooltips: {
            backgroundColor: "rgb(255,255,255)",
            bodyFontColor: "#858796",
            titleMarginBottom: 10,
            titleFontColor: '#6e707e',
            titleFontSize: 14,
            borderColor: '#dddfeb',
            borderWidth: 1,
            xPadding: 15,
            yPadding: 15,
            displayColors: false,
            intersect: false,
            mode: 'index',
            caretPadding: 10,
            callbacks: {
                label: function(tooltipItem, chart) {
                    var datasetLabel = chart.datasets[tooltipItem.datasetIndex].label || '';
                    return datasetLabel + ': $' + number_format(tooltipItem.yLabel);
                }
            }
        }
    }
});

// Booking Status Chart
var ctx = document.getElementById("bookingStatusChart");
var myPieChart = new Chart(ctx, {
    type: 'doughnut',
    data: {
        labels: ["{{ __('app.bookings.status.confirmed') }}", "{{ __('app.bookings.status.pending') }}", "{{ __('app.bookings.status.cancelled') }}", "{{ __('app.bookings.status.completed') }}"],
        datasets: [{
            data: [{{ $bookingStats['confirmed'] }}, {{ $bookingStats['pending'] }}, {{ $bookingStats['cancelled'] }}, {{ $bookingStats['completed'] }}],
            backgroundColor: ['#1cc88a', '#f6c23e', '#e74a3b', '#36b9cc'],
            hoverBackgroundColor: ['#17a673', '#f4b619', '#e02e1c', '#2c9faf'],
            hoverBorderColor: "rgba(234, 236, 244, 1)",
        }],
    },
    options: {
        maintainAspectRatio: false,
        tooltips: {
            backgroundColor: "rgb(255,255,255)",
            bodyFontColor: "#858796",
            borderColor: '#dddfeb',
            borderWidth: 1,
            xPadding: 15,
            yPadding: 15,
            displayColors: false,
            caretPadding: 10,
        },
        legend: {
            display: false
        },
        cutoutPercentage: 80,
    },
});

function number_format(number, decimals, dec_point, thousands_sep) {
    number = (number + '').replace(',', '').replace(' ', '');
    var n = !isFinite(+number) ? 0 : +number,
        prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
        sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
        dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
        s = '',
        toFixedFix = function(n, prec) {
            var k = Math.pow(10, prec);
            return '' + Math.round(n * k) / k;
        };
    s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
    if ((sep.length > 0)) {
        var i = s[0].length;
        if (i % 3 !== 0) {
            i = 0;
        }
        for (i; i < s[0].length; i += 3) {
            if (i !== 0) {
                s[0] = s[0].substr(0, i) + sep + s[0].substr(i);
            }
        }
    }
    if ((s[1] || '').length < prec) {
        s[1] = s[1] || '';
        s[1] += new Array(prec - s[1].length + 1).join('0');
    }
    return s.join(dec);
}
</script>
@endpush
@endsection 