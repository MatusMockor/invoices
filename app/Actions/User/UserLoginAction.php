<?php

declare(strict_types=1);

namespace App\Actions\User;

use App\DTOs\User\LoginDTO;
use App\DTOs\User\LoginResultDTO;
use App\Repositories\Contracts\UserRepository as UserRepositoryContract;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

final class UserLoginAction
{
    public function __construct(
        private readonly UserRepositoryContract $userRepository,
    ) {}

    /**
     * Authenticate user and create access token.
     *
     * @throws ValidationException
     */
    public function handle(LoginDTO $dto): LoginResultDTO
    {
        $user = $this->userRepository->findByEmail($dto->email);

        if (! $user) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (! Hash::check($dto->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $token = $user->createToken('auth-token')->accessToken;

        return new LoginResultDTO(
            user: $user,
            token: $token,
        );
    }
}
