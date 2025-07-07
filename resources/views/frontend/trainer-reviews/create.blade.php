@extends('layouts.frontend')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">
                        <i class="fas fa-star text-warning"></i>
                        Review Trainer: {{ $trainer->user->name }}
                    </h4>
                </div>
                <div class="card-body">
                    @if($existingReview)
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            You have already reviewed this trainer. You can only submit one review per trainer.
                        </div>
                    @else
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
                                
                                @php
                                    $averageRating = App\Models\TrainerReview::getAverageRatingForTrainer($trainer->id);
                                    $totalReviews = App\Models\TrainerReview::getReviewCountForTrainer($trainer->id);
                                @endphp
                                
                                <div class="mb-2">
                                    @for($i = 1; $i <= 5; $i++)
                                        @if($i <= $averageRating)
                                            <i class="fas fa-star text-warning"></i>
                                        @else
                                            <i class="far fa-star text-muted"></i>
                                        @endif
                                    @endfor
                                    <span class="text-muted">({{ $totalReviews }} reviews)</span>
                                </div>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('frontend.trainer-reviews.store', $trainer->id) }}">
                            @csrf
                            
                            <div class="form-group mb-4">
                                <label class="form-label">
                                    <strong>Rating *</strong>
                                </label>
                                <div class="star-rating">
                                    @for($i = 1; $i <= 5; $i++)
                                        <input type="radio" id="star{{ $i }}" name="rating" value="{{ $i }}" class="star-input" required>
                                        <label for="star{{ $i }}" class="star-label">
                                            <i class="far fa-star"></i>
                                        </label>
                                    @endfor
                                </div>
                                @error('rating')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group mb-4">
                                <label for="comment" class="form-label">
                                    <strong>Comment (Optional)</strong>
                                </label>
                                <textarea id="comment" name="comment" class="form-control @error('comment') is-invalid @enderror" 
                                          rows="4" placeholder="Share your experience with this trainer...">{{ old('comment') }}</textarea>
                                @error('comment')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Maximum 1000 characters</small>
                            </div>

                            <div class="d-flex justify-content-between">
                                <a href="{{ route('frontend.trainer-reviews.create-with-selection') }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i>
                                    Back to Trainer Selection
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-paper-plane"></i>
                                    Submit Review
                                </button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.star-rating {
    display: inline-block;
    font-size: 0;
}

.star-input {
    display: none;
}

.star-label {
    display: inline-block;
    font-size: 2rem;
    color: #ddd;
    cursor: pointer;
    transition: color 0.2s ease;
    margin-right: 5px;
}

.star-label:hover,
.star-label:hover ~ .star-label {
    color: #ffc107;
}

.star-input:checked ~ .star-label {
    color: #ffc107;
}

.star-input:checked ~ .star-label:hover,
.star-input:checked ~ .star-label:hover ~ .star-label {
    color: #ffc107;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const starInputs = document.querySelectorAll('.star-input');
    const starLabels = document.querySelectorAll('.star-label');
    
    starInputs.forEach((input, index) => {
        input.addEventListener('change', function() {
            // Update star icons based on selection
            starLabels.forEach((label, labelIndex) => {
                const icon = label.querySelector('i');
                if (labelIndex < this.value) {
                    icon.className = 'fas fa-star';
                } else {
                    icon.className = 'far fa-star';
                }
            });
        });
    });
});
</script>
@endsection 