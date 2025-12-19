<?php

declare(strict_types=1);

namespace App\Services\OracleCloud;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;

/**
 * Manages temporary files and directories for Oracle Cloud data processing.
 */
final class TempFileManager
{
    /**
     * @var array<string>
     */
    private array $trackedFiles = [];

    public function __construct(
        private readonly string $diskName,
        private readonly string $tempDir,
    ) {}

    /**
     * Ensure the temporary directory exists.
     */
    public function ensureTempDirectoryExists(): void
    {
        $disk = $this->getDisk();

        if (! $disk->exists($this->tempDir)) {
            $disk->makeDirectory($this->tempDir);
        }
    }

    /**
     * Track a file for later cleanup.
     */
    public function trackFile(string $path): void
    {
        $this->trackedFiles[] = $path;
    }

    /**
     * Get the full filesystem path for a relative path.
     */
    public function getFullPath(string $relativePath): string
    {
        return $this->getDisk()->path($relativePath);
    }

    /**
     * Build a temp file path from a filename.
     */
    public function buildTempPath(string $filename): string
    {
        return $this->tempDir.'/'.$filename;
    }

    /**
     * Check if a file exists.
     */
    public function exists(string $path): bool
    {
        return $this->getDisk()->exists($path);
    }

    /**
     * Delete a file if it exists.
     */
    public function delete(string $path): void
    {
        $disk = $this->getDisk();

        if ($disk->exists($path)) {
            $disk->delete($path);
        }
    }

    /**
     * Get local file paths for a given file key.
     *
     * @param  string  $fileKey  The file key
     * @return array{0: string, 1: string} [$gzPath, $jsonPath]
     */
    public function getLocalFilePaths(string $fileKey): array
    {
        $localFileName = basename($fileKey);
        $gzPath = $this->buildTempPath($localFileName);
        $jsonPath = str_replace('.gz', '', $gzPath);

        return [$gzPath, $jsonPath];
    }

    /**
     * Clean up a specific downloaded file (both .gz and .json versions).
     */
    public function cleanupFile(string $fileKey): void
    {
        [$gzPath, $jsonPath] = $this->getLocalFilePaths($fileKey);

        $this->delete($gzPath);
        $this->delete($jsonPath);

        $this->trackedFiles = array_values(array_filter(
            $this->trackedFiles,
            static fn (string $path): bool => $path !== $gzPath && $path !== $jsonPath
        ));
    }

    /**
     * Clean up all tracked files and optionally the temp directory.
     */
    public function cleanup(): void
    {
        $disk = $this->getDisk();

        foreach ($this->trackedFiles as $filePath) {
            if ($disk->exists($filePath)) {
                $disk->delete($filePath);
            }
        }

        $this->trackedFiles = [];

        $this->cleanupEmptyDirectory($disk);
    }

    /**
     * Clean up directory if empty.
     */
    private function cleanupEmptyDirectory(Filesystem $disk): void
    {
        if (! $disk->exists($this->tempDir)) {
            return;
        }

        $remainingFiles = $disk->files($this->tempDir);

        if (empty($remainingFiles)) {
            $disk->deleteDirectory($this->tempDir);
        }
    }

    private function getDisk(): Filesystem
    {
        return Storage::disk($this->diskName);
    }
}
