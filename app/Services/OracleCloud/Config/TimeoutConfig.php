<?php

declare(strict_types=1);

namespace App\Services\OracleCloud\Config;

/**
 * Timeout configuration for HTTP operations.
 */
final readonly class TimeoutConfig
{
    public function __construct(
        public int $short,
        public int $default,
        public int $long,
    ) {}

    /**
     * Create config from Laravel config values.
     */
    public static function fromConfig(): self
    {
        return new self(
            short: config('oracle_cloud.timeouts.short'),
            default: config('oracle_cloud.timeouts.default'),
            long: config('oracle_cloud.timeouts.long'),
        );
    }
}
