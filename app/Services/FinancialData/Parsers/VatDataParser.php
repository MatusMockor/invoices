<?php

declare(strict_types=1);

namespace App\Services\FinancialData\Parsers;

/**
 * Parser for VAT (IC DPH) data from VAT data XML.
 *
 * XML structure:
 * <ITEM>
 *   <IC_DPH>SK1020000135</IC_DPH>
 *   <ICO>36151475</ICO>
 *   <NAZOV_DS>Company Name</NAZOV_DS>
 *   ...
 * </ITEM>
 */
final class VatDataParser implements FinancialDataParser
{
    /**
     * @param array<string, string> $item
     * @return array<string, mixed>|null
     */
    public function parse(array $item): ?array
    {
        $ico = trim($item['ICO'] ?? '');

        if (strlen($ico) !== 8 || ! ctype_digit($ico)) {
            return null;
        }

        return [
            'ico' => $ico,
            'ic_dph' => $this->extractValue($item, 'IC_DPH'),
        ];
    }

    /**
     * Extract and trim value from array, returning null if empty.
     *
     * @param array<string, string> $item
     */
    private function extractValue(array $item, string $key): ?string
    {
        $value = trim($item[$key] ?? '');

        return $value !== '' ? $value : null;
    }
}
