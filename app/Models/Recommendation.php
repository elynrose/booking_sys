<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Recommendation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'trainer_id',
        'child_id',
        'title',
        'content',
        'type',
        'priority',
        'is_public',
        'read_at',
    ];

    protected $casts = [
        'is_public' => 'boolean',
        'read_at' => 'datetime',
    ];

    public function trainer()
    {
        return $this->belongsTo(User::class, 'trainer_id');
    }

    public function child()
    {
        return $this->belongsTo(Child::class);
    }

    public function attachments()
    {
        return $this->hasMany(RecommendationAttachment::class);
    }

    public function responses()
    {
        return $this->hasMany(RecommendationResponse::class)->orderBy('created_at', 'asc');
    }

    public function markAsRead()
    {
        $this->update(['read_at' => now()]);
    }

    public function isRead()
    {
        return !is_null($this->read_at);
    }

    public function getPriorityColorAttribute()
    {
        return [
            'low' => 'success',
            'medium' => 'warning',
            'high' => 'danger',
        ][$this->priority] ?? 'secondary';
    }

    public function getTypeIconAttribute()
    {
        return [
            'progress' => 'fas fa-chart-line',
            'improvement' => 'fas fa-arrow-up',
            'achievement' => 'fas fa-trophy',
            'general' => 'fas fa-comment',
        ][$this->type] ?? 'fas fa-comment';
    }
}
