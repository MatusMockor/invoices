<?php

declare(strict_types=1);

namespace App\Services\OracleCloud;

/**
 * Result of a JSON object extraction attempt.
 */
final readonly class ExtractionResult
{
    public function __construct(
        public ?string $json,
        public bool $isEndOfArray,
    ) {}

    /**
     * Create a result indicating end of array was found.
     */
    public static function endOfArray(): self
    {
        return new self(null, true);
    }

    /**
     * Create a result with extracted JSON.
     */
    public static function withJson(string $json): self
    {
        return new self($json, false);
    }

    /**
     * Create a result indicating no complete object found yet.
     */
    public static function incomplete(): self
    {
        return new self(null, false);
    }
}
