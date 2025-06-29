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
            <h1 class="display-5 fw-bold">{{ __('app.schedules.our_classes') }}</h1>
            <p class="lead text-muted">{{ __('app.schedules.find_perfect_class') }}</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('frontend.schedules.index') }}" method="GET" class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label for="category" class="form-label">{{ __('app.dashboard.category') }}</label>
                            <select name="category" id="category" class="form-control">
                                <option value="">{{ __('app.schedules.all_categories') }}</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="type" class="form-label">{{ __('app.schedules.class_type') }}</label>
                            <select name="type" id="type" class="form-control">
                                <option value="">{{ __('app.schedules.all_types') }}</option>
                                <option value="group" {{ request('type') == 'group' ? 'selected' : '' }}>{{ __('app.schedules.group_classes') }}</option>
                                <option value="private" {{ request('type') == 'private' ? 'selected' : '' }}>{{ __('app.schedules.private_individual_training') }}</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="age_group" class="form-label">{{ __('app.children.age') }} {{ __('app.dashboard.category') }}</label>
                            <select name="age_group" id="age_group" class="form-control">
                                <option value="">{{ __('app.schedules.all_ages') }}</option>
                                <option value="3-5" {{ request('age_group') == '3-5' ? 'selected' : '' }}>{{ __('app.schedules.age_groups.3-5') }}</option>
                                <option value="6-8" {{ request('age_group') == '6-8' ? 'selected' : '' }}>{{ __('app.schedules.age_groups.6-8') }}</option>
                                <option value="9-12" {{ request('age_group') == '9-12' ? 'selected' : '' }}>{{ __('app.schedules.age_groups.9-12') }}</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="day" class="form-label">{{ __('app.schedules.days.any_day') }}</label>
                            <select name="day" id="day" class="form-control">
                                <option value="">{{ __('app.schedules.days.any_day') }}</option>
                                <option value="monday" {{ request('day') == 'monday' ? 'selected' : '' }}>{{ __('app.schedules.days.monday') }}</option>
                                <option value="tuesday" {{ request('day') == 'tuesday' ? 'selected' : '' }}>{{ __('app.schedules.days.tuesday') }}</option>
                                <option value="wednesday" {{ request('day') == 'wednesday' ? 'selected' : '' }}>{{ __('app.schedules.days.wednesday') }}</option>
                                <option value="thursday" {{ request('day') == 'thursday' ? 'selected' : '' }}>{{ __('app.schedules.days.thursday') }}</option>
                                <option value="friday" {{ request('day') == 'friday' ? 'selected' : '' }}>{{ __('app.schedules.days.friday') }}</option>
                                <option value="saturday" {{ request('day') == 'saturday' ? 'selected' : '' }}>{{ __('app.schedules.days.saturday') }}</option>
                                <option value="sunday" {{ request('day') == 'sunday' ? 'selected' : '' }}>{{ __('app.schedules.days.sunday') }}</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary w-100">{{ __('app.schedules.apply_filters') }}</button>
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
                        <img src="{{ Storage::url($schedule->photo) }}" alt="{{ $schedule->title }}" class="card-img-top" style="height: 200px; object-fit: cover;">
                    @else
                        <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                            <svg width="100" height="100" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <rect width="100" height="100" fill="#f8f9fa"/>
                                <text x="50" y="50" text-anchor="middle" dominant-baseline="middle" fill="#6c757d" font-size="14">{{ __('app.schedules.no_image_available') }}</text>
                            </svg>
                        </div>
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
                            <i class="fas fa-tag me-1"></i>{{ optional($schedule->category)->name ?? __('app.dashboard.uncategorized') }}
                            <br><i class="fas fa-user-tie me-1"></i>{{ $schedule->trainer->user->name }}
                        </p>
                    @endif
                    <p class="card-text text-muted mb-2">{{ Str::limit($schedule->description, 70) }}</p>
                    <div class="mb-1 small text-secondary">
                        @if($schedule->start_date && $schedule->end_date)
                            {{ optional($schedule->start_date)->format('M d, Y') ?? __('app.status.n_a') }} &mdash; {{ $schedule->end_date->format('M d, Y') }}
                        @endif
                    </div>
                    <div class="mb-2 small text-secondary">
                        @if($schedule->start_time && $schedule->end_time)
                            {{ optional($schedule->start_time)->format('h:i A') ?? __('app.status.n_a') }} - {{ $schedule->end_time->format('h:i A') }}
                        @endif
                    </div>
                    <div class="mt-auto d-flex justify-content-between align-items-center">
                        <span class="badge" style="background-color: #2ecc71;">${{ number_format($schedule->price, 2) }}</span>
                        <span class="badge bg-primary text-white">{{ $schedule->max_participants - $schedule->bookings->count() }} {{ __('app.schedules.spots_left') }}</span>
                    </div>
                    <div class="mt-2">
                        <span class="badge badge-{{ $schedule->type === 'group' ? 'info' : 'warning' }}">
                            {{ $schedule->type === 'group' ? 'Group Class' : 'Private Training' }}
                        </span>
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
                            <i class="fas fa-check-circle me-2"></i> {{ __('app.status.currently_booked') }}
                        </button>
                    @else
                        <a href="{{ route('frontend.bookings.create', ['schedule_id' => $schedule->id]) }}" class="btn btn-primary btn-block mt-3">
                            <i class="fas fa-calendar-plus me-2"></i> {{ __('app.welcome.book_now') }}
                        </a>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="alert alert-info">
                {{ __('app.alerts.no_records_found') }}
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