<?php

declare(strict_types=1);

namespace App\Services\OracleCloud\Config;

/**
 * Configuration for Oracle Cloud Storage bucket client.
 */
final readonly class BucketConfig
{
    public function __construct(
        public string $bucketUrl,
        public TimeoutConfig $timeouts,
        public PrefixConfig $prefixes,
    ) {}

    /**
     * Create config from Laravel config values.
     */
    public static function fromConfig(): self
    {
        return new self(
            bucketUrl: config('oracle_cloud.bucket_url'),
            timeouts: TimeoutConfig::fromConfig(),
            prefixes: PrefixConfig::fromConfig(),
        );
    }
}
