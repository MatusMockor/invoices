<?php

declare(strict_types=1);

namespace App\DTOs\User;

use App\Http\Requests\LoginRequest;

final readonly class LoginDTO
{
    public function __construct(
        public string $email,
        public string $password,
    ) {}

    public static function fromFormRequest(LoginRequest $request): self
    {
        return new self(
            email: $request->getEmail(),
            password: $request->getPassword(),
        );
    }
}
