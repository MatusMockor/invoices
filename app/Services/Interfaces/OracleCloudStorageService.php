<?php

declare(strict_types=1);

namespace App\Services\Interfaces;

use Generator;

interface OracleCloudStorageService
{
    /**
     * List all files available in the Oracle Cloud Storage bucket.
     *
     * @return array<int, array{key: string, last_modified: string, size: int}>
     */
    public function listFiles(): array;

    /**
     * Download and stream JSON data from a gzipped file.
     *
     * @return Generator<array<string, mixed>>
     */
    public function downloadAndStreamJson(string $fileKey): Generator;

    /**
     * Get the latest daily incremental file.
     *
     * @return array{key: string, last_modified: string, size: int}|null
     */
    public function getLatestDailyFile(): ?array;

    /**
     * Get all batch-init files for full sync, ordered by filename.
     *
     * @return array<int, array{key: string, last_modified: string, size: int}>
     */
    public function getBatchInitFiles(): array;

    /**
     * Get batch-init file list for a specific date.
     * Downloads and parses init_YYYY-MM-DD_list.txt file.
     *
     * @param  string  $date  Date in YYYY-MM-DD format
     * @return array<int, string> Array of file keys to download
     */
    public function getBatchInitFileList(string $date): array;

    /**
     * Find the latest available batch-init date.
     * Searches for init_YYYY-MM-DD_list.txt files and returns the most recent date.
     *
     * @return string|null Latest date in YYYY-MM-DD format, or null if none found
     */
    public function getLatestBatchInitDate(): ?string;

    /**
     * Clean up a specific downloaded file immediately.
     *
     * @param  string  $fileKey  The file key that was downloaded
     */
    public function cleanupFile(string $fileKey): void;

    /**
     * Clean up temporary files.
     */
    public function cleanup(): void;
}
