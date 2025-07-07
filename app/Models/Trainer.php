<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Trainer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'profile_picture',
        'bio',
        'payment_method',
        'payment_details',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }

    public function availabilities()
    {
        return $this->hasMany(TrainerAvailability::class, 'trainer_id', 'user_id');
    }

    public function reviews()
    {
        return $this->hasMany(TrainerReview::class);
    }
}
