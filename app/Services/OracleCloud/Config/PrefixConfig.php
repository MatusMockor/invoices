<?php

declare(strict_types=1);

namespace App\Services\OracleCloud\Config;

/**
 * File prefix and pattern configuration.
 */
final readonly class PrefixConfig
{
    public function __construct(
        public string $daily,
        public string $init,
        public string $initListPattern,
        public string $initDateRegex,
    ) {}

    /**
     * Create config from Laravel config values.
     */
    public static function fromConfig(): self
    {
        return new self(
            daily: config('oracle_cloud.prefixes.daily'),
            init: config('oracle_cloud.prefixes.init'),
            initListPattern: config('oracle_cloud.init_list_pattern'),
            initDateRegex: config('oracle_cloud.init_date_regex'),
        );
    }
}
