<?php

namespace App\Observers;

use App\Events\DataChanged;
use App\Events\NewSignup;
use App\Events\UserReturned;
use App\Events\UserVerified;
use App\Models\User;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        // Fire new signup event
        event(new NewSignup($user));
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        // Check if email was verified
        if ($user->wasChanged('email_verified_at') && $user->email_verified_at) {
            // Only fire UserVerified event if user has a verification token and is not already verified
            if ($user->verification_token && !$user->verified) {
                event(new UserVerified($user));
            }
        }

        // Check if user returned after being inactive (last_login_at changed)
        if ($user->wasChanged('last_login_at') && $user->last_login_at) {
            $lastLogin = $user->getOriginal('last_login_at');
            if ($lastLogin && now()->diffInDays($lastLogin) > 30) {
                event(new UserReturned($user, $lastLogin));
            }
        }

        // Check for data changes (excluding login-related fields)
        $sensitiveFields = ['name', 'email', 'phone', 'address'];
        $changedFields = array_intersect($sensitiveFields, array_keys($user->getChanges()));
        
        if (!empty($changedFields)) {
            $oldData = [];
            foreach ($changedFields as $field) {
                $oldData[$field] = $user->getOriginal($field);
            }
            event(new DataChanged($user, $changedFields, $oldData));
        }
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        // Handle user deletion if needed
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
        // Handle user restoration if needed
    }

    /**
     * Handle the User "force deleted" event.
     */
    public function forceDeleted(User $user): void
    {
        // Handle force deletion if needed
    }
} 