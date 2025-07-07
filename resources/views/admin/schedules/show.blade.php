@extends('layouts.admin')

@section('content')
<div class="card">
    <div class="card-header">
        <h4 class="mb-0">
            <i class="fas fa-calendar mr-2"></i>
            Schedule Details: {{ $schedule->title }}
        </h4>
    </div>
    
    <div class="card-body">
        <div class="row">
            <div class="col-md-8">
                <h5>Basic Information</h5>
                <table class="table table-bordered">
                    <tr>
                        <th width="200">Title</th>
                        <td>{{ $schedule->title }}</td>
                    </tr>
                    <tr>
                        <th>Description</th>
                        <td>{{ $schedule->description ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Trainer</th>
                        <td>{{ optional($schedule->trainer)->user->name ?? 'No Trainer' }}</td>
                    </tr>
                    <tr>
                        <th>Category</th>
                        <td>{{ optional($schedule->category)->name ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Type</th>
                        <td>
                            <span class="badge badge-{{ $schedule->type === 'group' ? 'info' : 'warning' }}">
                                {{ $schedule->type === 'group' ? 'Group Class' : 'Private Training' }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td>
                            <span class="badge badge-{{ $schedule->status === 'active' ? 'success' : 'danger' }}">
                                {{ ucfirst($schedule->status) }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>Start Date</th>
                        <td>{{ optional($schedule->start_date)->format('M d, Y') ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>End Date</th>
                        <td>{{ optional($schedule->end_date)->format('M d, Y') ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Start Time</th>
                        <td>{{ optional($schedule->start_time)->format('h:i A') ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>End Time</th>
                        <td>{{ optional($schedule->end_time)->format('h:i A') ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Price</th>
                        <td>
                            @if($schedule->hasValidDiscount())
                                <span class="text-decoration-line-through text-muted">${{ number_format($schedule->price, 2) }}</span>
                                <span class="text-danger font-weight-bold">${{ number_format($schedule->discounted_price, 2) }}</span>
                                <span class="badge badge-danger ml-1">{{ $schedule->discount_percentage }}% OFF</span>
                            @else
                                ${{ number_format($schedule->price, 2) }}
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Max Participants</th>
                        <td>{{ $schedule->max_participants }}</td>
                    </tr>
                    <tr>
                        <th>Current Participants</th>
                        <td>{{ $schedule->current_participants }}</td>
                    </tr>
                    <tr>
                        <th>Location</th>
                        <td>{{ $schedule->location ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Featured</th>
                        <td>
                            <span class="badge badge-{{ $schedule->is_featured ? 'success' : 'secondary' }}">
                                {{ $schedule->is_featured ? 'Yes' : 'No' }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>Allow Unlimited Bookings</th>
                        <td>
                            <span class="badge badge-{{ $schedule->allow_unlimited_bookings ? 'success' : 'secondary' }}">
                                {{ $schedule->allow_unlimited_bookings ? 'Yes' : 'No' }}
                            </span>
                        </td>
                    </tr>
                </table>
            </div>
            
            <div class="col-md-4">
                <h5>Quick Actions</h5>
                <div class="btn-group-vertical w-100">
                    <a href="{{ route('admin.schedules.edit', $schedule) }}" class="btn btn-primary mb-2">
                        <i class="fas fa-edit mr-2"></i>Edit Schedule
                    </a>
                    <a href="{{ route('admin.trainer-availability.show', $schedule) }}" class="btn btn-info mb-2">
                        <i class="fas fa-calendar-alt mr-2"></i>Manage Availability
                    </a>
                    <a href="{{ route('admin.bookings.create') }}?schedule_id={{ $schedule->id }}" class="btn btn-success mb-2">
                        <i class="fas fa-plus mr-2"></i>Add Booking
                    </a>
                </div>
                
                <h5 class="mt-4">Statistics</h5>
                <div class="list-group">
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        Total Bookings
                        <span class="badge badge-primary badge-pill">{{ $schedule->bookings->count() }}</span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        Active Bookings
                        <span class="badge badge-success badge-pill">{{ $schedule->bookings->where('status', 'confirmed')->count() }}</span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        Available Spots
                        <span class="badge badge-info badge-pill">{{ $schedule->getRemainingSpots() }}</span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        Availability Slots
                        <span class="badge badge-warning badge-pill">{{ $schedule->availabilities->count() }}</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Recent Bookings -->
        @if($schedule->bookings->count() > 0)
        <div class="mt-4">
            <h5>Recent Bookings</h5>
            <div class="table-responsive">
                <table class="table table-sm table-bordered">
                    <thead class="thead-light">
                        <tr>
                            <th>User</th>
                            <th>Status</th>
                            <th>Payment Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($schedule->bookings->take(10) as $booking)
                        <tr>
                            <td>{{ optional($booking->user)->name ?? 'N/A' }}</td>
                            <td>
                                <span class="badge badge-{{ $booking->status === 'confirmed' ? 'success' : ($booking->status === 'pending' ? 'warning' : 'danger') }}">
                                    {{ ucfirst($booking->status) }}
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-{{ $booking->is_paid ? 'success' : 'warning' }}">
                                    {{ $booking->is_paid ? 'Paid' : 'Unpaid' }}
                                </span>
                            </td>
                            <td>{{ $booking->created_at->format('M d, Y H:i') }}</td>
                            <td>
                                <a href="{{ route('admin.bookings.edit', $booking) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>
    
    <div class="card-footer">
        <a href="{{ route('admin.schedules.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left mr-2"></i>Back to Schedules
        </a>
    </div>
</div>
@endsection 