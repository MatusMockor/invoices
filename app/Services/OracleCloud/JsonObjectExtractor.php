<?php

declare(strict_types=1);

namespace App\Services\OracleCloud;

/**
 * Extracts individual JSON objects from a buffer string.
 * Handles proper parsing of nested braces and string escaping.
 */
final class JsonObjectExtractor
{
    private int $position = 0;

    private int $processed = 0;

    private readonly int $bufferLength;

    public function __construct(
        private readonly string $buffer,
    ) {
        $this->bufferLength = strlen($buffer);
    }

    /**
     * Check if there's more buffer to process.
     */
    public function hasMore(): bool
    {
        return $this->position < $this->bufferLength;
    }

    /**
     * Get the remaining unprocessed buffer.
     */
    public function getRemainingBuffer(): string
    {
        return $this->processed > 0
            ? substr($this->buffer, $this->processed)
            : $this->buffer;
    }

    /**
     * Extract the next JSON object from the buffer.
     */
    public function extractNext(): ExtractionResult
    {
        $depth = 0;
        $start = -1;
        $inString = false;
        $escapeNext = false;

        while ($this->position < $this->bufferLength) {
            $char = $this->buffer[$this->position];

            if ($this->handleEscapeSequence($char, $inString, $escapeNext)) {
                $this->position++;

                continue;
            }

            if ($inString) {
                $this->position++;

                continue;
            }

            $result = $this->processCharacter($char, $depth, $start);
            if ($result !== null) {
                return $result;
            }

            $this->position++;
        }

        return ExtractionResult::incomplete();
    }

    /**
     * Handle escape sequences in strings.
     */
    private function handleEscapeSequence(string $char, bool &$inString, bool &$escapeNext): bool
    {
        if ($escapeNext) {
            $escapeNext = false;

            return true;
        }

        if ($char === '\\') {
            $escapeNext = true;

            return true;
        }

        if ($char === '"') {
            $inString = ! $inString;

            return true;
        }

        return false;
    }

    /**
     * Process a structural character (outside of strings).
     */
    private function processCharacter(string $char, int &$depth, int &$start): ?ExtractionResult
    {
        if ($char === '{') {
            if ($depth === 0) {
                $start = $this->position;
            }
            $depth++;

            return null;
        }

        if ($char === '}') {
            $depth--;

            if ($depth === 0 && $start !== -1) {
                return $this->extractObject($start);
            }

            return null;
        }

        if ($char === ']' && $depth === 0) {
            return ExtractionResult::endOfArray();
        }

        return null;
    }

    /**
     * Extract the JSON object from start to current position.
     */
    private function extractObject(int $start): ExtractionResult
    {
        $json = substr($this->buffer, $start, $this->position - $start + 1);
        $this->position++;
        $this->processed = $this->position;

        return ExtractionResult::withJson($json);
    }
}
