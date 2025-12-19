<?php

declare(strict_types=1);

namespace App\Services\FinancialData;

use App\DataTransferObjects\FinancialDataState;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use ZipArchive;

/**
 * Handles ZIP file extraction and XML file discovery.
 */
final class ZipExtractor
{
    public function __construct(
        private readonly string $diskName,
        private readonly string $tempDir,
    ) {}

    /**
     * Extract a ZIP file and find the XML file inside.
     */
    public function extract(string $zipFileName, string $logPrefix): FinancialDataState
    {
        $disk = $this->getDisk();
        $zipPath = $this->tempDir.'/'.$zipFileName;
        $zipFullPath = $disk->path($zipPath);

        $zip = new ZipArchive;

        if ($zip->open($zipFullPath) !== true) {
            throw new RuntimeException("Failed to open {$logPrefix} file: ".$zipFullPath);
        }

        $filesInZip = $this->getFilesInZip($zip);
        Log::info("Files found in {$logPrefix} archive", ['count' => count($filesInZip), 'files' => $filesInZip]);

        $fileName = $this->findXmlFile($filesInZip);

        if (! $fileName) {
            $zip->close();
            throw new RuntimeException("No XML file found in {$logPrefix} archive. Files: ".implode(', ', $filesInZip));
        }

        Log::info("Found XML data file in {$logPrefix}", ['file' => $fileName]);

        $extractPath = $disk->path($this->tempDir);

        if (! $zip->extractTo($extractPath, $fileName)) {
            $zip->close();
            throw new RuntimeException("Failed to extract file from {$logPrefix}: ".$fileName);
        }

        $zip->close();

        $extractedFileName = basename($fileName);

        Log::info("Extracted {$logPrefix} file successfully", ['file' => $extractedFileName]);

        return new FinancialDataState($extractedFileName);
    }

    /**
     * Get list of files in ZIP archive.
     *
     * @return array<string>
     */
    private function getFilesInZip(ZipArchive $zip): array
    {
        $filesInZip = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $stat = $zip->statIndex($i);
            if ($stat !== false) {
                $filesInZip[] = $stat['name'];
            }
        }

        return $filesInZip;
    }

    /**
     * Find XML file in list of files.
     *
     * @param  array<string>  $files
     */
    private function findXmlFile(array $files): ?string
    {
        foreach ($files as $name) {
            if (str_ends_with($name, '/')) {
                continue;
            }

            $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));

            if ($extension === 'xml') {
                return $name;
            }
        }

        return null;
    }

    private function getDisk(): Filesystem
    {
        return Storage::disk($this->diskName);
    }
}
