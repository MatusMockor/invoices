<?php

declare(strict_types=1);

namespace App\Services\OracleCloud;

use App\Services\OracleCloud\Config\JsonParserConfig;
use Generator;
use JsonException;
use RuntimeException;

/**
 * Streams and parses JSON records from large files without loading entire file into memory.
 * Expects Oracle format: {"exportDate":"...","results":[{...},{...}]}
 */
final class JsonStreamParser
{
    private const string RESULTS_ARRAY_MARKER = '"results":[';

    private const int RESULTS_ARRAY_MARKER_LENGTH = 11;

    public function __construct(
        private readonly JsonParserConfig $config,
    ) {}

    /**
     * Stream JSON records from a file.
     *
     * @return Generator<array<string, mixed>>
     *
     * @throws RuntimeException If file cannot be opened
     * @throws JsonException If JSON parsing fails
     */
    public function stream(string $filePath): Generator
    {
        $handle = fopen($filePath, 'rb');
        if ($handle === false) {
            throw new RuntimeException('Failed to open JSON file for streaming: '.$filePath);
        }

        try {
            yield from $this->processStream($handle);
        } finally {
            fclose($handle);
        }
    }

    /**
     * Process the file stream and extract JSON records.
     *
     * @param  resource  $handle
     * @return Generator<array<string, mixed>>
     */
    private function processStream($handle): Generator
    {
        $state = new JsonParserState;

        while (! feof($handle)) {
            $chunk = fread($handle, $this->config->chunkSize);
            if ($chunk === false) {
                break;
            }

            $state->appendToBuffer($chunk);

            if (! $state->inResultsArray) {
                $this->findResultsArrayStart($state);
                if (! $state->inResultsArray) {
                    continue;
                }
            }

            $result = $this->extractJsonObjects($state);

            foreach ($result['records'] as $record) {
                yield $record;
            }

            if ($result['finished']) {
                break;
            }
        }
    }

    /**
     * Find the start of the results array in the buffer.
     */
    private function findResultsArrayStart(JsonParserState $state): void
    {
        $pos = strpos($state->buffer, self::RESULTS_ARRAY_MARKER);

        if ($pos === false) {
            if (strlen($state->buffer) > $this->config->bufferKeepSize) {
                $state->buffer = substr($state->buffer, -$this->config->bufferKeepSize);
            }

            return;
        }

        $state->inResultsArray = true;
        $state->buffer = substr($state->buffer, $pos + self::RESULTS_ARRAY_MARKER_LENGTH);
    }

    /**
     * Extract JSON objects from the buffer.
     *
     * @return array{records: array<array<string, mixed>>, finished: bool}
     */
    private function extractJsonObjects(JsonParserState $state): array
    {
        $extractor = new JsonObjectExtractor($state->buffer);
        $records = [];

        while ($extractor->hasMore()) {
            $result = $extractor->extractNext();

            if ($result->isEndOfArray) {
                $state->buffer = $extractor->getRemainingBuffer();

                return ['records' => $records, 'finished' => true];
            }

            if ($result->json === null) {
                continue;
            }

            $record = $this->parseJsonObject($result->json, $state);
            if ($record !== null) {
                $records[] = $record;
            }
        }

        $state->buffer = $extractor->getRemainingBuffer();

        return ['records' => $records, 'finished' => false];
    }

    /**
     * Parse a JSON object string.
     *
     * @return array<string, mixed>|null
     */
    private function parseJsonObject(string $objectJson, JsonParserState $state): ?array
    {
        try {
            $record = json_decode($objectJson, true, $this->config->decodeDepth, JSON_THROW_ON_ERROR);
            $state->recordCount++;

            if ($state->recordCount % $this->config->gcInterval === 0) {
                gc_collect_cycles();
            }

            return $record;
        } catch (JsonException) {
            return null;
        }
    }
}
