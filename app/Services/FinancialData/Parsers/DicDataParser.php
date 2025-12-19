<?php

declare(strict_types=1);

namespace App\Services\FinancialData\Parsers;

/**
 * Parser for DIC (tax identification number) data from financial data XML.
 *
 * XML structure:
 * <ITEM>
 *   <ICO>36553689</ICO>
 *   <DIC>2021738367</DIC>
 *   ...
 * </ITEM>
 */
final class DicDataParser implements FinancialDataParser
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
            'dic' => $this->extractValue($item, 'DIC'),
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
