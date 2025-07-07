<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrainerReview extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'trainer_id',
        'rating',
        'comment',
    ];

    protected $casts = [
        'rating' => 'integer',
    ];

    /**
     * Get the user who wrote the review.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the trainer being reviewed.
     */
    public function trainer()
    {
        return $this->belongsTo(Trainer::class);
    }

    /**
     * Get the trainer's user information.
     */
    public function trainerUser()
    {
        return $this->belongsTo(Trainer::class)->with('user');
    }

    /**
     * Scope to get reviews for a specific trainer.
     */
    public function scopeForTrainer($query, $trainerId)
    {
        return $query->where('trainer_id', $trainerId);
    }

    /**
     * Scope to get reviews by a specific user.
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Get the average rating for a trainer.
     */
    public static function getAverageRatingForTrainer($trainerId)
    {
        return static::where('trainer_id', $trainerId)->avg('rating') ?? 0;
    }

    /**
     * Get the total number of reviews for a trainer.
     */
    public static function getReviewCountForTrainer($trainerId)
    {
        return static::where('trainer_id', $trainerId)->count();
    }
}
