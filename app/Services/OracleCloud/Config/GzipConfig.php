<?php

declare(strict_types=1);

namespace App\Services\OracleCloud\Config;

/**
 * Configuration for gzip decompression.
 */
final readonly class GzipConfig
{
    public function __construct(
        public int $chunkSize,
    ) {}

    /**
     * Create config from Laravel config values.
     */
    public static function fromConfig(): self
    {
        return new self(
            chunkSize: config('oracle_cloud.processing.gzip_chunk_size'),
        );
    }
}
