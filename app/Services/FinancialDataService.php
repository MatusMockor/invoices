<?php

declare(strict_types=1);

namespace App\Services;

use App\Services\FinancialData\FileDownloader;
use App\Services\FinancialData\Parsers\CompanyDataParser;
use App\Services\FinancialData\Parsers\DicDataParser;
use App\Services\FinancialData\Parsers\VatDataParser;
use App\Services\FinancialData\TempFileManager;
use App\Services\FinancialData\XmlStreamProcessor;
use App\Services\FinancialData\ZipExtractor;
use App\Services\Interfaces\FinancialDataService as FinancialDataServiceContract;
use Generator;

/**
 * Facade service for downloading and processing financial data from external sources.
 *
 * Coordinates the workflow between specialized helper classes:
 * - FileDownloader: Handles HTTP downloads with progress logging
 * - ZipExtractor: Extracts ZIP archives and finds XML files
 * - XmlStreamProcessor: Streams and parses large XML files
 * - TempFileManager: Manages temporary files and cleanup
 * - Strategy parsers: CompanyDataParser, DicDataParser, VatDataParser
 */
final class FinancialDataService implements FinancialDataServiceContract
{
    private readonly string $diskName;

    private readonly string $tempDir;

    private readonly FileDownloader $downloader;

    private readonly ZipExtractor $extractor;

    private readonly XmlStreamProcessor $xmlProcessor;

    private readonly TempFileManager $fileManager;

    private readonly CompanyDataParser $companyParser;

    private readonly DicDataParser $dicParser;

    private readonly VatDataParser $vatParser;

    public function __construct()
    {
        $this->diskName = config('filesystems.default');
        $this->tempDir = config('financial_data.temp_path');

        $this->downloader = new FileDownloader($this->diskName, $this->tempDir);
        $this->extractor = new ZipExtractor($this->diskName, $this->tempDir);
        $this->xmlProcessor = new XmlStreamProcessor($this->diskName, $this->tempDir);
        $this->fileManager = new TempFileManager($this->diskName, $this->tempDir);

        $this->companyParser = new CompanyDataParser;
        $this->dicParser = new DicDataParser;
        $this->vatParser = new VatDataParser;
    }

    /**
     * Download and extract company data from the financial data source.
     *
     * @return Generator<array<string, mixed>>
     */
    public function downloadAndExtractCompanyData(): Generator
    {
        $this->fileManager->ensureTempDirectoryExists();

        try {
            $url = config('financial_data.source_url');
            $fileName = $this->extractFileNameFromUrl($url);

            $this->downloader->download(
                url: $url,
                fileName: $fileName,
                logPrefix: 'financial data'
            );

            $state = $this->extractor->extract($fileName, 'ZIP');

            yield from $this->xmlProcessor->process($state, $this->companyParser, 'company');
        } finally {
            $this->cleanup();
        }
    }

    /**
     * Parse XML element into company data array.
     *
     * @param  array<string, string>  $item
     * @return array<string, mixed>|null
     */
    public function parseCompanyData(array $item): ?array
    {
        return $this->companyParser->parse($item);
    }

    /**
     * Download and extract DIC data from the financial data source.
     *
     * @return Generator<array<string, mixed>>
     */
    public function downloadAndExtractDicData(): Generator
    {
        $this->fileManager->ensureTempDirectoryExists();

        try {
            $url = config('financial_data.source_url');
            $fileName = $this->extractFileNameFromUrl($url);

            $this->downloader->download(
                url: $url,
                fileName: $fileName,
                logPrefix: 'financial data'
            );

            $state = $this->extractor->extract($fileName, 'ZIP');

            yield from $this->xmlProcessor->process($state, $this->dicParser, 'DIC');
        } finally {
            $this->cleanup();
        }
    }

    /**
     * Parse DIC XML element into data array.
     *
     * @param  array<string, string>  $item
     * @return array<string, mixed>|null
     */
    public function parseDicData(array $item): ?array
    {
        return $this->dicParser->parse($item);
    }

    /**
     * Download and extract VAT data from the VAT data source.
     *
     * @return Generator<array<string, mixed>>
     */
    public function downloadAndExtractVatData(): Generator
    {
        $this->fileManager->ensureTempDirectoryExists();

        try {
            $url = config('financial_data.vat_source_url');
            $fileName = $this->extractFileNameFromUrl($url);

            $this->downloader->download(
                url: $url,
                fileName: $fileName,
                logPrefix: 'VAT data'
            );

            $state = $this->extractor->extract($fileName, 'VAT ZIP');

            yield from $this->xmlProcessor->process($state, $this->vatParser, 'VAT');
        } finally {
            $this->cleanup();
        }
    }

    /**
     * Parse VAT XML element into data array.
     *
     * @param  array<string, string>  $item
     * @return array<string, mixed>|null
     */
    public function parseVatData(array $item): ?array
    {
        return $this->vatParser->parse($item);
    }

    /**
     * Clean up temporary files and directories.
     */
    public function cleanup(): void
    {
        $this->fileManager->cleanup();
    }

    /**
     * Extract filename from URL.
     */
    private function extractFileNameFromUrl(string $url): string
    {
        return basename(parse_url($url, PHP_URL_PATH) ?? 'download.zip');
    }
}
