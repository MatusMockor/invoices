<?php

declare(strict_types=1);

namespace App\DataTransferObjects;

/**
 * Data transfer object for Pay by Square payment information.
 */
final readonly class PayBySquareData
{
    public function __construct(
        public BankAccountData $bankAccount,
        public float $amount,
        public PaymentSymbols $symbols = new PaymentSymbols,
        public string $note = '',
    ) {}

    /**
     * Helper to access IBAN directly.
     */
    public function getIban(): string
    {
        return $this->bankAccount->iban;
    }

    /**
     * Helper to access SWIFT directly.
     */
    public function getSwift(): string
    {
        return $this->bankAccount->swift;
    }
}
