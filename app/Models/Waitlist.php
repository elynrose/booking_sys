<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Waitlist extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'schedule_id',
        'child_id',
        'status',
        'sessions_requested',
        'notes',
        'notified_at'
    ];

    protected $casts = [
        'notified_at' => 'datetime'
    ];

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

    public function markAsNotified()
    {
        $this->update([
            'status' => 'notified',
            'notified_at' => now()
        ]);
    }

    public function markAsConverted()
    {
        $this->update(['status' => 'converted']);
    }

    public function markAsCancelled()
    {
        $this->update(['status' => 'cancelled']);
    }

    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isNotified()
    {
        return $this->status === 'notified';
    }

    public function isConverted()
    {
        return $this->status === 'converted';
    }

    public function isCancelled()
    {
        return $this->status === 'cancelled';
    }
}
