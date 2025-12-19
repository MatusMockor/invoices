<?php

declare(strict_types=1);

namespace App\Services\FinancialData;

use App\DataTransferObjects\FinancialDataState;
use App\Services\FinancialData\Parsers\FinancialDataParser;
use Generator;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use SimpleXMLElement;
use XMLReader;

/**
 * Handles streaming XML processing using XMLReader for memory efficiency.
 */
final class XmlStreamProcessor
{
    private const MEMORY_CLEANUP_INTERVAL = 5000;

    public function __construct(
        private readonly string $diskName,
        private readonly string $tempDir,
    ) {}

    /**
     * Process XML data using streaming and yield parsed results.
     *
     * @return Generator<array<string, mixed>>
     */
    public function process(FinancialDataState $state, FinancialDataParser $parser, string $logPrefix): Generator
    {
        $fullPath = $this->resolveAndValidatePath($state, $logPrefix);

        Log::info("Streaming {$logPrefix} XML file with XMLReader for efficient processing...");

        $reader = $this->openXmlReader($fullPath, $logPrefix);
        $counts = ['processed' => 0, 'skipped' => 0];

        try {
            yield from $this->streamItems($reader, $parser, $counts);
        } finally {
            $reader->close();
            $this->logResults($logPrefix, $counts);
        }
    }

    /**
     * Resolve file path and validate it exists.
     */
    private function resolveAndValidatePath(FinancialDataState $state, string $logPrefix): string
    {
        $disk = $this->getDisk();
        $extractedPath = $this->tempDir . '/' . $state->extractedFileName;

        if (! $disk->exists($extractedPath)) {
            throw new RuntimeException("Extracted {$logPrefix} file does not exist: " . $extractedPath);
        }

        return $disk->path($extractedPath);
    }

    /**
     * Stream and process XML items.
     *
     * @param array<string, int> $counts
     * @return Generator<array<string, mixed>>
     */
    private function streamItems(XMLReader $reader, FinancialDataParser $parser, array &$counts): Generator
    {
        while ($reader->read()) {
            if ($reader->nodeType !== XMLReader::ELEMENT || $reader->name !== 'ITEM') {
                continue;
            }

            $itemArray = $this->readXmlItemAsArray($reader);
            if ($itemArray === null) {
                continue;
            }

            $parsedData = $parser->parse($itemArray);
            if (! $parsedData) {
                $counts['skipped']++;
                continue;
            }

            $counts['processed']++;
            yield $parsedData;

            if ($counts['processed'] % self::MEMORY_CLEANUP_INTERVAL === 0) {
                gc_collect_cycles();
            }
        }
    }

    /**
     * Log processing results.
     *
     * @param array<string, int> $counts
     */
    private function logResults(string $logPrefix, array $counts): void
    {
        Log::info("Processed {$logPrefix} data from XML", [
            'processed' => $counts['processed'],
            'skipped' => $counts['skipped'],
            'total' => $counts['processed'] + $counts['skipped'],
        ]);
    }

    /**
     * Open XMLReader for a file.
     */
    private function openXmlReader(string $fullPath, string $logPrefix): XMLReader
    {
        $reader = new XMLReader();

        if (! $reader->open($fullPath)) {
            throw new RuntimeException("Failed to open {$logPrefix} XML file: " . $fullPath);
        }

        return $reader;
    }

    /**
     * Read current XML ITEM element as array.
     *
     * @return array<string, string>|null
     */
    private function readXmlItemAsArray(XMLReader $reader): ?array
    {
        /** @var string|false $itemXml */
        $itemXml = $reader->readOuterXml();

        if ($itemXml === false || $itemXml === '') {
            return null;
        }

        $item = new SimpleXMLElement($itemXml);

        $itemArray = [];
        foreach ($item->children() as $child) {
            $itemArray[(string) $child->getName()] = (string) $child;
        }

        return $itemArray;
    }

    private function getDisk(): Filesystem
    {
        return Storage::disk($this->diskName);
    }
}
