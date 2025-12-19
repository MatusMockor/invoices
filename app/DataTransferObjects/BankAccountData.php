<?php

declare(strict_types=1);

namespace App\DataTransferObjects;

/**
 * Data transfer object for bank account information.
 *
 * Groups IBAN and SWIFT/BIC codes used for payment transfers.
 */
final readonly class BankAccountData
{
    public function __construct(
        public string $iban,
        public string $swift,
    ) {}

    /**
     * Create from user company attributes.
     */
    public static function fromUserCompany(string $iban, string $swift): self
    {
        return new self(
            iban: str_replace(' ', '', $iban),
            swift: $swift,
        );
    }
}
