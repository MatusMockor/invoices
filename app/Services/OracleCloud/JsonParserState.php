<?php

declare(strict_types=1);

namespace App\Services\OracleCloud;

/**
 * Mutable state object for JSON streaming parser.
 */
final class JsonParserState
{
    public string $buffer = '';

    public bool $inResultsArray = false;

    public int $recordCount = 0;

    /**
     * Append data to the buffer.
     */
    public function appendToBuffer(string $data): void
    {
        $this->buffer .= $data;
    }
}
