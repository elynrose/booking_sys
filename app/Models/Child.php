<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Child extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'date_of_birth',
        'gender',
        'notes',
        'photo',
        'address',
        'parent_consent'
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'parent_consent' => 'boolean'
    ];

    protected $appends = ['age'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getAgeAttribute()
    {
        return $this->date_of_birth ? Carbon::parse($this->date_of_birth)->age : null;
    }

    public function recommendations()
    {
        return $this->hasMany(Recommendation::class);
    }

    public function recommendationResponses()
    {
        return $this->hasManyThrough(RecommendationResponse::class, Recommendation::class);
    }

    public function getPhotoUrlAttribute()
    {
        if ($this->photo) {
            return \Storage::disk('s3')->url($this->photo);
        }
        return null;
    }
} 