<?php

declare(strict_types=1);

namespace App\Services\FinancialData\Parsers;

/**
 * Parser for company data from financial data XML.
 *
 * XML structure:
 * <ITEM>
 *   <ICO>36553689</ICO>
 *   <DIC>2021738367</DIC>
 *   <NAZOV_DS>Company Name</NAZOV_DS>
 *   <OBEC>City</OBEC>
 *   <PSC>90201</PSC>
 *   <ULICA_CISLO>Street 123</ULICA_CISLO>
 *   <NAZOV_STATU>Slovensko</NAZOV_STATU>
 * </ITEM>
 */
final class CompanyDataParser implements FinancialDataParser
{
    /**
     * @param array<string, string> $item
     * @return array<string, mixed>|null
     */
    public function parse(array $item): ?array
    {
        if (! array_key_exists('ICO', $item)) {
            return null;
        }

        $ico = trim($item['ICO']);

        return [
            'ico' => $ico,
            'name' => trim($item['NAZOV_DS']),
            'street' => $this->extractValue($item, 'ULICA_CISLO'),
            'city' => $this->extractValue($item, 'OBEC'),
            'postal_code' => $this->extractValue($item, 'PSC'),
            'country' => $this->extractValue($item, 'NAZOV_STATU'),
            'dic' => $this->extractValue($item, 'DIC'),
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
