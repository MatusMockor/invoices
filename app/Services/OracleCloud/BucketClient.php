<?php

declare(strict_types=1);

namespace App\Services\OracleCloud;

use App\Services\OracleCloud\Config\BucketConfig;
use Exception;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use SimpleXMLElement;
use Throwable;

/**
 * HTTP client for Oracle Cloud Storage bucket operations.
 */
final class BucketClient
{
    public function __construct(
        private readonly BucketConfig $config,
    ) {}

    /**
     * List all files available in the Oracle Cloud Storage bucket.
     *
     * @return array<int, array{key: string, last_modified: string, size: int}>
     */
    public function listFiles(): array
    {
        $response = Http::timeout($this->config->timeouts->short)->get($this->config->bucketUrl);

        if (! $response->successful()) {
            throw new RuntimeException('Failed to fetch bucket listing: HTTP '.$response->status());
        }

        return $this->parseXmlListing($response->body());
    }

    /**
     * Download a file from the bucket to a local path.
     *
     * @throws RuntimeException If download fails
     */
    public function downloadFile(string $fileKey, string $destinationPath): void
    {
        $url = $this->config->bucketUrl.$fileKey;

        $response = Http::timeout($this->config->timeouts->long)
            ->withOptions(['sink' => $destinationPath])
            ->get($url);

        if (! $response->successful()) {
            throw new RuntimeException('Failed to download file: HTTP '.$response->status());
        }
    }

    /**
     * Get the latest daily incremental file.
     *
     * @return array{key: string, last_modified: string, size: int}|null
     */
    public function getLatestDailyFile(): ?array
    {
        $files = $this->listFiles();
        $prefix = $this->config->prefixes->daily;

        $dailyFiles = array_filter($files, static function (array $file) use ($prefix): bool {
            return str_starts_with($file['key'], $prefix);
        });

        if (empty($dailyFiles)) {
            return null;
        }

        usort($dailyFiles, static function (array $firstFile, array $secondFile): int {
            return $secondFile['last_modified'] <=> $firstFile['last_modified'];
        });

        return $dailyFiles[0];
    }

    /**
     * Get all batch-init files for full sync, ordered by filename.
     *
     * @return array<int, array{key: string, last_modified: string, size: int}>
     */
    public function getBatchInitFiles(): array
    {
        $files = $this->listFiles();
        $prefix = $this->config->prefixes->init;

        $initFiles = array_filter($files, static function (array $file) use ($prefix): bool {
            return str_starts_with($file['key'], $prefix);
        });

        usort($initFiles, static function (array $firstFile, array $secondFile): int {
            return $firstFile['key'] <=> $secondFile['key'];
        });

        return $initFiles;
    }

    /**
     * Get batch-init file list for a specific date.
     *
     * @param  string  $date  Date in YYYY-MM-DD format
     * @return array<int, string> Array of file keys to download
     */
    public function getBatchInitFileList(string $date): array
    {
        $listFileKey = sprintf($this->config->prefixes->initListPattern, $date);
        $url = $this->config->bucketUrl.$listFileKey;

        try {
            $response = Http::timeout($this->config->timeouts->default)->get($url);

            if ($response->status() === 404) {
                return [];
            }

            if (! $response->successful()) {
                throw new RuntimeException("Failed to fetch batch init file list: HTTP {$response->status()}");
            }

            return $this->parseInitFileList($response->body());
        } catch (RuntimeException $e) {
            throw $e;
        } catch (Throwable $e) {
            throw new RuntimeException("Failed to retrieve batch init file list: {$e->getMessage()}", 0, $e);
        }
    }

    /**
     * Find the latest available batch-init date.
     *
     * @return string|null Latest date in YYYY-MM-DD format, or null if none found
     */
    public function getLatestBatchInitDate(): ?string
    {
        $files = $this->listFiles();
        $prefix = $this->config->prefixes->init.'init_';
        $regex = $this->config->prefixes->initDateRegex;

        $listFiles = array_filter($files, static function (array $file) use ($prefix): bool {
            return str_starts_with($file['key'], $prefix) && str_ends_with($file['key'], '_list.txt');
        });

        if (empty($listFiles)) {
            return null;
        }

        $dates = [];
        foreach ($listFiles as $file) {
            if (preg_match($regex, $file['key'], $matches)) {
                $dates[] = $matches[1];
            }
        }

        if (empty($dates)) {
            return null;
        }

        rsort($dates);

        return $dates[0];
    }

    /**
     * Parse XML bucket listing response.
     *
     * @return array<int, array{key: string, last_modified: string, size: int}>
     */
    private function parseXmlListing(string $xmlContent): array
    {
        try {
            $xml = new SimpleXMLElement($xmlContent);
        } catch (Exception $e) {
            throw new RuntimeException('Failed to parse bucket XML: '.$e->getMessage());
        }

        $files = [];

        foreach ($xml->Contents as $content) {
            $files[] = [
                'key' => (string) $content->Key,
                'last_modified' => (string) $content->LastModified,
                'size' => (int) $content->Size,
            ];
        }

        return $files;
    }

    /**
     * Parse init file list content.
     *
     * @return array<int, string>
     */
    private function parseInitFileList(string $content): array
    {
        $lines = explode("\n", $content);
        $fileKeys = [];
        $initPrefix = $this->config->prefixes->init;

        foreach ($lines as $line) {
            $filename = trim($line);
            if ($filename !== '' && str_ends_with($filename, '.json.gz')) {
                $fileKeys[] = $initPrefix.$filename;
            }
        }

        return $fileKeys;
    }
}
