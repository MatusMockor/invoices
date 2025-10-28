<?php

declare(strict_types=1);

namespace App\Actions\User;

use App\Models\User;
use App\Repositories\Interfaces\UserRepository as UserRepositoryContract;
use Illuminate\Support\Facades\DB;

final class UserAccountDeleteAction
{
    public function __construct(
        private readonly UserRepositoryContract $userRepository
    ) {}

    /**
     * Soft delete user account and revoke all tokens.
     */
    public function handle(User $user): bool
    {
        return DB::transaction(function () use ($user): bool {
            // Revoke all Sanctum tokens
            $user->tokens()->delete();

            // Soft delete the user
            return $this->userRepository->softDelete($user);
        });
    }
}
