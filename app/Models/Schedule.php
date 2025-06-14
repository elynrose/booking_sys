<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Schedule extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'photo',
        'trainer_id',
        'category_id',
        'start_date',
        'end_date',
        'start_time',
        'end_time',
        'price',
        'max_participants',
        'is_featured',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'is_featured' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function class()
    {
        return $this->belongsTo(Session::class, 'class_id');
    }

    public function trainer()
    {
        return $this->belongsTo(Trainer::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class)
            ->withPivot(['check_in_date', 'check_in_time', 'check_out_time'])
            ->withTimestamps();
    }

    public function getNextSessionDate()
    {
        $today = Carbon::today();
        $startDate = Carbon::parse($this->start_date);
        $endDate = Carbon::parse($this->end_date);
        
        // If schedule hasn't started yet, return start date
        if ($today->lt($startDate)) {
            return $startDate;
        }

        // If schedule has ended, return null
        if ($today->gt($endDate)) {
            return null;
        }

        // Get the day of week for the schedule
        $dayOfWeek = $startDate->dayOfWeek;
        
        // Find the next occurrence of this day
        $nextDate = $today->copy();
        while ($nextDate->dayOfWeek !== $dayOfWeek) {
            $nextDate->addDay();
        }

        // If the next date is after the end date, return null
        if ($nextDate->gt($endDate)) {
            return null;
        }

        return $nextDate;
    }

    public function isCheckedIn($userId)
    {
        $today = Carbon::today();
        
        return $this->users()
            ->where('users.id', $userId)
            ->wherePivot('check_in_date', $today)
            ->exists();
    }

    public function getRemainingSpots()
    {
        return $this->max_participants - $this->current_participants;
    }

    public function isFull()
    {
        return $this->current_participants >= $this->max_participants;
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function waitlists()
    {
        return $this->hasMany(Waitlist::class);
    }

    public function sessions()
    {
        return $this->hasMany(Session::class);
    }

    public function incrementParticipants()
    {
        $this->increment('current_participants');
    }

    public function decrementParticipants()
    {
        $this->decrement('current_participants');
    }

    public function getPhotoUrlAttribute()
    {
        if ($this->photo) {
            return asset('storage/' . $this->photo);
        }
        return asset('images/default-schedule.jpg');
    }

    public function isAvailable()
    {
        return $this->status === 'active' || $this->status === 'published';
    }
}
