<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\EmailWhitelist;
use App\Repositories\Contracts\EmailWhitelistRepository as EmailWhitelistRepositoryContract;

final readonly class EmailWhitelistRepository implements EmailWhitelistRepositoryContract
{
    public function exists(string $email): bool
    {
        return EmailWhitelist::where('email', $email)->exists();
    }
}
