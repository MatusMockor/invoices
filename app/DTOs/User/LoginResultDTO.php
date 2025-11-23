<?php

declare(strict_types=1);

namespace App\DTOs\User;

use App\Models\User;

final readonly class LoginResultDTO
{
    public function __construct(
        public User $user,
        public string $token,
    ) {}
}
