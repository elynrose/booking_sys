<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RecommendationResponse extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'recommendation_id',
        'user_id',
        'content',
        'is_public',
        'read_at',
    ];

    protected $casts = [
        'is_public' => 'boolean',
        'read_at' => 'datetime',
    ];

    public function recommendation()
    {
        return $this->belongsTo(Recommendation::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function markAsRead()
    {
        $this->update(['read_at' => now()]);
    }

    public function isRead()
    {
        return !is_null($this->read_at);
    }

    public function getFormattedContentAttribute()
    {
        return nl2br(e($this->content));
    }
}
