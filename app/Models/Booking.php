<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Booking extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'schedule_id',
        'child_id',
        'sessions_remaining',
        'status',
        'check_in_code',
        'is_paid',
        'payment_method',
        'payment_status',
        'checkin_code',
        'checkout_code',
        'checkin_time',
        'checkout_time'
    ];

    protected $casts = [
        'is_paid' => 'boolean',
        'checkin_time' => 'datetime',
        'checkout_time' => 'datetime'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($booking) {
            $booking->check_in_code = Str::random(10);
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }

    public function child()
    {
        return $this->belongsTo(Child::class);
    }

    public function sessions()
    {
        return $this->hasMany(Session::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function checkins()
    {
        return $this->hasMany(Checkin::class);
    }

    public function canCheckIn()
    {
        return $this->is_paid && $this->sessions_remaining > 0 && $this->status === 'confirmed';
    }

    public function decrementSessions()
    {
        $this->decrement('sessions_remaining');
    }

    public function isLastSession()
    {
        return $this->sessions_remaining === 1;
    }
}
