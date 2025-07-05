<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class TrainerAvailability extends Model
{
    use HasFactory;

    protected $fillable = [
        'trainer_id',
        'schedule_id',
        'date',
        'start_time',
        'end_time',
        'status',
        'notes'
    ];

    protected $casts = [
        'date' => 'date',
        // 'start_time' and 'end_time' are stored as strings (H:i:s)
    ];

    /**
     * Get start_time as Carbon object
     */
    public function getStartTimeAttribute($value)
    {
        if (is_string($value)) {
            return Carbon::createFromFormat('H:i:s', $value);
        }
        return $value;
    }

    /**
     * Get end_time as Carbon object
     */
    public function getEndTimeAttribute($value)
    {
        if (is_string($value)) {
            return Carbon::createFromFormat('H:i:s', $value);
        }
        return $value;
    }

    public function trainer()
    {
        return $this->belongsTo(User::class, 'trainer_id');
    }

    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }

    /**
     * Check if this availability is available for booking
     */
    public function isAvailableForBooking()
    {
        return $this->status === 'available' && 
               $this->date >= Carbon::today() &&
               $this->schedule->status === 'active';
    }

    /**
     * Get the next available session for a schedule
     */
    public static function getNextAvailableSession($scheduleId, $trainerId = null)
    {
        $query = self::where('schedule_id', $scheduleId)
            ->where('status', 'available')
            ->where('date', '>=', Carbon::today())
            ->orderBy('date')
            ->orderBy('start_time');

        if ($trainerId) {
            $query->where('trainer_id', $trainerId);
        }

        return $query->first();
    }

    /**
     * Get all available sessions for a schedule in a date range
     */
    public static function getAvailableSessions($scheduleId, $startDate = null, $endDate = null, $trainerId = null)
    {
        $query = self::where('schedule_id', $scheduleId)
            ->where('status', 'available')
            ->where('date', '>=', $startDate ?? Carbon::today());

        if ($endDate) {
            $query->where('date', '<=', $endDate);
        }

        if ($trainerId) {
            $query->where('trainer_id', $trainerId);
        }

        return $query->orderBy('date')
            ->orderBy('start_time')
            ->get();
    }

    /**
     * Create recurring availability for a schedule
     */
    public static function createRecurringAvailability($scheduleId, $trainerId, $startDate, $endDate, $daysOfWeek, $startTime, $endTime)
    {
        $currentDate = Carbon::parse($startDate);
        $endDate = Carbon::parse($endDate);
        
        while ($currentDate <= $endDate) {
            if (in_array($currentDate->dayOfWeek, $daysOfWeek)) {
                self::updateOrCreate(
                    [
                        'trainer_id' => $trainerId,
                        'schedule_id' => $scheduleId,
                        'date' => $currentDate->format('Y-m-d')
                    ],
                    [
                        'start_time' => $startTime,
                        'end_time' => $endTime,
                        'status' => 'available'
                    ]
                );
            }
            $currentDate->addDay();
        }
    }

    /**
     * Bulk update availability status
     */
    public static function bulkUpdateStatus($scheduleId, $trainerId, $dates, $status)
    {
        return self::where('schedule_id', $scheduleId)
            ->where('trainer_id', $trainerId)
            ->whereIn('date', $dates)
            ->update(['status' => $status]);
    }
}
