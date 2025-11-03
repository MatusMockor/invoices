<?php

declare(strict_types=1);

namespace App\Services;

use App\Services\Interfaces\OracleCloudStorageService as OracleCloudStorageServiceContract;
use Exception;
use Generator;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use JsonException;
use RuntimeException;
use SimpleXMLElement;
use Throwable;

final class OracleCloudStorageService implements OracleCloudStorageServiceContract
{
    private const DISK_NAME = 'local';

    private const TEMP_DIR = 'temp/company-sync';

    private const BATCH_DAILY_PREFIX = 'batch-daily/';

    private const BATCH_INIT_PREFIX = 'batch-init/';

    private const BATCH_INIT_LIST_PATTERN = 'batch-init/init_%s_list.txt';

    private const BATCH_INIT_DATE_REGEX = '/init_(\d{4}-\d{2}-\d{2})_list\.txt$/';

    private const GZIP_CHUNK_SIZE = 8192;

    private const JSON_STREAM_CHUNK_SIZE = 4194304;

    private const GC_INTERVAL = 10000;

    private const JSON_DECODE_DEPTH = 512;

    private const HTTP_TIMEOUT = 60;

    private const HTTP_TIMEOUT_LONG = 600;

    private const HTTP_TIMEOUT_SHORT = 30;

    private const BUFFER_KEEP_SIZE = 20;

    private const RESULTS_ARRAY_MARKER = '"results":[';

    private const RESULTS_ARRAY_MARKER_LENGTH = 11;

    private readonly string $diskName;

    private readonly string $tempDir;

    private readonly string $bucketUrl;

    /**
     * @var array<string>
     */
    private array $downloadedFiles = [];

    public function __construct()
    {
        $this->diskName = self::DISK_NAME;
        $this->tempDir = self::TEMP_DIR;
        $this->bucketUrl = config('oracle_cloud.bucket_url', 'https://frkqbrydxwdp.compat.objectstorage.eu-frankfurt-1.oraclecloud.com/susr-rpo/');
    }

    /**
     * List all files available in the Oracle Cloud Storage bucket.
     *
     * @return array<int, array{key: string, last_modified: string, size: int}>
     */
    public function listFiles(): array
    {
        $response = Http::timeout(self::HTTP_TIMEOUT_SHORT)->get($this->bucketUrl);

        if (! $response->successful()) {
            throw new RuntimeException('Failed to fetch bucket listing: HTTP '.$response->status());
        }

        $xmlContent = $response->body();

        try {
            $xml = new SimpleXMLElement($xmlContent);
        } catch (Exception $e) {
            throw new RuntimeException('Failed to parse bucket XML: '.$e->getMessage());
        }

        $files = [];

        foreach ($xml->Contents as $content) {
            $key = (string) $content->Key;
            $lastModified = (string) $content->LastModified;
            $size = (int) $content->Size;

            $files[] = [
                'key' => $key,
                'last_modified' => $lastModified,
                'size' => $size,
            ];
        }

        return $files;
    }

    /**
     * Download and stream JSON data from a gzipped file.
     *
     * @return Generator<array<string, mixed>>
     */
    public function downloadAndStreamJson(string $fileKey): Generator
    {
        $this->ensureTempDirectoryExists();

        $localPath = $this->downloadFile($fileKey);

        try {
            yield from $this->streamJsonFromGzip($localPath);
        } finally {
            // Cleanup will be called later by cleanup() method
        }
    }

    /**
     * Get the latest daily incremental file.
     *
     * @return array{key: string, last_modified: string, size: int}|null
     */
    public function getLatestDailyFile(): ?array
    {
        $files = $this->listFiles();

        $dailyFiles = array_filter($files, static function (array $file): bool {
            return str_starts_with($file['key'], self::BATCH_DAILY_PREFIX);
        });

        if (empty($dailyFiles)) {
            return null;
        }

        // Sort by last_modified descending to get the latest
        usort($dailyFiles, static function (array $a, array $b): int {
            return $b['last_modified'] <=> $a['last_modified'];
        });

        return $dailyFiles[0];
    }

    /**
     * Get all batch-init files for full sync, ordered by filename.
     *
     * @return array<int, array{key: string, last_modified: string, size: int}>
     */
    public function getBatchInitFiles(): array
    {
        $files = $this->listFiles();

        $initFiles = array_filter($files, static function (array $file): bool {
            return str_starts_with($file['key'], self::BATCH_INIT_PREFIX);
        });

        // Sort by key (filename) to ensure correct order (_001, _002, etc.)
        usort($initFiles, static function (array $a, array $b): int {
            return $a['key'] <=> $b['key'];
        });

        return array_values($initFiles);
    }

    /**
     * Get batch-init file list for a specific date.
     * Downloads and parses init_YYYY-MM-DD_list.txt file.
     *
     * @param  string  $date  Date in YYYY-MM-DD format
     * @return array<int, string> Array of file keys to download
     */
    public function getBatchInitFileList(string $date): array
    {
        $listFileKey = sprintf(self::BATCH_INIT_LIST_PATTERN, $date);
        $url = $this->bucketUrl.$listFileKey;

        try {
            $response = Http::timeout(self::HTTP_TIMEOUT)->get($url);

            if (! $response->successful()) {
                throw new RuntimeException("Failed to fetch batch init file list: HTTP {$response->status()}");
            }

            $content = $response->body();
            $lines = explode("\n", $content);

            // Parse filenames and build full file keys
            $fileKeys = [];
            foreach ($lines as $line) {
                $filename = trim($line);
                if ($filename !== '' && str_ends_with($filename, '.json.gz')) {
                    $fileKeys[] = self::BATCH_INIT_PREFIX.$filename;
                }
            }

            return $fileKeys;
        } catch (Throwable $e) {
            throw new RuntimeException("Failed to retrieve batch init file list: {$e->getMessage()}", 0, $e);
        }
    }

    /**
     * Find the latest available batch-init date.
     * Searches for init_YYYY-MM-DD_list.txt files and returns the most recent date.
     *
     * @return string|null Latest date in YYYY-MM-DD format, or null if none found
     */
    public function getLatestBatchInitDate(): ?string
    {
        $files = $this->listFiles();

        // Filter for batch-init list files
        $listFiles = array_filter($files, static function (array $file): bool {
            return str_starts_with($file['key'], self::BATCH_INIT_PREFIX.'init_') && str_ends_with($file['key'], '_list.txt');
        });

        if (empty($listFiles)) {
            return null;
        }

        // Extract dates from filenames
        $dates = [];
        foreach ($listFiles as $file) {
            // Extract date from: batch-init/init_2025-11-01_list.txt
            if (preg_match(self::BATCH_INIT_DATE_REGEX, $file['key'], $matches)) {
                $dates[] = $matches[1];
            }
        }

        if (empty($dates)) {
            return null;
        }

        // Sort dates descending and get the latest
        rsort($dates);

        return $dates[0];
    }

    /**
     * Clean up a specific downloaded file immediately.
     *
     * @param  string  $fileKey  The file key that was downloaded
     */
    public function cleanupFile(string $fileKey): void
    {
        $disk = Storage::disk($this->diskName);

        [$gzPath, $jsonPath] = $this->getLocalFilePaths($fileKey);

        // Delete .gz file
        if ($disk->exists($gzPath)) {
            $disk->delete($gzPath);

            // Remove from tracking array
            $this->downloadedFiles = array_filter(
                $this->downloadedFiles,
                static fn (string $path): bool => $path !== $gzPath
            );
        }

        // Delete decompressed .json file
        if ($disk->exists($jsonPath)) {
            $disk->delete($jsonPath);

            // Remove from tracking array
            $this->downloadedFiles = array_filter(
                $this->downloadedFiles,
                static fn (string $path): bool => $path !== $jsonPath
            );
        }
    }

    /**
     * Clean up temporary files.
     */
    public function cleanup(): void
    {
        $disk = Storage::disk($this->diskName);

        foreach ($this->downloadedFiles as $filePath) {
            if ($disk->exists($filePath)) {
                $disk->delete($filePath);
            }
        }

        $this->downloadedFiles = [];

        // Clean up directory if empty
        if ($disk->exists($this->tempDir)) {
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
     * Download a file from Oracle Cloud Storage.
     *
     * @param  string  $fileKey  The file key to download
     * @return string The local path to the downloaded file
     *
     * @throws RuntimeException If download fails or file doesn't exist after download
     */
    private function downloadFile(string $fileKey): string
    {
        $url = $this->bucketUrl.$fileKey;
        $localFileName = basename($fileKey);
        $localPath = $this->tempDir.'/'.$localFileName;

        $disk = Storage::disk($this->diskName);
        $fullPath = $disk->path($localPath);

        $response = Http::timeout(self::HTTP_TIMEOUT_LONG)
            ->withOptions(['sink' => $fullPath])
            ->get($url);

        if (! $response->successful()) {
            if ($disk->exists($localPath)) {
                $disk->delete($localPath);
            }

            throw new RuntimeException('Failed to download file: HTTP '.$response->status());
        }

        if (! $disk->exists($localPath)) {
            throw new RuntimeException('Download completed but file does not exist: '.$localPath);
        }

        $this->downloadedFiles[] = $localPath;

        return $localPath;
    }

    /**
     * Stream JSON data from a gzipped file.
     * Oracle format: {"exportDate":"...","results":[{...},{...}]}
     *
     * @return Generator<array<string, mixed>>
     */
    private function streamJsonFromGzip(string $localPath): Generator
    {
        $disk = Storage::disk($this->diskName);
        $gzipPath = $disk->path($localPath);

        if (! $disk->exists($localPath)) {
            throw new RuntimeException('File does not exist: '.$localPath);
        }

        // Create path for decompressed JSON file
        [, $jsonPath] = $this->getLocalFilePaths(basename($localPath));
        $jsonFullPath = $disk->path($jsonPath);

        // Decompress .gz file to disk
        $gzHandle = gzopen($gzipPath, 'rb');
        if ($gzHandle === false) {
            throw new RuntimeException('Failed to open gzip file: '.$gzipPath);
        }

        $jsonHandle = fopen($jsonFullPath, 'wb');
        if ($jsonHandle === false) {
            gzclose($gzHandle);
            throw new RuntimeException('Failed to create decompressed file: '.$jsonFullPath);
        }

        try {
            // Decompress in chunks
            while (! gzeof($gzHandle)) {
                $chunk = gzread($gzHandle, self::GZIP_CHUNK_SIZE);
                if ($chunk === false) {
                    break;
                }
                fwrite($jsonHandle, $chunk);
            }

            gzclose($gzHandle);
            fclose($jsonHandle);

            // Track decompressed file for cleanup
            $this->downloadedFiles[] = $jsonPath;

            // Stream and parse JSON from decompressed file
            yield from $this->streamJsonRecords($jsonFullPath);
        } catch (JsonException $e) {
            throw new RuntimeException('Failed to parse JSON file: '.$e->getMessage(), 0, $e);
        }
    }

    /**
     * Stream JSON records from a file without loading the entire file into memory.
     * Expects Oracle format: {"exportDate":"...","results":[{...},{...}]}
     *
     * @return Generator<array<string, mixed>>
     */
    private function streamJsonRecords(string $filePath): Generator
    {
        $handle = fopen($filePath, 'rb');
        if ($handle === false) {
            throw new RuntimeException('Failed to open JSON file for streaming: '.$filePath);
        }

        try {
            $buffer = '';
            $inResultsArray = false;
            $recordCount = 0;
            $chunkSize = self::JSON_STREAM_CHUNK_SIZE; // 4MB chunks for better performance

            // Read file in chunks
            while (! feof($handle)) {
                $chunk = fread($handle, $chunkSize);
                if ($chunk === false) {
                    break;
                }

                $buffer .= $chunk;

                // Look for the start of "results" array if not found yet
                if (! $inResultsArray) {
                    $pos = strpos($buffer, self::RESULTS_ARRAY_MARKER);
                    if ($pos !== false) {
                        $inResultsArray = true;
                        // Move buffer to start of results array content
                        $buffer = substr($buffer, $pos + self::RESULTS_ARRAY_MARKER_LENGTH);
                    } else {
                        // Keep last chars in case marker is split across chunks
                        if (strlen($buffer) > self::BUFFER_KEEP_SIZE) {
                            $buffer = substr($buffer, -self::BUFFER_KEEP_SIZE);
                        }

                        continue;
                    }
                }

                // Extract complete JSON objects using optimized brace matching
                $processed = 0;
                $bufferLen = strlen($buffer);
                $depth = 0;
                $start = -1;
                $inString = false;
                $escapeNext = false;

                // Use byte array for faster access
                for ($i = 0; $i < $bufferLen; $i++) {
                    $char = $buffer[$i];

                    $result = $this->processJsonCharacter(
                        $char,
                        $depth,
                        $inString,
                        $escapeNext,
                        $start,
                        $i,
                        $buffer,
                        $recordCount
                    );

                    if ($result !== null) {
                        if ($result['break']) {
                            break 2;
                        }

                        if (isset($result['record'])) {
                            yield $result['record'];
                        }

                        if (isset($result['processed'])) {
                            $processed = $result['processed'];
                        }
                    }
                }

                // Keep only unprocessed part in buffer
                if ($processed > 0) {
                    $buffer = substr($buffer, $processed);
                }
            }
        } finally {
            fclose($handle);
        }
    }

    /**
     * Get local file paths for a given file key.
     *
     * @param  string  $fileKey  The file key
     * @return array{0: string, 1: string} [$gzPath, $jsonPath]
     */
    private function getLocalFilePaths(string $fileKey): array
    {
        $localFileName = basename($fileKey);
        $gzPath = $this->tempDir.'/'.$localFileName;
        $jsonPath = str_replace('.gz', '', $gzPath);

        return [$gzPath, $jsonPath];
    }

    /**
     * Process a single JSON character during streaming.
     *
     * @param  string  $char  The character to process
     * @param  int  &$depth  Current depth in JSON structure
     * @param  bool  &$inString  Whether we're currently inside a string
     * @param  bool  &$escapeNext  Whether the next character is escaped
     * @param  int  &$start  Start position of current object
     * @param  int  $i  Current position in buffer
     * @param  string  $buffer  The buffer being processed
     * @param  int  &$recordCount  Total records processed
     * @return array<string, mixed>|null Result array with 'break', 'record', or 'processed' keys, or null
     */
    private function processJsonCharacter(
        string $char,
        int &$depth,
        bool &$inString,
        bool &$escapeNext,
        int &$start,
        int $i,
        string $buffer,
        int &$recordCount
    ): ?array {
        // Handle string escaping to properly track braces inside strings
        if ($escapeNext) {
            $escapeNext = false;

            return null;
        }

        if ($char === '\\') {
            $escapeNext = true;

            return null;
        }

        if ($char === '"') {
            $inString = ! $inString;

            return null;
        }

        // Only process structural characters outside of strings
        if ($inString) {
            return null;
        }

        if ($char === '{') {
            if ($depth === 0) {
                $start = $i;
            }
            $depth++;

            return null;
        }

        if ($char === '}') {
            $depth--;

            // Complete object found
            if ($depth === 0 && $start !== -1) {
                $objectJson = substr($buffer, $start, $i - $start + 1);

                try {
                    $record = json_decode($objectJson, true, self::JSON_DECODE_DEPTH, JSON_THROW_ON_ERROR);
                    $recordCount++;

                    // Garbage collection every N records (reduced logging)
                    if ($recordCount % self::GC_INTERVAL === 0) {
                        gc_collect_cycles();
                    }

                    $start = -1;

                    return [
                        'break' => false,
                        'record' => $record,
                        'processed' => $i + 1,
                    ];
                } catch (JsonException $e) {
                    // Skip invalid records silently for better performance
                    $start = -1;

                    return [
                        'break' => false,
                        'processed' => $i + 1,
                    ];
                }
            }

            return null;
        }

        if ($char === ']' && $depth === 0) {
            // End of results array reached
            return ['break' => true];
        }

        return null;
    }
}
