@extends('layouts.frontend')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">
                        <i class="fas fa-star text-warning"></i>
                        My Trainer Reviews
                    </h4>
                    <a href="{{ route('frontend.trainer-reviews.create-with-selection') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i>
                        Submit New Review
                    </a>
                </div>
                <div class="card-body">
                    @if($reviews->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Trainer</th>
                                        <th>Rating</th>
                                        <th>Comment</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($reviews as $review)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    @if($review->trainer->user->photo)
                                                        <img src="{{ $review->trainer->user->photo_url }}" 
                                                             alt="{{ $review->trainer->user->name }}" 
                                                             class="rounded-circle me-3" 
                                                             style="width: 40px; height: 40px; object-fit: cover;">
                                                    @else
                                                        <div class="bg-secondary rounded-circle d-inline-flex align-items-center justify-content-center me-3" 
                                                             style="width: 40px; height: 40px;">
                                                            <i class="fas fa-user text-white"></i>
                                                        </div>
                                                    @endif
                                                    <div>
                                                        <strong>{{ $review->trainer->user->name }}</strong>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
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
                                            </td>
                                            <td>
                                                @if($review->comment)
                                                    <div class="text-truncate" style="max-width: 300px;" title="{{ $review->comment }}">
                                                        {{ Str::limit($review->comment, 100) }}
                                                    </div>
                                                @else
                                                    <span class="text-muted">No comment</span>
                                                @endif
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    {{ $review->created_at->format('M d, Y') }}
                                                </small>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-center mt-4">
                            {{ $reviews->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-star text-muted" style="font-size: 4rem;"></i>
                            <h5 class="mt-3 text-muted">No reviews yet</h5>
                            <p class="text-muted">You haven't submitted any trainer reviews yet.</p>
                            <a href="{{ route('frontend.trainer-reviews.create-with-selection') }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i>
                                Submit Your First Review
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