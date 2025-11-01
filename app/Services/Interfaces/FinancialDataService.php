<?php

declare(strict_types=1);

namespace App\Services\Interfaces;

use Generator;

interface FinancialDataService
{
    /**
     * Download and extract company data from the financial data source.
     *
     * @return Generator<array<string, mixed>>
     */
    public function downloadAndExtractCompanyData(): Generator;

    /**
     * Parse a single CSV line into company data array.
     *
     * @param  array<int, string>  $line
     * @return array<string, mixed>|null
     */
    public function parseCompanyData(array $line): ?array;

    /**
     * Download and extract VAT data from the VAT data source.
     *
     * @return Generator<array<string, mixed>>
     */
    public function downloadAndExtractVatData(): Generator;

    /**
     * Parse VAT XML element into data array.
     *
     * @param  array<string, string>  $item
     * @return array<string, mixed>|null
     */
    public function parseVatData(array $item): ?array;

    /**
     * Clean up temporary files and directories.
     */
    public function cleanup(): void;
}
