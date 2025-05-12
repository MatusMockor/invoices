<?php

namespace App\Services\Interfaces;

interface PayBySquare
{
    /**
     * Generate a Pay by Square QR code for an invoice payment
     *
     * @param  string  $iban  Recipient's IBAN (no spaces)
     * @param  string  $swift  Recipient's BIC/SWIFT code
     * @param  float  $amount  Payment amount
     * @param  string  $variableSymbol  Variable symbol (max 10 digits)
     * @param  string  $constantSymbol  Constant symbol (max 4 digits)
     * @param  string  $specificSymbol  Specific symbol (max 10 digits)
     * @param  string  $note  Payment note
     * @param  string|null  $recipient  Recipient name
     * @return string Base64 encoded QR code image
     */
    public function generateQrCode(
        string $iban,
        string $swift,
        float $amount,
        string $variableSymbol = '',
        string $constantSymbol = '',
        string $specificSymbol = '',
        string $note = '',
        ?string $recipient = null
    ): string;
}
