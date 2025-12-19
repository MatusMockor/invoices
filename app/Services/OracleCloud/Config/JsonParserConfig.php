<?php

declare(strict_types=1);

namespace App\Services\OracleCloud\Config;

/**
 * Configuration for JSON stream parsing.
 */
final readonly class JsonParserConfig
{
    public function __construct(
        public int $chunkSize,
        public int $gcInterval,
        public int $decodeDepth,
        public int $bufferKeepSize,
    ) {}

    /**
     * Create config from Laravel config values.
     */
    public static function fromConfig(): self
    {
        return new self(
            chunkSize: config('oracle_cloud.processing.json_stream_chunk_size'),
            gcInterval: config('oracle_cloud.processing.gc_interval'),
            decodeDepth: config('oracle_cloud.processing.json_decode_depth'),
            bufferKeepSize: config('oracle_cloud.processing.buffer_keep_size'),
        );
    }
}
