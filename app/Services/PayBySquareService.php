<?php

namespace App\Services;

use App\Services\Interfaces\PayBySquare as PayBySquareContract;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class PayBySquareService implements PayBySquareContract
{
    /**
     * Remove accents from a string
     */
    private function removeAccents(string $string): string
    {
        if (! preg_match('/[\x80-\xff]/', $string)) {
            return $string;
        }

        //        transliterator_transliterate('Any-Latin; Latin-ASCII', $inputString);
        $chars = [
            // Decompositions for Latin-1 Supplement
            chr(195).chr(128) => 'A', chr(195).chr(129) => 'A',
            chr(195).chr(130) => 'A', chr(195).chr(131) => 'A',
            chr(195).chr(132) => 'A', chr(195).chr(133) => 'A',
            chr(195).chr(135) => 'C', chr(195).chr(136) => 'E',
            chr(195).chr(137) => 'E', chr(195).chr(138) => 'E',
            chr(195).chr(139) => 'E', chr(195).chr(140) => 'I',
            chr(195).chr(141) => 'I', chr(195).chr(142) => 'I',
            chr(195).chr(143) => 'I', chr(195).chr(145) => 'N',
            chr(195).chr(146) => 'O', chr(195).chr(147) => 'O',
            chr(195).chr(148) => 'O', chr(195).chr(149) => 'O',
            chr(195).chr(150) => 'O', chr(195).chr(153) => 'U',
            chr(195).chr(154) => 'U', chr(195).chr(155) => 'U',
            chr(195).chr(156) => 'U', chr(195).chr(157) => 'Y',
            chr(195).chr(159) => 's', chr(195).chr(160) => 'a',
            chr(195).chr(161) => 'a', chr(195).chr(162) => 'a',
            chr(195).chr(163) => 'a', chr(195).chr(164) => 'a',
            chr(195).chr(165) => 'a', chr(195).chr(167) => 'c',
            chr(195).chr(168) => 'e', chr(195).chr(169) => 'e',
            chr(195).chr(170) => 'e', chr(195).chr(171) => 'e',
            chr(195).chr(172) => 'i', chr(195).chr(173) => 'i',
            chr(195).chr(174) => 'i', chr(195).chr(175) => 'i',
            chr(195).chr(177) => 'n', chr(195).chr(178) => 'o',
            chr(195).chr(179) => 'o', chr(195).chr(180) => 'o',
            chr(195).chr(181) => 'o', chr(195).chr(182) => 'o',
            chr(195).chr(182) => 'o', chr(195).chr(185) => 'u',
            chr(195).chr(186) => 'u', chr(195).chr(187) => 'u',
            chr(195).chr(188) => 'u', chr(195).chr(189) => 'y',
            chr(195).chr(191) => 'y',
            // Decompositions for Latin Extended-A
            chr(196).chr(128) => 'A', chr(196).chr(129) => 'a',
            chr(196).chr(130) => 'A', chr(196).chr(131) => 'a',
            chr(196).chr(132) => 'A', chr(196).chr(133) => 'a',
            chr(196).chr(134) => 'C', chr(196).chr(135) => 'c',
            chr(196).chr(136) => 'C', chr(196).chr(137) => 'c',
            chr(196).chr(138) => 'C', chr(196).chr(139) => 'c',
            chr(196).chr(140) => 'C', chr(196).chr(141) => 'c',
            chr(196).chr(142) => 'D', chr(196).chr(143) => 'd',
            chr(196).chr(144) => 'D', chr(196).chr(145) => 'd',
            chr(196).chr(146) => 'E', chr(196).chr(147) => 'e',
            chr(196).chr(148) => 'E', chr(196).chr(149) => 'e',
            chr(196).chr(150) => 'E', chr(196).chr(151) => 'e',
            chr(196).chr(152) => 'E', chr(196).chr(153) => 'e',
            chr(196).chr(154) => 'E', chr(196).chr(155) => 'e',
            chr(196).chr(156) => 'G', chr(196).chr(157) => 'g',
            chr(196).chr(158) => 'G', chr(196).chr(159) => 'g',
            chr(196).chr(160) => 'G', chr(196).chr(161) => 'g',
            chr(196).chr(162) => 'G', chr(196).chr(163) => 'g',
            chr(196).chr(164) => 'H', chr(196).chr(165) => 'h',
            chr(196).chr(166) => 'H', chr(196).chr(167) => 'h',
            chr(196).chr(168) => 'I', chr(196).chr(169) => 'i',
            chr(196).chr(170) => 'I', chr(196).chr(171) => 'i',
            chr(196).chr(172) => 'I', chr(196).chr(173) => 'i',
            chr(196).chr(174) => 'I', chr(196).chr(175) => 'i',
            chr(196).chr(176) => 'I', chr(196).chr(177) => 'i',
            chr(196).chr(178) => 'IJ', chr(196).chr(179) => 'ij',
            chr(196).chr(180) => 'J', chr(196).chr(181) => 'j',
            chr(196).chr(182) => 'K', chr(196).chr(183) => 'k',
            chr(196).chr(184) => 'k', chr(196).chr(185) => 'L',
            chr(196).chr(186) => 'l', chr(196).chr(187) => 'L',
            chr(196).chr(188) => 'l', chr(196).chr(189) => 'L',
            chr(196).chr(190) => 'l', chr(196).chr(191) => 'L',
            chr(197).chr(128) => 'l', chr(197).chr(129) => 'L',
            chr(197).chr(130) => 'l', chr(197).chr(131) => 'N',
            chr(197).chr(132) => 'n', chr(197).chr(133) => 'N',
            chr(197).chr(134) => 'n', chr(197).chr(135) => 'N',
            chr(197).chr(136) => 'n', chr(197).chr(137) => 'N',
            chr(197).chr(138) => 'n', chr(197).chr(139) => 'N',
            chr(197).chr(140) => 'O', chr(197).chr(141) => 'o',
            chr(197).chr(142) => 'O', chr(197).chr(143) => 'o',
            chr(197).chr(144) => 'O', chr(197).chr(145) => 'o',
            chr(197).chr(146) => 'OE', chr(197).chr(147) => 'oe',
            chr(197).chr(148) => 'R', chr(197).chr(149) => 'r',
            chr(197).chr(150) => 'R', chr(197).chr(151) => 'r',
            chr(197).chr(152) => 'R', chr(197).chr(153) => 'r',
            chr(197).chr(154) => 'S', chr(197).chr(155) => 's',
            chr(197).chr(156) => 'S', chr(197).chr(157) => 's',
            chr(197).chr(158) => 'S', chr(197).chr(159) => 's',
            chr(197).chr(160) => 'S', chr(197).chr(161) => 's',
            chr(197).chr(162) => 'T', chr(197).chr(163) => 't',
            chr(197).chr(164) => 'T', chr(197).chr(165) => 't',
            chr(197).chr(166) => 'T', chr(197).chr(167) => 't',
            chr(197).chr(168) => 'U', chr(197).chr(169) => 'u',
            chr(197).chr(170) => 'U', chr(197).chr(171) => 'u',
            chr(197).chr(172) => 'U', chr(197).chr(173) => 'u',
            chr(197).chr(174) => 'U', chr(197).chr(175) => 'u',
            chr(197).chr(176) => 'U', chr(197).chr(177) => 'u',
            chr(197).chr(178) => 'U', chr(197).chr(179) => 'u',
            chr(197).chr(180) => 'W', chr(197).chr(181) => 'w',
            chr(197).chr(182) => 'Y', chr(197).chr(183) => 'y',
            chr(197).chr(184) => 'Y', chr(197).chr(185) => 'Z',
            chr(197).chr(186) => 'z', chr(197).chr(187) => 'Z',
            chr(197).chr(188) => 'z', chr(197).chr(189) => 'Z',
            chr(197).chr(190) => 'z', chr(197).chr(191) => 's',
        ];

        return strtr($string, $chars);
    }

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
    ): string {
        // Clean and prepare the input data
        $note = strtolower($this->removeAccents($note ?? ''));
        $recipient = $recipient ?? '';
        $date = date('Ymd');

        // Create the Pay by Square data structure
        $data = implode("\t", [
            0 => '',
            1 => '1',
            2 => implode("\t", [
                true,
                $amount,                   // AMOUNT
                'EUR',                     // CURRENCY
                $date,                     // DATE
                $variableSymbol,           // VARIABLE SYMBOL
                $constantSymbol,           // CONSTANT SYMBOL
                $specificSymbol,           // SPECIFIC SYMBOL
                '',
                $note,                     // NOTE
                '1',
                $iban,                     // IBAN
                $swift,                    // SWIFT
                '0',
                '0',
                $recipient,                 // RECIPIENT
            ]),
        ]);

        // Generate CRC32B hash
        $data = strrev(hash('crc32b', $data, true)).$data;

        // Compress the data using LZMA
        $descriptors = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
        ];

        $process = proc_open("/usr/bin/xz '--format=raw' '--lzma1=lc=3,lp=0,pb=2,dict=128KiB' '-c' '-'", $descriptors, $pipes);

        if (is_resource($process)) {
            fwrite($pipes[0], $data);
            fclose($pipes[0]);

            $compressedData = stream_get_contents($pipes[1]);
            fclose($pipes[1]);

            proc_close($process);

            // Convert to hexadecimal
            $hexData = bin2hex("\x00\x00".pack('v', strlen($data)).$compressedData);

            // Convert to binary
            $binaryData = '';
            $hexLength = strlen($hexData);
            for ($i = 0; $i < $hexLength; $i++) {
                $binaryData .= str_pad(base_convert($hexData[$i], 16, 2), 4, '0', STR_PAD_LEFT);
            }

            // Pad to multiple of 5
            $length = strlen($binaryData);
            $remainder = $length % 5;

            if ($remainder > 0) {
                $padding = 5 - $remainder;
                $binaryData .= str_repeat('0', $padding);
                $length += $padding;
            }

            // Convert to base32
            $length = $length / 5;
            $base32Data = str_repeat('_', $length);

            for ($i = 0; $i < $length; $i++) {
                $base32Data[$i] = '0123456789ABCDEFGHIJKLMNOPQRSTUV'[bindec(substr($binaryData, $i * 5, 5))];
            }

            // Generate QR code
            $qrCode = QrCode::format('png')
                ->size(200)
                ->errorCorrection('L')
                ->margin(2)
                ->generate($base32Data);

            // Convert to base64
            $base64 = base64_encode($qrCode);

            return 'data:image/png;base64,'.$base64;
        }

        throw new \RuntimeException('Failed to generate Pay by Square QR code');
    }
}
