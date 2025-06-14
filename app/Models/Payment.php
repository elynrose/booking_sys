<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'booking_id',
        'schedule_id',
        'amount',
        'description',
        'status',
        'payment_date',
        'paid_at',
        'stripe_payment_id',
        'zelle_reference',
        'notes',
    ];

    protected $casts = [
        'payment_date' => 'datetime',
        'paid_at' => 'datetime',
        'amount' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }

    public function confirm()
    {
        $this->update(['status' => 'completed']);
        $this->booking->update([
            'is_paid' => true,
            'status' => 'confirmed',
            'payment_status' => 'confirmed'
        ]);
    }

    public function refund()
    {
        $this->update(['status' => 'refunded']);
        $this->booking->update([
            'is_paid' => false,
            'payment_status' => 'refunded'
        ]);
    }

    public function isStripe()
    {
        return $this->payment_method === 'stripe';
    }

    public function isZelle()
    {
        return $this->payment_method === 'zelle';
    }
}
