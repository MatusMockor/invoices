<?php

declare(strict_types=1);

namespace App\Actions\User;

use App\Models\User;
use Illuminate\Support\Facades\DB;

final class UserLogoutAction
{
    /**
     * Revoke the user's current access token.
     */
    public function handle(User $user): void
    {
        DB::transaction(function () use ($user): void {
            $user->currentAccessToken()->delete();
        });
    }
}
