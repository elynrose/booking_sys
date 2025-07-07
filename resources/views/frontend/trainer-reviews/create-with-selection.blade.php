@extends('layouts.frontend')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">
                        <i class="fas fa-star text-warning"></i>
                        Submit Trainer Review
                    </h4>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-4">Select a trainer to submit your review:</p>

                    @if($trainers->count() > 0)
                        <div class="row">
                            @foreach($trainers as $trainer)
                                <div class="col-md-6 mb-3">
                                    <div class="card h-100 border">
                                        <div class="card-body text-center">
                                            <div class="mb-3">
                                                @if($trainer->user->photo)
                                                    <img src="{{ $trainer->user->photo_url }}" 
                                                         alt="{{ $trainer->user->name }}" 
                                                         class="rounded-circle" 
                                                         style="width: 80px; height: 80px; object-fit: cover;">
                                                @else
                                                    <div class="bg-secondary rounded-circle d-inline-flex align-items-center justify-content-center" 
                                                         style="width: 80px; height: 80px;">
                                                        <i class="fas fa-user text-white" style="font-size: 2rem;"></i>
                                                    </div>
                                                @endif
                                            </div>
                                            
                                            <h5 class="card-title">{{ $trainer->user->name }}</h5>
                                            
                                            @if($trainer->bio)
                                                <p class="card-text text-muted small">
                                                    {{ Str::limit($trainer->bio, 100) }}
                                                </p>
                                            @endif

                                            @php
                                                $averageRating = App\Models\TrainerReview::getAverageRatingForTrainer($trainer->id);
                                                $totalReviews = App\Models\TrainerReview::getReviewCountForTrainer($trainer->id);
                                            @endphp

                                            <div class="mb-3">
                                                @for($i = 1; $i <= 5; $i++)
                                                    @if($i <= $averageRating)
                                                        <i class="fas fa-star text-warning"></i>
                                                    @else
                                                        <i class="far fa-star text-muted"></i>
                                                    @endif
                                                @endfor
                                                <small class="text-muted">({{ $totalReviews }} reviews)</small>
                                            </div>

                                            <a href="{{ route('frontend.trainer-reviews.create', $trainer->id) }}" 
                                               class="btn btn-primary btn-sm">
                                                <i class="fas fa-star"></i>
                                                Review This Trainer
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-users text-muted" style="font-size: 3rem;"></i>
                            <h5 class="mt-3 text-muted">No trainers available</h5>
                            <p class="text-muted">There are currently no active trainers to review.</p>
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
@endsection 