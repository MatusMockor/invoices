<?php

declare(strict_types=1);

namespace App\DataTransferObjects;

/**
 * Data transfer object for Pay by Square payment information.
 */
final readonly class PayBySquareData
{
    public function __construct(
        public string $iban,
        public string $swift,
        public float $amount,
        public string $variableSymbol = '',
        public string $constantSymbol = '',
        public string $specificSymbol = '',
        public string $note = '',
        public ?string $recipient = null,
    ) {}
}
