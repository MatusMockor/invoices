<?php

declare(strict_types=1);

namespace App\Services\OracleCloud;

use App\Services\OracleCloud\Config\GzipConfig;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Handles gzip file decompression with chunked processing.
 */
final class GzipDecompressor
{
    public function __construct(
        private readonly GzipConfig $config,
    ) {}

    /**
     * Decompress a gzip file to a JSON file.
     *
     * @throws RuntimeException If decompression fails
     */
    public function decompress(string $gzipPath, string $outputPath): void
    {
        $this->ensureDirectoryExists(dirname($outputPath));

        Log::debug('Decompression paths', [
            'gzipPath' => $gzipPath,
            'outputPath' => $outputPath,
            'dir_exists' => is_dir(dirname($outputPath)),
        ]);

        $gzHandle = gzopen($gzipPath, 'rb');
        if ($gzHandle === false) {
            throw new RuntimeException('Failed to open gzip file: '.$gzipPath);
        }

        $jsonHandle = fopen($outputPath, 'wb');
        if ($jsonHandle === false) {
            gzclose($gzHandle);
            $error = error_get_last();
            throw new RuntimeException('Failed to create decompressed file: '.$outputPath.' Error: '.($error['message'] ?? 'unknown'));
        }

        try {
            $this->processChunks($gzHandle, $jsonHandle);
        } finally {
            gzclose($gzHandle);
            fclose($jsonHandle);
        }
    }

    /**
     * Process gzip file in chunks.
     *
     * @param  resource  $gzHandle
     * @param  resource  $jsonHandle
     */
    private function processChunks($gzHandle, $jsonHandle): void
    {
        while (! gzeof($gzHandle)) {
            $chunk = gzread($gzHandle, $this->config->chunkSize);
            if ($chunk === false) {
                break;
            }
            fwrite($jsonHandle, $chunk);
        }
    }

    /**
     * Ensure directory exists, create if not.
     */
    private function ensureDirectoryExists(string $directory): void
    {
        if (is_dir($directory)) {
            return;
        }

        if (! mkdir($directory, 0755, true) && ! is_dir($directory)) {
            throw new RuntimeException('Failed to create directory: '.$directory);
        }
    }
}
