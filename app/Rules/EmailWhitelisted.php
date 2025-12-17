<?php

declare(strict_types=1);

namespace App\Rules;

use App\Repositories\Contracts\EmailWhitelistRepository as EmailWhitelistRepositoryContract;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
final class EmailWhitelisted implements ValidationRule
{
    public function __construct(
        private readonly EmailWhitelistRepositoryContract $whitelist
    ) {}

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // If whitelist is disabled, always pass validation
        if (! config('registration.email_whitelist_enabled')) {
            return;
        }

        // Check if email exists in whitelist
        if (! $this->whitelist->exists((string) $value)) {
            $fail('Tento email nie je autorizovaný pre registráciu.');
        }
    }
}
