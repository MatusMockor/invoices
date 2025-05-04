<?php

namespace App\Services;

use SimpleSoftwareIO\QrCode\Facades\QrCode;

class PayBySquareService
{
    /**
     * Generate a PAY by square QR code for an invoice
     *
     * @param string $iban Beneficiary's IBAN
     * @param float $amount Payment amount
     * @param string $variableSymbol Variable symbol (usually invoice number)
     * @param string $currency Currency code (EUR by default)
     * @param string|null $message Optional message
     * @return string SVG QR code
     */
    public function generateQrCode(string $iban, float $amount, string $variableSymbol, string $currency = 'EUR', ?string $message = null): string
    {
        // Format the payment data according to the PAY by square standard
        $paymentData = $this->formatPaymentData($iban, $amount, $variableSymbol, $currency, $message);
        
        // Generate QR code as SVG (doesn't require Imagick)
        return QrCode::format('svg')
            ->size(200)
            ->errorCorrection('H')
            ->generate($paymentData);
    }
    
    /**
     * Format payment data according to PAY by square standard
     *
     * @param string $iban Beneficiary's IBAN
     * @param float $amount Payment amount
     * @param string $variableSymbol Variable symbol
     * @param string $currency Currency code
     * @param string|null $message Optional message
     * @return string Formatted payment data string
     */
    private function formatPaymentData(string $iban, float $amount, string $variableSymbol, string $currency, ?string $message): string
    {
        // Remove spaces from IBAN
        $iban = str_replace(' ', '', $iban);
        
        // Format amount to 2 decimal places
        $amount = number_format($amount, 2, '.', '');
        
        // Basic PAY by square format (simplified version)
        // For a full implementation, you would need to follow the official PAY by square specification
        $paymentData = "SPD*1.0*";
        $paymentData .= "ACC:{$iban}*";
        $paymentData .= "AM:{$amount}*";
        $paymentData .= "CC:{$currency}*";
        $paymentData .= "VS:{$variableSymbol}";
        
        if ($message) {
            $paymentData .= "*MSG:{$message}";
        }
        
        return $paymentData;
    }
}
