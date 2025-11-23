<?php

declare(strict_types=1);

namespace App\DTOs\User;

use App\Http\Requests\RegisterRequest;

final readonly class SimpleUserRegistrationDTO
{
    public function __construct(
        public string $firstName,
        public string $lastName,
        public string $email,
        public string $password,
    ) {}

    public static function fromFormRequest(RegisterRequest $request): self
    {
        return new self(
            firstName: $request->getFirstName(),
            lastName: $request->getLastName(),
            email: $request->getEmail(),
            password: $request->getPassword(),
        );
    }
}
