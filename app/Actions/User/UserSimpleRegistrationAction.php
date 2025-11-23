<?php

declare(strict_types=1);

namespace App\Actions\User;

use App\DTOs\User\SimpleUserRegistrationDTO;
use App\Models\User;
use App\Repositories\Contracts\UserRepository as UserRepositoryContract;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

final class UserSimpleRegistrationAction
{
    public function __construct(
        private readonly UserRepositoryContract $userRepository,
    ) {}

    /**
     * Register a new user (simple registration without company).
     */
    public function handle(SimpleUserRegistrationDTO $dto): User
    {
        return DB::transaction(function () use ($dto): User {
            return $this->userRepository->create([
                'first_name' => $dto->firstName,
                'last_name' => $dto->lastName,
                'email' => $dto->email,
                'password' => Hash::make($dto->password),
            ]);
        });
    }
}
