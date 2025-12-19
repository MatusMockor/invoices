<?php

declare(strict_types=1);

namespace App\Services\FinancialData\Parsers;

/**
 * Strategy interface for parsing financial data from XML items.
 *
 * Implementing classes define specific parsing logic for different data types
 * (Company, DIC, VAT) while maintaining a consistent interface.
 */
interface FinancialDataParser
{
    /**
     * Parse an XML item array into structured data.
     *
     * @param  array<string, string>  $item  Raw XML item as associative array
     * @return array<string, mixed>|null Parsed data or null if item should be skipped
     */
    public function parse(array $item): ?array;
}
