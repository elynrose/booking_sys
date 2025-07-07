<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ScheduleType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'color',
        'is_active',
        'sort_order'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer'
    ];

    /**
     * Boot the model and add event listeners
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($scheduleType) {
            if (empty($scheduleType->slug)) {
                $scheduleType->slug = Str::slug($scheduleType->name);
            }
        });

        static::updating(function ($scheduleType) {
            if ($scheduleType->isDirty('name') && empty($scheduleType->slug)) {
                $scheduleType->slug = Str::slug($scheduleType->name);
            }
        });
    }

    /**
     * Get schedules for this type
     */
    public function schedules()
    {
        return $this->hasMany(Schedule::class, 'type', 'name');
    }

    /**
     * Scope to get only active types
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to order by sort order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Get the display name with icon
     */
    public function getDisplayNameAttribute()
    {
        if ($this->icon) {
            return "<i class=\"{$this->icon}\"></i> {$this->name}";
        }
        return $this->name;
    }
}
