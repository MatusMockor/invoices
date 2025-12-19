<?php

declare(strict_types=1);

namespace App\Services\FinancialData;

use Closure;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

/**
 * Handles downloading files from remote URLs with progress logging.
 */
final class FileDownloader
{
    private const DOWNLOAD_TIMEOUT_SECONDS = 600;

    private const LOG_PROGRESS_BYTES = 10485760; // 10MB

    private const BYTES_PER_MB = 1048576;

    public function __construct(
        private readonly string $diskName,
        private readonly string $tempDir,
    ) {}

    /**
     * Download a file from a URL to the temp directory.
     */
    public function download(string $url, string $fileName, string $logPrefix): void
    {
        Log::info("Downloading {$logPrefix} from: " . $url);

        $disk = $this->getDisk();
        $filePath = $this->tempDir . '/' . $fileName;
        $fullPath = $disk->path($filePath);

        $response = Http::timeout(self::DOWNLOAD_TIMEOUT_SECONDS)
            ->withOptions([
                'sink' => $fullPath,
                'progress' => $this->createProgressCallback($logPrefix),
            ])
            ->get($url);

        if (! $response->successful()) {
            $this->deleteFileIfExists($disk, $filePath);

            throw new RuntimeException("Failed to download {$logPrefix}: HTTP " . $response->status());
        }

        if (! $disk->exists($filePath)) {
            throw new RuntimeException("{$logPrefix} download completed but file does not exist: " . $filePath);
        }

        $this->logDownloadSuccess($disk, $filePath, $logPrefix);
    }

    /**
     * Create a progress callback for download logging.
     */
    private function createProgressCallback(string $logPrefix): Closure
    {
        $logProgressBytes = self::LOG_PROGRESS_BYTES;
        $bytesPerMb = self::BYTES_PER_MB;

        return static function (int $downloadTotal, int $downloadedBytes) use ($logPrefix, $logProgressBytes, $bytesPerMb): void {
            if ($downloadTotal <= 0 || $downloadedBytes <= 0) {
                return;
            }

            if ($downloadedBytes % $logProgressBytes !== 0) {
                return;
            }

            Log::debug("{$logPrefix} download progress", [
                'downloaded_mb' => round($downloadedBytes / $bytesPerMb, 2),
                'total_mb' => round($downloadTotal / $bytesPerMb, 2),
                'progress' => round(($downloadedBytes / $downloadTotal) * 100, 2) . '%',
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
            'size' => $fileSize . ' bytes',
            'size_mb' => round($fileSize / self::BYTES_PER_MB, 2) . ' MB',
        ]);
    }

    private function getDisk(): Filesystem
    {
        return Storage::disk($this->diskName);
    }
}
