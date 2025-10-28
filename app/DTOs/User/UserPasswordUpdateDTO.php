<?php

declare(strict_types=1);

namespace App\DTOs\User;

use App\Http\Requests\UpdatePasswordRequest;

final readonly class UserPasswordUpdateDTO
{
    public function __construct(
        public string $password
    ) {}

    public static function fromRequest(UpdatePasswordRequest $request): self
    {
        return new self(
            password: $request->validated('password')
        );
    }
}
