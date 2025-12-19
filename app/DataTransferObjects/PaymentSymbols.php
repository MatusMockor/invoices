<?php

declare(strict_types=1);

namespace App\DataTransferObjects;

/**
 * Data transfer object for payment reference symbols.
 *
 * Groups variable, constant, and specific symbols used in Slovak banking.
 */
final readonly class PaymentSymbols
{
    public function __construct(
        public string $variable = '',
        public string $constant = '',
        public string $specific = '',
    ) {}

    /**
     * Create from invoice model attributes.
     */
    public static function fromInvoice(
        ?string $variableSymbol,
        ?string $constantSymbol,
        ?string $specificSymbol
    ): self {
        return new self(
            variable: $variableSymbol ?? '',
            constant: $constantSymbol ?? '',
            specific: $specificSymbol ?? '',
        );
    }
}
