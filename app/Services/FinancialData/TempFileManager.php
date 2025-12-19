<?php

declare(strict_types=1);

namespace App\Services\FinancialData;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;

/**
 * Manages temporary files and directories for financial data processing.
 */
final class TempFileManager
{
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
     * Clean up all temporary files and directories.
     */
    public function cleanup(): void
    {
        $disk = $this->getDisk();

        $this->cleanupFilesByExtension($disk, 'zip');
        $this->cleanupFilesByExtension($disk, 'xml');
        $this->cleanupEmptyDirectory($disk);
    }

    /**
     * Clean up all files with given extension in temp directory.
     */
    private function cleanupFilesByExtension(Filesystem $disk, string $extension): void
    {
        if (! $disk->exists($this->tempDir)) {
            return;
        }

        $files = $disk->files($this->tempDir);
        foreach ($files as $file) {
            if (str_ends_with(strtolower($file), '.'.$extension)) {
                $disk->delete($file);
            }
        }
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
