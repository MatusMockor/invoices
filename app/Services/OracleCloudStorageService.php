<?php

declare(strict_types=1);

namespace App\Services;

use App\Services\Interfaces\OracleCloudStorageService as OracleCloudStorageServiceContract;
use App\Services\OracleCloud\BucketClient;
use App\Services\OracleCloud\Config\BucketConfig;
use App\Services\OracleCloud\Config\GzipConfig;
use App\Services\OracleCloud\Config\JsonParserConfig;
use App\Services\OracleCloud\GzipDecompressor;
use App\Services\OracleCloud\JsonStreamParser;
use App\Services\OracleCloud\TempFileManager;
use Generator;
use JsonException;
use RuntimeException;

/**
 * Facade service for Oracle Cloud Storage operations.
 *
 * Coordinates the workflow between specialized helper classes:
 * - BucketClient: Handles HTTP operations with Oracle Cloud bucket
 * - TempFileManager: Manages temporary files and cleanup
 * - GzipDecompressor: Decompresses gzip files
 * - JsonStreamParser: Streams and parses large JSON files
 */
final class OracleCloudStorageService implements OracleCloudStorageServiceContract
{
    private readonly TempFileManager $fileManager;

    private readonly BucketClient $bucketClient;

    private readonly GzipDecompressor $decompressor;

    private readonly JsonStreamParser $jsonParser;

    public function __construct()
    {
        $diskName = config('filesystems.default');
        $tempDir = config('oracle_cloud.temp_path');

        $this->fileManager = new TempFileManager($diskName, $tempDir);
        $this->bucketClient = new BucketClient(BucketConfig::fromConfig());
        $this->decompressor = new GzipDecompressor(GzipConfig::fromConfig());
        $this->jsonParser = new JsonStreamParser(JsonParserConfig::fromConfig());
    }

    /**
     * List all files available in the Oracle Cloud Storage bucket.
     *
     * @return array<int, array{key: string, last_modified: string, size: int}>
     */
    public function listFiles(): array
    {
        return $this->bucketClient->listFiles();
    }

    /**
     * Download and stream JSON data from a gzipped file.
     *
     * @return Generator<array<string, mixed>>
     */
    public function downloadAndStreamJson(string $fileKey): Generator
    {
        $this->fileManager->ensureTempDirectoryExists();

        $localPath = $this->downloadFile($fileKey);

        yield from $this->streamJsonFromGzip($localPath);
    }

    /**
     * Get the latest daily incremental file.
     *
     * @return array{key: string, last_modified: string, size: int}|null
     */
    public function getLatestDailyFile(): ?array
    {
        return $this->bucketClient->getLatestDailyFile();
    }

    /**
     * Get all batch-init files for full sync, ordered by filename.
     *
     * @return array<int, array{key: string, last_modified: string, size: int}>
     */
    public function getBatchInitFiles(): array
    {
        return $this->bucketClient->getBatchInitFiles();
    }

    /**
     * Get batch-init file list for a specific date.
     *
     * @param  string  $date  Date in YYYY-MM-DD format
     * @return array<int, string> Array of file keys to download
     */
    public function getBatchInitFileList(string $date): array
    {
        return $this->bucketClient->getBatchInitFileList($date);
    }

    /**
     * Find the latest available batch-init date.
     *
     * @return string|null Latest date in YYYY-MM-DD format, or null if none found
     */
    public function getLatestBatchInitDate(): ?string
    {
        return $this->bucketClient->getLatestBatchInitDate();
    }

    /**
     * Clean up a specific downloaded file immediately.
     *
     * @param  string  $fileKey  The file key that was downloaded
     */
    public function cleanupFile(string $fileKey): void
    {
        $this->fileManager->cleanupFile($fileKey);
    }

    /**
     * Clean up temporary files.
     */
    public function cleanup(): void
    {
        $this->fileManager->cleanup();
    }

    /**
     * Download a file from Oracle Cloud Storage.
     *
     * @throws RuntimeException If download fails or file doesn't exist after download
     */
    private function downloadFile(string $fileKey): string
    {
        $localFileName = basename($fileKey);
        $localPath = $this->fileManager->buildTempPath($localFileName);
        $fullPath = $this->fileManager->getFullPath($localPath);

        $this->bucketClient->downloadFile($fileKey, $fullPath);

        if (! $this->fileManager->exists($localPath)) {
            throw new RuntimeException('Download completed but file does not exist: '.$localPath);
        }

        $this->fileManager->trackFile($localPath);

        return $localPath;
    }

    /**
     * Stream JSON data from a gzipped file.
     *
     * @return Generator<array<string, mixed>>
     *
     * @throws RuntimeException If file processing fails
     */
    private function streamJsonFromGzip(string $localPath): Generator
    {
        if (! $this->fileManager->exists($localPath)) {
            throw new RuntimeException('File does not exist: '.$localPath);
        }

        $gzipPath = $this->fileManager->getFullPath($localPath);
        [, $jsonRelativePath] = $this->fileManager->getLocalFilePaths(basename($localPath));
        $jsonFullPath = $this->fileManager->getFullPath($jsonRelativePath);

        $this->decompressor->decompress($gzipPath, $jsonFullPath);
        $this->fileManager->trackFile($jsonRelativePath);

        try {
            yield from $this->jsonParser->stream($jsonFullPath);
        } catch (JsonException $e) {
            throw new RuntimeException('Failed to parse JSON file: '.$e->getMessage(), 0, $e);
        }
    }
}
