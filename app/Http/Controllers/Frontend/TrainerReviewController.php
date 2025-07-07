<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Trainer;
use App\Models\TrainerReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TrainerReviewController extends Controller
{
    /**
     * Show the review form for a specific trainer.
     */
    public function create($trainerId)
    {
        $trainer = Trainer::with('user')->findOrFail($trainerId);
        
        // Check if user has already reviewed this trainer
        $existingReview = TrainerReview::where('user_id', Auth::id())
            ->where('trainer_id', $trainerId)
            ->first();

        return view('frontend.trainer-reviews.create', compact('trainer', 'existingReview'));
    }

    /**
     * Store a new review.
     */
    public function store(Request $request, $trainerId)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        // Check if user has already reviewed this trainer
        $existingReview = TrainerReview::where('user_id', Auth::id())
            ->where('trainer_id', $trainerId)
            ->first();

        if ($existingReview) {
            return redirect()->back()->with('error', 'You have already reviewed this trainer.');
        }

        TrainerReview::create([
            'user_id' => Auth::id(),
            'trainer_id' => $trainerId,
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        return redirect()->route('frontend.trainer-reviews.my-reviews')
            ->with('success', 'Review submitted successfully!');
    }

    /**
     * Show user's reviews.
     */
    public function myReviews()
    {
        $reviews = TrainerReview::with(['trainer.user'])
            ->where('user_id', Auth::id())
            ->latest()
            ->paginate(10);

        return view('frontend.trainer-reviews.my-reviews', compact('reviews'));
    }

    /**
     * Show all reviews for a specific trainer.
     */
    public function trainerReviews($trainerId)
    {
        $trainer = Trainer::with('user')->findOrFail($trainerId);
        $reviews = TrainerReview::with(['user'])
            ->where('trainer_id', $trainerId)
            ->latest()
            ->paginate(10);

        $averageRating = TrainerReview::getAverageRatingForTrainer($trainerId);
        $totalReviews = TrainerReview::getReviewCountForTrainer($trainerId);

        return view('frontend.trainer-reviews.trainer-reviews', compact('trainer', 'reviews', 'averageRating', 'totalReviews'));
    }

    /**
     * Show the review form with trainer selection.
     */
    public function createWithSelection()
    {
        $trainers = Trainer::with('user')
            ->where('is_active', true)
            ->get();

        return view('frontend.trainer-reviews.create-with-selection', compact('trainers'));
    }
}
