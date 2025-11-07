<?php

declare(strict_types=1);

namespace App\Actions\User;

use App\DTOs\User\UserPasswordUpdateDTO;
use App\Models\User;
use App\Repositories\Contracts\UserRepository as UserRepositoryContract;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

final class UserPasswordUpdateAction
{
    public function __construct(
        private readonly UserRepositoryContract $userRepository
    ) {}

    /**
     * Update user's password.
     */
    public function handle(User $user, UserPasswordUpdateDTO $dto): bool
    {
        return DB::transaction(function () use ($user, $dto): bool {
            return $this->userRepository->update($user, [
                'password' => Hash::make($dto->password),
            ]);
        });
    }
}
