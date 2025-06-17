<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Child extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'date_of_birth',
        'gender',
        'notes'
    ];

    protected $casts = [
        'date_of_birth' => 'date'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
} 