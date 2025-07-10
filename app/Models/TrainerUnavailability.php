<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class TrainerUnavailability extends Model
{
    use HasFactory;

    protected $fillable = [
        'trainer_id',
        'schedule_id',
        'date',
        'start_time',
        'end_time',
        'reason',
        'notes'
    ];

    protected $casts = [
        'date' => 'date',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    public function trainer()
    {
        return $this->belongsTo(Trainer::class, 'trainer_id');
    }

    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }

    /**
     * Check if trainer is unavailable for a specific date and time
     */
    public static function isTrainerUnavailable($trainerId, $scheduleId, $date, $time = null)
    {
        $query = self::where('trainer_id', $trainerId)
            ->where('date', $date);

        // If schedule_id is provided, check both specific and general unavailability
        if ($scheduleId) {
            $query->where(function($q) use ($scheduleId) {
                $q->where('schedule_id', $scheduleId)
                  ->orWhereNull('schedule_id');
            });
        } else {
            $query->whereNull('schedule_id');
        }

        // If time is provided, check time-specific unavailability
        if ($time) {
            $timeStr = $time instanceof Carbon ? $time->format('H:i:s') : $time;
            $query->where(function($q) use ($timeStr) {
                $q->whereNull('start_time') // All day unavailability
                  ->orWhere(function($subQ) use ($timeStr) {
                      $subQ->where('start_time', '<=', $timeStr)
                           ->where('end_time', '>', $timeStr);
                  });
            });
        }

        return $query->exists();
    }

    /**
     * Get all unavailability periods for a trainer in a date range
     */
    public static function getUnavailabilityPeriods($trainerId, $scheduleId = null, $startDate = null, $endDate = null)
    {
        $query = self::where('trainer_id', $trainerId);

        if ($scheduleId) {
            $query->where(function($q) use ($scheduleId) {
                $q->where('schedule_id', $scheduleId)
                  ->orWhereNull('schedule_id');
            });
        } else {
            $query->whereNull('schedule_id');
        }

        if ($startDate) {
            $query->where('date', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('date', '<=', $endDate);
        }

        return $query->orderBy('date')
            ->orderBy('start_time')
            ->get();
    }

    /**
     * Check if a specific time period is unavailable
     */
    public function coversTime($time)
    {
        $timeStr = $time instanceof Carbon ? $time->format('H:i:s') : $time;
        
        // If no start/end time, it's all day unavailability
        if (!$this->start_time && !$this->end_time) {
            return true;
        }

        // If only start time is set, check if time is after start
        if ($this->start_time && !$this->end_time) {
            return $timeStr >= $this->start_time->format('H:i:s');
        }

        // If only end time is set, check if time is before end
        if (!$this->start_time && $this->end_time) {
            return $timeStr <= $this->end_time->format('H:i:s');
        }

        // If both times are set, check if time is within range
        if ($this->start_time && $this->end_time) {
            return $timeStr >= $this->start_time->format('H:i:s') && 
                   $timeStr <= $this->end_time->format('H:i:s');
        }

        return false;
    }
} 