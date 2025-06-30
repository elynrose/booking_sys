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
        'address',
        'member_id',
        'timezone',
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
        $this->notify(new ResetPassword($token));
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
        return $this->roles()->where('id', 1)->exists();
    }

    public function getIsUserAttribute()
    {
        return $this->roles()->where('id', 2)->exists();
    }

    public function getIsTrainerAttribute()
    {
        return $this->roles()->where('id', 3)->exists();
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

    public function hasRole($role)
    {
        try {
            // Check if user is authenticated and has roles relationship
            if (!$this->exists || !$this->roles) {
                return false;
            }
            
            return $this->roles()->where('title', $role)->exists();
        } catch (\Exception $e) {
            // Log the error but don't crash the application
            \Log::error('hasRole error: ' . $e->getMessage(), [
                'user_id' => $this->id ?? 'unknown',
                'role' => $role,
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Static helper method for safe role checking
     */
    public static function hasRoleSafe($role)
    {
        try {
            if (!auth()->check()) {
                return false;
            }
            
            return auth()->user()->hasRole($role);
        } catch (\Exception $e) {
            \Log::error('hasRoleSafe error: ' . $e->getMessage(), [
                'role' => $role,
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }
}
