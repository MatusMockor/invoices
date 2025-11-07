<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

interface EmailWhitelistRepository
{
    public function exists(string $email): bool;
}
