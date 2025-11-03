<?php

declare(strict_types=1);

namespace App\Services;

use App\DataTransferObjects\FinancialDataState;
use App\Services\Interfaces\FinancialDataService as FinancialDataServiceContract;
use Generator;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use SimpleXMLElement;
use XMLReader;
use ZipArchive;

class FinancialDataService implements FinancialDataServiceContract
{
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
        $url = config('financial_data.source_url');

        Log::info('Downloading financial data from: '.$url);

        $disk = Storage::disk($this->diskName);
        $zipPath = $this->tempDir.'/'.$this->zipFileName;

        // Get the full path for sink option
        $fullPath = $disk->path($zipPath);

        // Use sink option to stream directly to file - more reliable for large files
        $response = Http::timeout(600) // Increase timeout to 10 minutes for 50MB download
            ->withOptions([
                'sink' => $fullPath,
                'progress' => static function (int $downloadTotal, int $downloadedBytes): void {
                    if ($downloadTotal > 0 && $downloadedBytes > 0 && $downloadedBytes % 10485760 === 0) {
                        // Log every 10MB
                        Log::debug('Download progress', [
                            'downloaded_mb' => round($downloadedBytes / 1048576, 2),
                            'total_mb' => round($downloadTotal / 1048576, 2),
                            'progress' => round(($downloadedBytes / $downloadTotal) * 100, 2).'%',
                        ]);
                    }
                },
            ])
            ->get($url);

        if (! $response->successful()) {
            // Clean up partial download
            if ($disk->exists($zipPath)) {
                $disk->delete($zipPath);
            }

            throw new RuntimeException('Failed to download financial data: HTTP '.$response->status());
        }

        if (! $disk->exists($zipPath)) {
            throw new RuntimeException('Download completed but file does not exist: '.$zipPath);
        }

        $fileSize = $disk->size($zipPath);

        Log::info('Financial data downloaded successfully', [
            'size' => $fileSize.' bytes',
            'size_mb' => round($fileSize / 1048576, 2).' MB',
        ]);
    }

    /**
     * Extract the ZIP file and find the XML file.
     */
    private function extractZipFile(): FinancialDataState
    {
        $disk = Storage::disk($this->diskName);
        $zipPath = $this->tempDir.'/'.$this->zipFileName;
        $zipFullPath = $disk->path($zipPath);

        $zip = new ZipArchive;

        if ($zip->open($zipFullPath) !== true) {
            throw new RuntimeException('Failed to open ZIP file: '.$zipFullPath);
        }

        // Log all files in ZIP for debugging
        $filesInZip = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $stat = $zip->statIndex($i);
            if ($stat !== false) {
                $filesInZip[] = $stat['name'];
            }
        }
        Log::info('Files found in ZIP archive', ['count' => count($filesInZip), 'files' => $filesInZip]);

        // Find XML file (case-insensitive, handle directories)
        $fileName = null;
        foreach ($filesInZip as $name) {
            // Skip directories
            if (str_ends_with($name, '/')) {
                continue;
            }

            $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));

            if ($extension === 'xml') {
                $fileName = $name;
                Log::info('Found XML data file in ZIP', ['file' => $fileName]);
                break;
            }
        }

        if (! $fileName) {
            $zip->close();
            throw new RuntimeException('No XML file found in ZIP archive. Files: '.implode(', ', $filesInZip));
        }

        // Extract to temp directory
        $extractPath = $disk->path($this->tempDir);

        if (! $zip->extractTo($extractPath, $fileName)) {
            $zip->close();
            throw new RuntimeException('Failed to extract file from ZIP: '.$fileName);
        }

        $zip->close();

        $extractedFileName = basename($fileName);

        Log::info('Extracted file successfully', ['file' => $extractedFileName]);

        return new FinancialDataState($extractedFileName);
    }

    /**
     * Process the company data from the extracted XML file using XMLReader for streaming.
     *
     * @return Generator<array<string, mixed>>
     */
    private function processCompanyData(FinancialDataState $state): Generator
    {
        $disk = Storage::disk($this->diskName);
        $extractedPath = $this->tempDir.'/'.$state->extractedFileName;
        $extractedFullPath = $disk->path($extractedPath);

        if (! $disk->exists($extractedPath)) {
            throw new RuntimeException('Extracted file does not exist: '.$extractedPath);
        }

        Log::info('Streaming XML file with XMLReader for efficient processing...');

        $reader = new XMLReader;

        if (! $reader->open($extractedFullPath)) {
            throw new RuntimeException('Failed to open XML file: '.$extractedFullPath);
        }

        $processedCount = 0;
        $skippedCount = 0;

        // Stream through XML and process ITEM elements one by one
        while ($reader->read()) {
            if ($reader->nodeType !== XMLReader::ELEMENT || $reader->name !== 'ITEM') {
                continue;
            }

            // Read ITEM element as SimpleXMLElement for easier parsing
            $itemXml = $reader->readOuterXml();

            if ($itemXml === false) {
                continue;
            }

            $item = new SimpleXMLElement($itemXml);

            // Convert to array
            $itemArray = [];
            foreach ($item->children() as $child) {
                $itemArray[(string) $child->getName()] = (string) $child;
            }

            $companyData = $this->parseCompanyData($itemArray);

            if (! $companyData) {
                $skippedCount++;

                continue;
            }

            $processedCount++;
            yield $companyData;

            // Memory cleanup every 5000 records
            if ($processedCount % 5000 === 0) {
                gc_collect_cycles();
            }
        }

        $reader->close();
        unset($reader);

        Log::info('Processed company data from XML', [
            'processed' => $processedCount,
            'skipped' => $skippedCount,
            'total' => $processedCount + $skippedCount,
        ]);
    }

    /**
     * Process the DIC data from the extracted XML file using XMLReader for streaming.
     *
     * @return Generator<array<string, mixed>>
     */
    private function processDicData(FinancialDataState $state): Generator
    {
        $disk = Storage::disk($this->diskName);
        $extractedPath = $this->tempDir.'/'.$state->extractedFileName;
        $extractedFullPath = $disk->path($extractedPath);

        if (! $disk->exists($extractedPath)) {
            throw new RuntimeException('Extracted file does not exist: '.$extractedPath);
        }

        Log::info('Streaming XML file for DIC data with XMLReader...');

        $reader = new XMLReader;

        if (! $reader->open($extractedFullPath)) {
            throw new RuntimeException('Failed to open XML file: '.$extractedFullPath);
        }

        $processedCount = 0;
        $skippedCount = 0;

        // Stream through XML and process ITEM elements one by one
        while ($reader->read()) {
            if ($reader->nodeType !== XMLReader::ELEMENT || $reader->name !== 'ITEM') {
                continue;
            }

            // Read ITEM element as SimpleXMLElement for easier parsing
            $itemXml = $reader->readOuterXml();

            if ($itemXml === false) {
                continue;
            }

            $item = new SimpleXMLElement($itemXml);

            // Convert to array
            $itemArray = [];
            foreach ($item->children() as $child) {
                $itemArray[(string) $child->getName()] = (string) $child;
            }

            $dicData = $this->parseDicData($itemArray);

            if (! $dicData) {
                $skippedCount++;

                continue;
            }

            $processedCount++;
            yield $dicData;

            // Memory cleanup every 5000 records
            if ($processedCount % 5000 === 0) {
                gc_collect_cycles();
            }
        }

        $reader->close();
        unset($reader);

        Log::info('Processed DIC data from XML', [
            'processed' => $processedCount,
            'skipped' => $skippedCount,
            'total' => $processedCount + $skippedCount,
        ]);
    }

    /**
     * Download the VAT ZIP file from the configured URL.
     */
    private function downloadVatZipFile(): void
    {
        $url = config('financial_data.vat_source_url');

        Log::info('Downloading VAT data from: '.$url);

        $disk = Storage::disk($this->diskName);
        $zipPath = $this->tempDir.'/'.$this->vatZipFileName;

        $fullPath = $disk->path($zipPath);

        $response = Http::timeout(600)
            ->withOptions([
                'sink' => $fullPath,
                'progress' => static function (int $downloadTotal, int $downloadedBytes): void {
                    if ($downloadTotal > 0 && $downloadedBytes > 0 && $downloadedBytes % 10485760 === 0) {
                        Log::debug('VAT download progress', [
                            'downloaded_mb' => round($downloadedBytes / 1048576, 2),
                            'total_mb' => round($downloadTotal / 1048576, 2),
                            'progress' => round(($downloadedBytes / $downloadTotal) * 100, 2).'%',
                        ]);
                    }
                },
            ])
            ->get($url);

        if (! $response->successful()) {
            if ($disk->exists($zipPath)) {
                $disk->delete($zipPath);
            }

            throw new RuntimeException('Failed to download VAT data: HTTP '.$response->status());
        }

        if (! $disk->exists($zipPath)) {
            throw new RuntimeException('VAT download completed but file does not exist: '.$zipPath);
        }

        $fileSize = $disk->size($zipPath);

        Log::info('VAT data downloaded successfully', [
            'size' => $fileSize.' bytes',
            'size_mb' => round($fileSize / 1048576, 2).' MB',
        ]);
    }

    /**
     * Extract the VAT ZIP file and find the XML file.
     */
    private function extractVatZipFile(): FinancialDataState
    {
        $disk = Storage::disk($this->diskName);
        $zipPath = $this->tempDir.'/'.$this->vatZipFileName;
        $zipFullPath = $disk->path($zipPath);

        $zip = new ZipArchive;

        if ($zip->open($zipFullPath) !== true) {
            throw new RuntimeException('Failed to open VAT ZIP file: '.$zipFullPath);
        }

        $filesInZip = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $stat = $zip->statIndex($i);
            if ($stat !== false) {
                $filesInZip[] = $stat['name'];
            }
        }
        Log::info('Files found in VAT ZIP archive', ['count' => count($filesInZip), 'files' => $filesInZip]);

        $fileName = null;
        foreach ($filesInZip as $name) {
            if (str_ends_with($name, '/')) {
                continue;
            }

            $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));

            if ($extension === 'xml') {
                $fileName = $name;
                Log::info('Found XML VAT data file in ZIP', ['file' => $fileName]);
                break;
            }
        }

        if (! $fileName) {
            $zip->close();
            throw new RuntimeException('No XML file found in VAT ZIP archive. Files: '.implode(', ', $filesInZip));
        }

        $extractPath = $disk->path($this->tempDir);

        if (! $zip->extractTo($extractPath, $fileName)) {
            $zip->close();
            throw new RuntimeException('Failed to extract file from VAT ZIP: '.$fileName);
        }

        $zip->close();

        $extractedFileName = basename($fileName);

        Log::info('Extracted VAT file successfully', ['file' => $extractedFileName]);

        return new FinancialDataState($extractedFileName);
    }

    /**
     * Process the VAT data from the extracted XML file using XMLReader for streaming.
     *
     * @return Generator<array<string, mixed>>
     */
    private function processVatData(FinancialDataState $state): Generator
    {
        $disk = Storage::disk($this->diskName);
        $extractedPath = $this->tempDir.'/'.$state->extractedFileName;
        $extractedFullPath = $disk->path($extractedPath);

        if (! $disk->exists($extractedPath)) {
            throw new RuntimeException('Extracted VAT file does not exist: '.$extractedPath);
        }

        Log::info('Streaming VAT XML file with XMLReader for efficient processing...');

        $reader = new XMLReader;

        if (! $reader->open($extractedFullPath)) {
            throw new RuntimeException('Failed to open VAT XML file: '.$extractedFullPath);
        }

        $processedCount = 0;
        $skippedCount = 0;

        // Stream through XML and process ITEM elements one by one
        while ($reader->read()) {
            if ($reader->nodeType !== XMLReader::ELEMENT || $reader->name !== 'ITEM') {
                continue;
            }

            // Read ITEM element as SimpleXMLElement for easier parsing
            $itemXml = $reader->readOuterXml();

            if ($itemXml === false) {
                continue;
            }

            $item = new SimpleXMLElement($itemXml);

            // Convert to array
            $itemArray = [];
            foreach ($item->children() as $child) {
                $itemArray[(string) $child->getName()] = (string) $child;
            }

            $vatData = $this->parseVatData($itemArray);

            if (! $vatData) {
                $skippedCount++;

                continue;
            }

            $processedCount++;
            yield $vatData;

            // Memory cleanup every 5000 records
            if ($processedCount % 5000 === 0) {
                gc_collect_cycles();
            }
        }

        $reader->close();
        unset($reader);

        Log::info('Processed VAT data from XML', [
            'processed' => $processedCount,
            'skipped' => $skippedCount,
            'total' => $processedCount + $skippedCount,
        ]);
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
