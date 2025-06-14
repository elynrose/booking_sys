<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Session extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'booking_id',
        'schedule_id',
        'check_in_time',
        'check_out_time',
        'status',
        'notes',
    ];

    protected $casts = [
        'check_in_time' => 'datetime',
        'check_out_time' => 'datetime',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }

    public function checkIn()
    {
        $this->update([
            'check_in_time' => now(),
            'status' => 'checked_in'
        ]);
    }

    public function checkOut()
    {
        $this->update([
            'check_out_time' => now(),
            'status' => 'checked_out'
        ]);
    }

    public function markAsMissed()
    {
        $this->update(['status' => 'missed']);
    }
}
