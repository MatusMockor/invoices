<?php

declare(strict_types=1);

namespace App\Services;

use App\DataTransferObjects\PayBySquareData;
use App\Services\Interfaces\PayBySquare as PayBySquareContract;
use RuntimeException;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Transliterator;

/**
 * Service for generating Pay by Square QR codes.
 */
class PayBySquareService implements PayBySquareContract
{
    private const string BASE32_ALPHABET = '0123456789ABCDEFGHIJKLMNOPQRSTUV';

    private const int QR_SIZE = 200;

    private const int QR_MARGIN = 2;

    private const string LZMA_COMMAND = "/usr/bin/xz '--format=raw' '--lzma1=lc=3,lp=0,pb=2,dict=128KiB' '-c' '-'";

    /**
     * Generate a Pay by Square QR code for an invoice payment.
     *
     * @return string Base64 encoded QR code image
     */
    public function generateQrCode(PayBySquareData $data): string
    {
        $paymentData = $this->buildPaymentData($data);
        $dataWithCrc = $this->addCrcChecksum($paymentData);
        $compressedData = $this->compressWithLzma($dataWithCrc);
        $base32Data = $this->encodeToBase32($compressedData, strlen($dataWithCrc));

        return $this->generateQrCodeImage($base32Data);
    }

    private function buildPaymentData(PayBySquareData $data): string
    {
        $note = strtolower($this->removeAccents($data->note));

        return implode("\t", [
            0 => '',
            1 => '1',
            2 => implode("\t", [
                true,
                $data->amount,
                'EUR',
                date('Ymd'),
                $data->variableSymbol,
                $data->constantSymbol,
                $data->specificSymbol,
                '',
                $note,
                '1',
                $data->iban,
                $data->swift,
                '0',
                '0',
                $data->recipient ?? '',
            ]),
        ]);
    }

    private function addCrcChecksum(string $data): string
    {
        return strrev(hash('crc32b', $data, true)).$data;
    }

    private function compressWithLzma(string $data): string
    {
        $descriptors = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
        ];

        $process = proc_open(self::LZMA_COMMAND, $descriptors, $pipes);

        if (! is_resource($process)) {
            throw new RuntimeException('Failed to generate Pay by Square QR code');
        }

        fwrite($pipes[0], $data);
        fclose($pipes[0]);

        $compressedData = stream_get_contents($pipes[1]);
        fclose($pipes[1]);
        proc_close($process);

        return $compressedData;
    }

    private function encodeToBase32(string $compressedData, int $originalLength): string
    {
        $hexData = bin2hex("\x00\x00".pack('v', $originalLength).$compressedData);
        $binaryData = $this->hexToBinary($hexData);
        $binaryData = $this->padToMultipleOfFive($binaryData);

        return $this->binaryToBase32($binaryData);
    }

    private function hexToBinary(string $hexData): string
    {
        $binaryData = '';
        $hexLength = strlen($hexData);

        for ($i = 0; $i < $hexLength; $i++) {
            $binaryData .= str_pad(base_convert($hexData[$i], 16, 2), 4, '0', STR_PAD_LEFT);
        }

        return $binaryData;
    }

    private function padToMultipleOfFive(string $binaryData): string
    {
        $length = strlen($binaryData);
        $remainder = $length % 5;

        if ($remainder === 0) {
            return $binaryData;
        }

        return $binaryData.str_repeat('0', 5 - $remainder);
    }

    private function binaryToBase32(string $binaryData): string
    {
        $length = strlen($binaryData) / 5;
        $base32Data = '';

        for ($i = 0; $i < $length; $i++) {
            $base32Data .= self::BASE32_ALPHABET[bindec(substr($binaryData, $i * 5, 5))];
        }

        return $base32Data;
    }

    private function generateQrCodeImage(string $data): string
    {
        $qrCode = QrCode::format('png')
            ->size(self::QR_SIZE)
            ->errorCorrection('L')
            ->margin(self::QR_MARGIN)
            ->generate($data);

        $qrCodeString = (string) $qrCode;

        return 'data:image/png;base64,'.base64_encode($qrCodeString);
    }

    private function removeAccents(string $string): string
    {
        if (! preg_match('/[\x80-\xff]/', $string)) {
            return $string;
        }

        $transliterator = Transliterator::create('NFD; [:Nonspacing Mark:] Remove; NFC');

        if ($transliterator !== null) {
            $result = $transliterator->transliterate($string);

            return $result !== false ? $result : $string;
        }

        $result = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $string);

        return $result !== false ? $result : $string;
    }
}
