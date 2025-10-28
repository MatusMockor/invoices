<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\User;

class UserObserver
{
    /**
     * Handle the User "updating" event.
     */
    public function updating(User $user): void
    {
        // If email is being changed, reset email verification
        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }
    }
}
