<?php

declare(strict_types=1);

namespace App\Services;

use App\DataTransferObjects\FinancialDataState;
use App\Services\Interfaces\FinancialDataService as FinancialDataServiceContract;
use Closure;
use Generator;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use SimpleXMLElement;
use XMLReader;
use ZipArchive;

/**
 * Service for downloading and processing financial data from external sources.
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 * @SuppressWarnings(PHPMD.NPathComplexity)
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 */
class FinancialDataService implements FinancialDataServiceContract
{
    private const int MEMORY_CLEANUP_INTERVAL = 5000;

    private const int DOWNLOAD_TIMEOUT_SECONDS = 600;

    private const int LOG_PROGRESS_BYTES = 10485760; // 10MB

    private const int BYTES_PER_MB = 1048576;

    private string $diskName = 'local';

    private string $tempDir;

    private string $zipFileName = 'companies.zip';

    private string $vatZipFileName = 'vat.zip';

    public function __construct()
    {
        $this->tempDir = config('financial_data.temp_path');
    }

    /**
     * Download and extract company data from the financial data source.
     *
     * @return Generator<array<string, mixed>>
     */
    public function downloadAndExtractCompanyData(): Generator
    {
        $this->ensureTempDirectoryExists();

        try {
            $this->downloadZipFile();
            $state = $this->extractZipFile();

            yield from $this->processCompanyData($state);
        } finally {
            $this->cleanup();
        }
    }

    /**
     * Parse XML element into company data array.
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
     *
     * @param  array<string, string>  $item
     * @return array<string, mixed>|null
     */
    public function parseCompanyData(array $item): ?array
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
     * Download and extract DIC data from the financial data source.
     *
     * @return Generator<array<string, mixed>>
     */
    public function downloadAndExtractDicData(): Generator
    {
        $this->ensureTempDirectoryExists();

        try {
            $this->downloadZipFile();
            $state = $this->extractZipFile();

            yield from $this->processDicData($state);
        } finally {
            $this->cleanup();
        }
    }

    /**
     * Parse DIC XML element into data array.
     *
     * XML structure:
     * <ITEM>
     *   <ICO>36553689</ICO>
     *   <DIC>2021738367</DIC>
     *   ...
     * </ITEM>
     *
     * @param  array<string, string>  $item
     * @return array<string, mixed>|null
     */
    public function parseDicData(array $item): ?array
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
     * Download and extract VAT data from the VAT data source.
     *
     * @return Generator<array<string, mixed>>
     */
    public function downloadAndExtractVatData(): Generator
    {
        $this->ensureTempDirectoryExists();

        try {
            $this->downloadVatZipFile();
            $state = $this->extractVatZipFile();

            yield from $this->processVatData($state);
        } finally {
            $this->cleanupVatFiles();
        }
    }

    /**
     * Parse VAT XML element into data array.
     *
     * XML structure:
     * <ITEM>
     *   <IC_DPH>SK1020000135</IC_DPH>
     *   <ICO>36151475</ICO>
     *   <NAZOV_DS>Company Name</NAZOV_DS>
     *   ...
     * </ITEM>
     *
     * @param  array<string, string>  $item
     * @return array<string, mixed>|null
     */
    public function parseVatData(array $item): ?array
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
     * Clean up temporary files and directories.
     */
    public function cleanup(): void
    {
        $disk = Storage::disk($this->diskName);

        // Clean up company ZIP file
        $zipPath = $this->tempDir.'/'.$this->zipFileName;
        if ($disk->exists($zipPath)) {
            $disk->delete($zipPath);
        }

        // Clean up VAT files
        $this->cleanupVatFiles();

        // Clean up all XML files in temp directory
        if ($disk->exists($this->tempDir)) {
            $files = $disk->files($this->tempDir);
            foreach ($files as $file) {
                if (str_ends_with(strtolower($file), '.xml')) {
                    $disk->delete($file);
                }
            }

            // Clean up directory if empty
            $remainingFiles = $disk->files($this->tempDir);
            if (empty($remainingFiles)) {
                $disk->deleteDirectory($this->tempDir);
            }
        }
    }

    /**
     * Ensure the temporary directory exists.
     */
    private function ensureTempDirectoryExists(): void
    {
        $disk = Storage::disk($this->diskName);

        if (! $disk->exists($this->tempDir)) {
            $disk->makeDirectory($this->tempDir);
        }
    }

    /**
     * Download the ZIP file from the configured URL using sink for reliable streaming.
     */
    private function downloadZipFile(): void
    {
        $this->downloadFile(
            configKey: 'financial_data.source_url',
            fileName: $this->zipFileName,
            logPrefix: 'financial data'
        );
    }

    /**
     * Extract the ZIP file and find the XML file.
     */
    private function extractZipFile(): FinancialDataState
    {
        return $this->extractZip($this->zipFileName, 'ZIP');
    }

    /**
     * Process the company data from the extracted XML file using XMLReader for streaming.
     *
     * @return Generator<array<string, mixed>>
     */
    private function processCompanyData(FinancialDataState $state): Generator
    {
        yield from $this->processXmlData(
            state: $state,
            parser: fn (array $item): ?array => $this->parseCompanyData($item),
            logPrefix: 'company'
        );
    }

    /**
     * Process the DIC data from the extracted XML file using XMLReader for streaming.
     *
     * @return Generator<array<string, mixed>>
     */
    private function processDicData(FinancialDataState $state): Generator
    {
        yield from $this->processXmlData(
            state: $state,
            parser: fn (array $item): ?array => $this->parseDicData($item),
            logPrefix: 'DIC'
        );
    }

    /**
     * Download the VAT ZIP file from the configured URL.
     */
    private function downloadVatZipFile(): void
    {
        $this->downloadFile(
            configKey: 'financial_data.vat_source_url',
            fileName: $this->vatZipFileName,
            logPrefix: 'VAT data'
        );
    }

    /**
     * Download a file from a configured URL to the temp directory.
     */
    private function downloadFile(string $configKey, string $fileName, string $logPrefix): void
    {
        $url = config($configKey);

        Log::info("Downloading {$logPrefix} from: ".$url);

        $disk = Storage::disk($this->diskName);
        $zipPath = $this->tempDir.'/'.$fileName;
        $fullPath = $disk->path($zipPath);

        $response = Http::timeout(self::DOWNLOAD_TIMEOUT_SECONDS)
            ->withOptions([
                'sink' => $fullPath,
                'progress' => $this->createProgressCallback($logPrefix),
            ])
            ->get($url);

        if (! $response->successful()) {
            $this->deleteFileIfExists($disk, $zipPath);

            throw new RuntimeException("Failed to download {$logPrefix}: HTTP ".$response->status());
        }

        if (! $disk->exists($zipPath)) {
            throw new RuntimeException("{$logPrefix} download completed but file does not exist: ".$zipPath);
        }

        $this->logDownloadSuccess($disk, $zipPath, $logPrefix);
    }

    /**
     * Create a progress callback for download logging.
     */
    private function createProgressCallback(string $logPrefix): Closure
    {
        return static function (int $downloadTotal, int $downloadedBytes) use ($logPrefix): void {
            if ($downloadTotal <= 0 || $downloadedBytes <= 0) {
                return;
            }

            if ($downloadedBytes % self::LOG_PROGRESS_BYTES !== 0) {
                return;
            }

            Log::debug("{$logPrefix} download progress", [
                'downloaded_mb' => round($downloadedBytes / self::BYTES_PER_MB, 2),
                'total_mb' => round($downloadTotal / self::BYTES_PER_MB, 2),
                'progress' => round(($downloadedBytes / $downloadTotal) * 100, 2).'%',
            ]);
        };
    }

    /**
     * Delete a file if it exists.
     */
    private function deleteFileIfExists(Filesystem $disk, string $path): void
    {
        if ($disk->exists($path)) {
            $disk->delete($path);
        }
    }

    /**
     * Log successful download information.
     */
    private function logDownloadSuccess(Filesystem $disk, string $path, string $logPrefix): void
    {
        $fileSize = $disk->size($path);

        Log::info("{$logPrefix} downloaded successfully", [
            'size' => $fileSize.' bytes',
            'size_mb' => round($fileSize / self::BYTES_PER_MB, 2).' MB',
        ]);
    }

    /**
     * Extract the VAT ZIP file and find the XML file.
     */
    private function extractVatZipFile(): FinancialDataState
    {
        return $this->extractZip($this->vatZipFileName, 'VAT ZIP');
    }

    /**
     * Extract a ZIP file and find the XML file inside.
     */
    private function extractZip(string $zipFileName, string $logPrefix): FinancialDataState
    {
        $disk = Storage::disk($this->diskName);
        $zipPath = $this->tempDir.'/'.$zipFileName;
        $zipFullPath = $disk->path($zipPath);

        $zip = new ZipArchive;

        if ($zip->open($zipFullPath) !== true) {
            throw new RuntimeException("Failed to open {$logPrefix} file: ".$zipFullPath);
        }

        $filesInZip = $this->getFilesInZip($zip);
        Log::info("Files found in {$logPrefix} archive", ['count' => count($filesInZip), 'files' => $filesInZip]);

        $fileName = $this->findXmlFile($filesInZip);

        if (! $fileName) {
            $zip->close();
            throw new RuntimeException("No XML file found in {$logPrefix} archive. Files: ".implode(', ', $filesInZip));
        }

        Log::info("Found XML data file in {$logPrefix}", ['file' => $fileName]);

        $extractPath = $disk->path($this->tempDir);

        if (! $zip->extractTo($extractPath, $fileName)) {
            $zip->close();
            throw new RuntimeException("Failed to extract file from {$logPrefix}: ".$fileName);
        }

        $zip->close();

        $extractedFileName = basename($fileName);

        Log::info("Extracted {$logPrefix} file successfully", ['file' => $extractedFileName]);

        return new FinancialDataState($extractedFileName);
    }

    /**
     * Get list of files in ZIP archive.
     *
     * @return array<string>
     */
    private function getFilesInZip(ZipArchive $zip): array
    {
        $filesInZip = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $stat = $zip->statIndex($i);
            if ($stat !== false) {
                $filesInZip[] = $stat['name'];
            }
        }

        return $filesInZip;
    }

    /**
     * Find XML file in list of files.
     *
     * @param  array<string>  $files
     */
    private function findXmlFile(array $files): ?string
    {
        foreach ($files as $name) {
            if (str_ends_with($name, '/')) {
                continue;
            }

            $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));

            if ($extension === 'xml') {
                return $name;
            }
        }

        return null;
    }

    /**
     * Process the VAT data from the extracted XML file using XMLReader for streaming.
     *
     * @return Generator<array<string, mixed>>
     */
    private function processVatData(FinancialDataState $state): Generator
    {
        yield from $this->processXmlData(
            state: $state,
            parser: fn (array $item): ?array => $this->parseVatData($item),
            logPrefix: 'VAT'
        );
    }

    /**
     * Generic XML data processor using XMLReader for streaming.
     *
     * @param  Closure(array<string, string>): ?array<string, mixed>  $parser
     * @return Generator<array<string, mixed>>
     */
    private function processXmlData(FinancialDataState $state, Closure $parser, string $logPrefix): Generator
    {
        $disk = Storage::disk($this->diskName);
        $extractedPath = $this->tempDir.'/'.$state->extractedFileName;
        $extractedFullPath = $disk->path($extractedPath);

        if (! $disk->exists($extractedPath)) {
            throw new RuntimeException("Extracted {$logPrefix} file does not exist: ".$extractedPath);
        }

        Log::info("Streaming {$logPrefix} XML file with XMLReader for efficient processing...");

        $reader = $this->openXmlReader($extractedFullPath, $logPrefix);

        $processedCount = 0;
        $skippedCount = 0;

        while ($reader->read()) {
            if ($reader->nodeType !== XMLReader::ELEMENT || $reader->name !== 'ITEM') {
                continue;
            }

            $itemArray = $this->readXmlItemAsArray($reader);

            if ($itemArray === null) {
                continue;
            }

            $parsedData = $parser($itemArray);

            if (! $parsedData) {
                $skippedCount++;

                continue;
            }

            $processedCount++;
            yield $parsedData;

            if ($processedCount % self::MEMORY_CLEANUP_INTERVAL === 0) {
                gc_collect_cycles();
            }
        }

        $reader->close();
        unset($reader);

        Log::info("Processed {$logPrefix} data from XML", [
            'processed' => $processedCount,
            'skipped' => $skippedCount,
            'total' => $processedCount + $skippedCount,
        ]);
    }

    /**
     * Open XMLReader for a file.
     */
    private function openXmlReader(string $fullPath, string $logPrefix): XMLReader
    {
        $reader = new XMLReader;

        if (! $reader->open($fullPath)) {
            throw new RuntimeException("Failed to open {$logPrefix} XML file: ".$fullPath);
        }

        return $reader;
    }

    /**
     * Read current XML ITEM element as array.
     *
     * @return array<string, string>|null
     */
    private function readXmlItemAsArray(XMLReader $reader): ?array
    {
        /** @var string|false $itemXml */
        $itemXml = $reader->readOuterXml();

        if ($itemXml === false || $itemXml === '') {
            return null;
        }

        $item = new SimpleXMLElement($itemXml);

        $itemArray = [];
        foreach ($item->children() as $child) {
            $itemArray[(string) $child->getName()] = (string) $child;
        }

        return $itemArray;
    }

    /**
     * Clean up VAT-related temporary files.
     */
    private function cleanupVatFiles(): void
    {
        $disk = Storage::disk($this->diskName);

        $vatZipPath = $this->tempDir.'/'.$this->vatZipFileName;
        if ($disk->exists($vatZipPath)) {
            $disk->delete($vatZipPath);
        }
    }

    /**
     * Extract and trim value from array, returning null if empty.
     */
    private function extractValue(array $item, string $key): ?string
    {
        $value = trim($item[$key] ?? '');

        return $value !== '' ? $value : null;
    }
}
