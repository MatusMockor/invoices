<?php

declare(strict_types=1);

namespace App\Services;

use App\Services\Interfaces\FinancialDataService as FinancialDataServiceContract;
use Generator;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use ZipArchive;

class FinancialDataService implements FinancialDataServiceContract
{
    private string $diskName = 'local';

    private string $tempDir;

    private string $zipFileName = 'companies.zip';

    private string $vatZipFileName = 'vat.zip';

    private ?string $extractedFileName = null;

    private ?string $vatExtractedFileName = null;

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
            $this->extractZipFile();

            yield from $this->processCompanyData();
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
            'street' => ! empty(trim($item['ULICA_CISLO'] ?? '')) ? trim($item['ULICA_CISLO']) : null,
            'city' => ! empty(trim($item['OBEC'] ?? '')) ? trim($item['OBEC']) : null,
            'postal_code' => ! empty(trim($item['PSC'] ?? '')) ? trim($item['PSC']) : null,
            'country' => ! empty(trim($item['NAZOV_STATU'] ?? '')) ? trim($item['NAZOV_STATU']) : null,
            'dic' => ! empty(trim($item['DIC'] ?? '')) ? trim($item['DIC']) : null,
            'ic_dph' => ! empty(trim($item['IC_DPH'] ?? '')) ? trim($item['IC_DPH']) : null,
            'company_type' => null, // Not available in XML
            'registration_number' => null, // Not available in XML
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
            $this->extractVatZipFile();

            yield from $this->processVatData();
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
            'ic_dph' => ! empty(trim($item['IC_DPH'] ?? '')) ? trim($item['IC_DPH']) : null,
        ];
    }

    /**
     * Clean up temporary files and directories.
     */
    public function cleanup(): void
    {
        $disk = Storage::disk($this->diskName);

        $zipPath = $this->tempDir.'/'.$this->zipFileName;
        if ($disk->exists($zipPath)) {
            $disk->delete($zipPath);
        }

        if ($this->extractedFileName) {
            $extractedPath = $this->tempDir.'/'.$this->extractedFileName;
            if ($disk->exists($extractedPath)) {
                $disk->delete($extractedPath);
            }
        }

        $this->cleanupVatFiles();

        // Clean up directory if empty
        if ($disk->exists($this->tempDir)) {
            $files = $disk->files($this->tempDir);
            if (empty($files)) {
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
    private function extractZipFile(): void
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

        // Store only the filename (not the full path) for later use
        $this->extractedFileName = basename($fileName);

        Log::info('Extracted file successfully', ['file' => $this->extractedFileName]);
    }

    /**
     * Process the company data from the extracted XML file.
     * Note: Uses SimpleXML which loads entire file into memory.
     * For production, install php-xml extension and use XMLReader instead.
     *
     * @return Generator<array<string, mixed>>
     */
    private function processCompanyData(): Generator
    {
        if (! $this->extractedFileName) {
            throw new RuntimeException('No extracted file available');
        }

        $disk = Storage::disk($this->diskName);
        $extractedPath = $this->tempDir.'/'.$this->extractedFileName;
        $extractedFullPath = $disk->path($extractedPath);

        if (! $disk->exists($extractedPath)) {
            throw new RuntimeException('Extracted file does not exist: '.$extractedPath);
        }

        Log::info('Loading XML file (this may take a moment for large files)...');

        // Load XML file - this loads entire file into memory
        // For better memory efficiency, install XMLReader extension
        $xml = simplexml_load_file($extractedFullPath);

        if ($xml === false) {
            throw new RuntimeException('Failed to parse XML file: '.$extractedFullPath);
        }

        $processedCount = 0;
        $skippedCount = 0;

        // Iterate through ITEM elements using foreach with yield for memory efficiency
        foreach ($xml->DS_DSRDP->ITEM as $item) {
            // Convert SimpleXMLElement to array
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

            // Unset to free memory periodically
            if ($processedCount % 5000 === 0) {
                unset($item);
                gc_collect_cycles();
            }
        }

        unset($xml);

        Log::info('Processed company data from XML', [
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
    private function extractVatZipFile(): void
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

        $this->vatExtractedFileName = basename($fileName);

        Log::info('Extracted VAT file successfully', ['file' => $this->vatExtractedFileName]);
    }

    /**
     * Process the VAT data from the extracted XML file.
     *
     * @return Generator<array<string, mixed>>
     */
    private function processVatData(): Generator
    {
        if (! $this->vatExtractedFileName) {
            throw new RuntimeException('No extracted VAT file available');
        }

        $disk = Storage::disk($this->diskName);
        $extractedPath = $this->tempDir.'/'.$this->vatExtractedFileName;
        $extractedFullPath = $disk->path($extractedPath);

        if (! $disk->exists($extractedPath)) {
            throw new RuntimeException('Extracted VAT file does not exist: '.$extractedPath);
        }

        Log::info('Loading VAT XML file (this may take a moment for large files)...');

        $xml = simplexml_load_file($extractedFullPath);

        if ($xml === false) {
            throw new RuntimeException('Failed to parse VAT XML file: '.$extractedFullPath);
        }

        $processedCount = 0;
        $skippedCount = 0;

        foreach ($xml->DS_DPHS->ITEM as $item) {
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

            if ($processedCount % 5000 === 0) {
                unset($item);
                gc_collect_cycles();
            }
        }

        unset($xml);

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

        if ($this->vatExtractedFileName) {
            $vatExtractedPath = $this->tempDir.'/'.$this->vatExtractedFileName;
            if ($disk->exists($vatExtractedPath)) {
                $disk->delete($vatExtractedPath);
            }
        }
    }
}
