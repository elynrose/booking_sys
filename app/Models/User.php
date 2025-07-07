<?php

namespace App\Models;

use App\Notifications\VerifyUserNotification;
use Carbon\Carbon;
use DateTimeInterface;
use Hash;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Cashier\Billable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable implements HasMedia
{
    use SoftDeletes, Notifiable, HasFactory, Billable, InteractsWithMedia, HasRoles;

    public $table = 'users';

    protected $hidden = [
        'remember_token', 'two_factor_code',
        'password',
    ];

    protected $dates = [
        'email_verified_at',
        'verified_at',
        'created_at',
        'updated_at',
        'deleted_at',
        'two_factor_expires_at',
    ];

    protected $fillable = [
        'name',
        'email',
        'email_verified_at',
        'password',
        'verified',
        'verified_at',
        'verification_token',
        'two_factor',
        'two_factor_code',
        'remember_token',
        'created_at',
        'updated_at',
        'deleted_at',
        'two_factor_expires_at',
        'role',
        'phone',
        'phone_number',
        'address',
        'photo',
        'member_id',
        'timezone',
        'sms_notifications_enabled',
        'sms_notification_preferences',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function generateTwoFactorCode()
    {
        $this->timestamps            = false;
        $this->two_factor_code       = rand(100000, 999999);
        $this->two_factor_expires_at = now()->addMinutes(15)->format(config('panel.date_format') . ' ' . config('panel.time_format'));
        $this->save();
    }

    public function resetTwoFactorCode()
    {
        $this->timestamps            = false;
        $this->two_factor_code       = null;
        $this->two_factor_expires_at = null;
        $this->save();
    }

    public function getEmailVerifiedAtAttribute($value)
    {
        return $value ? Carbon::createFromFormat('Y-m-d H:i:s', $value)->format(config('panel.date_format') . ' ' . config('panel.time_format')) : null;
    }

    public function setEmailVerifiedAtAttribute($value)
    {
        $this->attributes['email_verified_at'] = $value ? Carbon::createFromFormat(config('panel.date_format') . ' ' . config('panel.time_format'), $value)->format('Y-m-d H:i:s') : null;
    }

    public function setPasswordAttribute($input)
    {
        if ($input) {
            $this->attributes['password'] = app('hash')->needsRehash($input) ? Hash::make($input) : $input;
        }
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new \Illuminate\Auth\Notifications\ResetPassword($token));
    }

    public function getVerifiedAtAttribute($value)
    {
        return $value ? Carbon::createFromFormat('Y-m-d H:i:s', $value)->format(config('panel.date_format') . ' ' . config('panel.time_format')) : null;
    }

    public function setVerifiedAtAttribute($value)
    {
        $this->attributes['verified_at'] = $value ? Carbon::createFromFormat(config('panel.date_format') . ' ' . config('panel.time_format'), $value)->format('Y-m-d H:i:s') : null;
    }

    public function getTwoFactorExpiresAtAttribute($value)
    {
        return $value ? Carbon::createFromFormat('Y-m-d H:i:s', $value)->format(config('panel.date_format') . ' ' . config('panel.time_format')) : null;
    }

    public function setTwoFactorExpiresAtAttribute($value)
    {
        $this->attributes['two_factor_expires_at'] = $value ? Carbon::createFromFormat(config('panel.date_format') . ' ' . config('panel.time_format'), $value)->format('Y-m-d H:i:s') : null;
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function children()
    {
        return $this->hasMany(Child::class);
    }

    public function waitlists()
    {
        return $this->hasMany(Waitlist::class);
    }

    public function sessions()
    {
        return $this->hasMany(Session::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function getIsAdminAttribute()
    {
        return $this->hasRole('Admin');
    }

    public function getIsUserAttribute()
    {
        return $this->hasRole('User');
    }

    public function getIsTrainerAttribute()
    {
        return $this->hasRole('Trainer');
    }

    public function schedules()
    {
        return $this->belongsToMany(Schedule::class)
            ->withPivot(['check_in_date', 'check_in_time', 'check_out_time'])
            ->withTimestamps();
    }

    public function instructorSchedules()
    {
        return $this->hasMany(Schedule::class, 'instructor_id');
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    public function trainer()
    {
        return $this->hasOne(Trainer::class);
    }

    public function trainerAvailabilities()
    {
        return $this->hasMany(TrainerAvailability::class, 'trainer_id');
    }

    public function trainerReviews()
    {
        return $this->hasMany(TrainerReview::class);
    }

    /**
     * Get profile photo URL
     */
    public function getProfilePhotoUrlAttribute()
    {
        if (!$this->photo) {
            return null;
        }
        
        return config('filesystems.default') === 's3' 
            ? Storage::disk('s3')->url($this->photo) 
            : Storage::url($this->photo);
    }

    /**
     * Get photo URL (alias for profile photo URL)
     */
    public function getPhotoUrlAttribute()
    {
        return $this->profile_photo_url;
    }

    /**
     * Generate a unique member ID for the user
     */
    public function generateMemberId()
    {
        if ($this->member_id) {
            return $this->member_id;
        }

        do {
            $year = date('Y');
            $randomNumber = str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
            $memberId = "GYM-{$year}-{$randomNumber}";
        } while (User::where('member_id', $memberId)->exists());

        $this->update(['member_id' => $memberId]);
        return $memberId;
    }

    /**
     * Get SMS notification preferences
     */
    public function getSmsNotificationPreferencesAttribute($value)
    {
        return $value ? json_decode($value, true) : [
            'booking_created' => true,
            'booking_confirmed' => true,
            'booking_cancelled' => true,
            'payment_received' => true,
            'payment_failed' => true,
            'class_reminder' => true,
            'class_cancelled' => true,
            'class_rescheduled' => true,
        ];
    }

    /**
     * Set SMS notification preferences
     */
    public function setSmsNotificationPreferencesAttribute($value)
    {
        $this->attributes['sms_notification_preferences'] = json_encode($value);
    }

    /**
     * Check if user wants to receive specific SMS notification
     */
    public function wantsSmsNotification($type)
    {
        $preferences = $this->sms_notification_preferences;
        return $this->sms_notifications_enabled && 
               isset($preferences[$type]) && 
               $preferences[$type];
    }

    /**
     * Route notifications for the Twilio SMS channel
     */
    public function routeNotificationForTwilioSms()
    {
        return $this->phone_number;
    }
}
