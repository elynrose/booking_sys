@extends('layouts.frontend')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">
                            <i class="fas fa-star text-warning"></i>
                            Reviews for {{ $trainer->user->name }}
                        </h4>
                        <a href="{{ route('frontend.trainer-reviews.create', $trainer->id) }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i>
                            Review This Trainer
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Trainer Info -->
                    <div class="row mb-4">
                        <div class="col-md-3 text-center">
                            @if($trainer->user->photo)
                                <img src="{{ $trainer->user->photo_url }}" 
                                     alt="{{ $trainer->user->name }}" 
                                     class="rounded-circle" 
                                     style="width: 100px; height: 100px; object-fit: cover;">
                            @else
                                <div class="bg-secondary rounded-circle d-inline-flex align-items-center justify-content-center" 
                                     style="width: 100px; height: 100px;">
                                    <i class="fas fa-user text-white" style="font-size: 3rem;"></i>
                                </div>
                            @endif
                        </div>
                        <div class="col-md-9">
                            <h5>{{ $trainer->user->name }}</h5>
                            @if($trainer->bio)
                                <p class="text-muted">{{ $trainer->bio }}</p>
                            @endif
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-2">
                                        <strong>Average Rating:</strong>
                                        <div class="star-display">
                                            @for($i = 1; $i <= 5; $i++)
                                                @if($i <= $averageRating)
                                                    <i class="fas fa-star text-warning"></i>
                                                @else
                                                    <i class="far fa-star text-muted"></i>
                                                @endif
                                            @endfor
                                            <span class="ms-2">({{ number_format($averageRating, 1) }}/5)</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-2">
                                        <strong>Total Reviews:</strong> {{ $totalReviews }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Reviews List -->
                    @if($reviews->count() > 0)
                        <div class="reviews-list">
                            @foreach($reviews as $review)
                                <div class="card mb-3 border">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-2 text-center">
                                                @if($review->user->photo)
                                                    <img src="{{ $review->user->photo_url }}" 
                                                         alt="{{ $review->user->name }}" 
                                                         class="rounded-circle" 
                                                         style="width: 50px; height: 50px; object-fit: cover;">
                                                @else
                                                    <div class="bg-secondary rounded-circle d-inline-flex align-items-center justify-content-center" 
                                                         style="width: 50px; height: 50px;">
                                                        <i class="fas fa-user text-white"></i>
                                                    </div>
                                                @endif
                                                <div class="mt-2">
                                                    <small class="text-muted">{{ $review->user->name }}</small>
                                                </div>
                                            </div>
                                            <div class="col-md-10">
                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                    <div class="star-display">
                                                        @for($i = 1; $i <= 5; $i++)
                                                            @if($i <= $review->rating)
                                                                <i class="fas fa-star text-warning"></i>
                                                            @else
                                                                <i class="far fa-star text-muted"></i>
                                                            @endif
                                                        @endfor
                                                        <span class="ms-2 text-muted">({{ $review->rating }}/5)</span>
                                                    </div>
                                                    <small class="text-muted">
                                                        {{ $review->created_at->format('M d, Y') }}
                                                    </small>
                                                </div>
                                                
                                                @if($review->comment)
                                                    <p class="mb-0">{{ $review->comment }}</p>
                                                @else
                                                    <p class="text-muted mb-0">No comment provided</p>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="d-flex justify-content-center mt-4">
                            {{ $reviews->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-star text-muted" style="font-size: 4rem;"></i>
                            <h5 class="mt-3 text-muted">No reviews yet</h5>
                            <p class="text-muted">This trainer doesn't have any reviews yet.</p>
                            <a href="{{ route('frontend.trainer-reviews.create', $trainer->id) }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i>
                                Be the First to Review
                            </a>
                        </div>
                    @endif

                    <div class="mt-4 text-center">
                        <a href="{{ route('frontend.home') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i>
                            Back to Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.star-display {
    display: inline-block;
}
</style>
@endsection 