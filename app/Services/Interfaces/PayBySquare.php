<?php

declare(strict_types=1);

namespace App\Services\Interfaces;

use App\DataTransferObjects\PayBySquareData;

/**
 * Interface for Pay by Square QR code generation.
 */
interface PayBySquare
{
    /**
     * Generate a Pay by Square QR code for an invoice payment.
     *
     * @return string Base64 encoded QR code image
     */
    public function generateQrCode(PayBySquareData $data): string;
}
