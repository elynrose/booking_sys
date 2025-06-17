@extends('layouts.frontend')

@section('content')
<div class="container py-5">
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="display-5 fw-bold">Our Classes</h1>
            <p class="lead text-muted">Find the perfect class for your child's development and interests.</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('frontend.schedules.index') }}" method="GET" class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label for="category" class="form-label">Category</label>
                            <select name="category" id="category" class="form-control">
                                <option value="">All Categories</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->slug }}" {{ request('category') == $category->slug ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="age_group" class="form-label">Age Group</label>
                            <select name="age_group" id="age_group" class="form-control">
                                <option value="">All Ages</option>
                                <option value="3-5">3-5 years</option>
                                <option value="6-8">6-8 years</option>
                                <option value="9-12">9-12 years</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="day" class="form-label">Day</label>
                            <select name="day" id="day" class="form-control">
                                <option value="">Any Day</option>
                                <option value="monday">Monday</option>
                                <option value="tuesday">Tuesday</option>
                                <option value="wednesday">Wednesday</option>
                                <option value="thursday">Thursday</option>
                                <option value="friday">Friday</option>
                                <option value="saturday">Saturday</option>
                                <option value="sunday">Sunday</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary w-100">Apply Filters</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Classes Grid View -->
    <div class="row g-4" id="grid-view">
        @forelse($schedules as $schedule)
        <div class="col-md-4 mb-4">
            <div class="card h-100 mb-4">
                <div class="position-relative">
                    @if($schedule->photo)
                        <img src="{{ $schedule->photo_url }}" alt="{{ $schedule->title }}" class="card-img-top" style="height: 200px; object-fit: cover;">
                    @else
                        <svg class="card-img-top" width="100%" height="200" xmlns="http://www.w3.org/2000/svg">
                            <rect width="100%" height="100%" fill="#f8f9fa"/>
                            <text x="50%" y="50%" font-family="Arial" font-size="24" fill="#6c757d" text-anchor="middle" dominant-baseline="middle">No Image Available</text>
                        </svg>
                    @endif
                    @if($schedule->trainer && $schedule->trainer->user)
                        <div class="position-absolute" style="top: 100%; left: 86%; transform: translate(-50%, -50%); z-index: 2;">
                            @if($schedule->trainer->profile_picture)
                                <img src="{{ Storage::url($schedule->trainer->profile_picture) }}" 
                                     alt="{{ $schedule->trainer->user->name }}" 
                                     class="rounded-circle border border-white shadow-lg"
                                     style="width: 64px; height: 64px; object-fit: cover;">
                            @else
                                <div class="rounded-circle border border-white bg-primary d-flex align-items-center justify-content-center shadow-lg"
                                     style="width: 64px; height: 64px;">
                                    <span class="text-white" style="font-size: 24px;">
                                        {{ strtoupper(substr($schedule->trainer->user->name, 0, 1)) }}
                                    </span>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title mb-1">{{ $schedule->title }}</h5>
                    @if($schedule->trainer && $schedule->trainer->user)
                        <p class="card-text text-muted mb-2">
                            <i class="fas fa-user-tie"></i> {{ $schedule->trainer->user->name }}
                        </p>
                    @endif
                    <p class="card-text text-muted mb-2">{{ Str::limit($schedule->description, 70) }}</p>
                    <div class="mb-1 small text-secondary">
                        @if($schedule->start_date && $schedule->end_date)
                            {{ $schedule->start_date->format('M d, Y') }} &mdash; {{ $schedule->end_date->format('M d, Y') }}
                        @endif
                    </div>
                    <div class="mb-2 small text-secondary">
                        {{ $schedule->start_time->format('h:i A') }} - {{ $schedule->end_time->format('h:i A') }}
                    </div>
                    <div class="mt-auto d-flex justify-content-between align-items-center">
                        <span class="badge" style="background-color: #2ecc71;">${{ number_format($schedule->price, 2) }}</span>
                        <span class="badge bg-primary text-white">{{ $schedule->max_participants - $schedule->bookings_count }} spots left</span>
                    </div>
                    @php
                        $hasActiveBooking = auth()->user()->bookings()
                            ->where('schedule_id', $schedule->id)
                            ->whereDoesntHave('checkins', function($query) {
                                $query->whereNotNull('checkout_time');
                            })
                            ->exists();
                    @endphp
                    @if($hasActiveBooking)
                        <button class="btn btn-secondary btn-block mt-3" disabled>
                            <i class="fas fa-check-circle me-2"></i> Currently Booked
                        </button>
                    @else
                        <a href="{{ route('bookings.create', $schedule) }}" class="btn btn-primary btn-block mt-3">
                            <i class="fas fa-calendar-plus me-2"></i> Book Now
                        </a>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="alert alert-info">
                No classes found matching your criteria. Please try different filters.
            </div>
        </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="row mt-4">
        <div class="col-12">
            {{ $schedules->links() }}
        </div>
    </div>
</div>

@push('styles')
<style>
    .card {
        transition: transform 0.2s;
    }
    .card:hover {
        transform: translateY(-5px);
    }
    .badge {
        font-size: 0.8rem;
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // View switcher
        const viewButtons = document.querySelectorAll('[data-view]');
        const gridView = document.getElementById('grid-view');
        
        viewButtons.forEach(button => {
            button.addEventListener('click', function() {
                viewButtons.forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');
                
                if (this.dataset.view === 'list') {
                    gridView.classList.remove('row');
                    gridView.classList.add('list-view');
                } else {
                    gridView.classList.add('row');
                    gridView.classList.remove('list-view');
                }
            });
        });
    });
</script>
@endpush
@endsection 