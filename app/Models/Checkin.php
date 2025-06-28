<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Checkin extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'checkin_time',
        'checkout_time',
        'is_late_checkin',
        'late_minutes',
    ];

    protected $casts = [
        'checkin_time' => 'datetime',
        'checkout_time' => 'datetime',
        'is_late_checkin' => 'boolean',
        'late_minutes' => 'integer',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
} 