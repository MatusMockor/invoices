<?php

declare(strict_types=1);

namespace App\Actions\Company;

use App\Models\CompanySyncLog;
use App\Repositories\Interfaces\CompanyRepository as CompanyRepositoryContract;
use App\Services\Interfaces\OracleCloudStorageService as OracleCloudStorageServiceContract;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

final class SyncCompaniesFromOracleAction
{
    public function __construct(
        private readonly OracleCloudStorageServiceContract $oracleCloudStorageService,
        private readonly CompanyRepositoryContract $companyRepository,
    ) {}

    /**
     * Sync companies from Oracle Cloud Storage batch-init files.
     * Automatically finds the latest available batch-init date.
     *
     * @return array{created: int, errors: int, files_processed: int}
     */
    public function handle(): array
    {
        $today = today()->toDateString();

        Log::info('Starting company sync from Oracle Cloud Storage');

        // Try to find batch-init for today first
        $fileKeys = $this->oracleCloudStorageService->getBatchInitFileList($today);

        // If no files for today, find the latest available date
        if (empty($fileKeys)) {
            Log::info('No batch-init found for today, searching for latest available date', ['today' => $today]);

            $latestDate = $this->oracleCloudStorageService->getLatestBatchInitDate();

            if (! $latestDate) {
                Log::warning('No batch-init dates found at all');

                return [
                    'created' => 0,
                    'errors' => 0,
                    'files_processed' => 0,
                ];
            }

            $syncDate = $latestDate;
            Log::info('Found latest batch-init date', ['date' => $syncDate]);
        } else {
            $syncDate = $today;
            Log::info('Found batch-init for today', ['date' => $syncDate]);
        }

        // Check if sync already exists for this date
        $existingSync = CompanySyncLog::where('sync_date', $syncDate)->first();

        if ($existingSync && $existingSync->status === 'completed') {
            Log::info('Sync already completed for this date', ['date' => $syncDate]);

            return [
                'created' => $existingSync->companies_created,
                'errors' => $existingSync->errors,
                'files_processed' => $existingSync->files_processed,
            ];
        }

        // Create or update sync log
        $syncLog = CompanySyncLog::updateOrCreate(
            ['sync_date' => $syncDate],
            [
                'sync_type' => 'batch-init',
                'status' => 'processing',
                'started_at' => now(),
                'files_processed' => 0,
                'companies_created' => 0,
                'errors' => 0,
            ]
        );

        $stats = [
            'created' => 0,
            'errors' => 0,
            'files_processed' => 0,
        ];

        try {
            // Get file list if not already fetched
            if (! isset($fileKeys) || empty($fileKeys)) {
                $fileKeys = $this->oracleCloudStorageService->getBatchInitFileList($syncDate);
            }

            if (empty($fileKeys)) {
                Log::warning('No batch-init files found', ['date' => $syncDate]);

                $syncLog->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                ]);

                return $stats;
            }

            Log::info('Processing batch-init files', [
                'date' => $syncDate,
                'files_count' => count($fileKeys),
            ]);

            // Process each file from the list
            foreach ($fileKeys as $fileKey) {
                $this->processFile($fileKey, $stats);
                $stats['files_processed']++;

                // Clean up the file immediately to free memory
                $this->oracleCloudStorageService->cleanupFile($fileKey);

                // Update progress in database
                $syncLog->update([
                    'files_processed' => $stats['files_processed'],
                    'companies_created' => $stats['created'],
                    'errors' => $stats['errors'],
                ]);
            }

            // Mark sync as completed
            $syncLog->update([
                'status' => 'completed',
                'completed_at' => now(),
                'files_processed' => $stats['files_processed'],
                'companies_created' => $stats['created'],
                'errors' => $stats['errors'],
            ]);

            Log::info('Company sync completed', [
                'date' => $syncDate,
                'files_processed' => $stats['files_processed'],
                'created' => $stats['created'],
                'errors' => $stats['errors'],
            ]);

            return $stats;
        } catch (Throwable $e) {
            // Mark sync as failed
            $syncLog->update([
                'status' => 'failed',
                'completed_at' => now(),
            ]);

            Log::error('Company sync failed', [
                'date' => $syncDate,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        } finally {
            $this->oracleCloudStorageService->cleanup();
        }
    }

    /**
     * Process a single file.
     *
     * @param  array{created: int, errors: int}  $stats
     */
    private function processFile(string $fileKey, array &$stats): void
    {
        Log::info('Processing file', ['file' => $fileKey]);

        $batchSize = config('oracle_cloud.batch_size', 5000);
        $batch = [];
        $totalProcessed = 0;

        try {
            foreach ($this->oracleCloudStorageService->downloadAndStreamJson($fileKey) as $companyData) {
                $parsedData = $this->parseCompanyData($companyData);

                if (! $parsedData) {
                    continue;
                }

                $batch[] = $parsedData;

                if (count($batch) >= $batchSize) {
                    $this->processBatch($batch, $stats);
                    $totalProcessed += count($batch);
                    $batch = [];

                    Log::info('File progress', [
                        'file' => $fileKey,
                        'processed' => $totalProcessed,
                    ]);
                }
            }

            if (! empty($batch)) {
                $this->processBatch($batch, $stats);
                $totalProcessed += count($batch);
            }

            Log::info('File processing completed', [
                'file' => $fileKey,
                'total_processed' => $totalProcessed,
            ]);
        } catch (Throwable $e) {
            Log::error('File processing failed', [
                'file' => $fileKey,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Process a batch of companies.
     *
     * @param  array<int, array<string, mixed>>  $batch
     * @param  array{created: int, errors: int}  $stats
     */
    private function processBatch(array $batch, array &$stats): void
    {
        $batch = $this->filterDuplicateIcos($batch);

        if (empty($batch)) {
            return;
        }

        try {
            $companyRepository = $this->companyRepository;

            DB::transaction(static function () use ($batch, &$stats, $companyRepository): void {
                $affectedRows = $companyRepository->upsertBatch($batch);
                $stats['created'] += $affectedRows;
            });
        } catch (Throwable $e) {
            $stats['errors'] += count($batch);

            Log::error('Batch processing failed', [
                'batch_size' => count($batch),
                'error' => $e->getMessage(),
                'first_3_icos' => array_slice(array_column($batch, 'ico'), 0, 3),
            ]);
        }
    }

    /**
     * Parse company data from Oracle JSON to database format.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>|null
     */
    private function parseCompanyData(array $data): ?array
    {
        // Extract ICO from identifiers array
        $identifiers = $data['identifiers'] ?? [];
        if (empty($identifiers) || ! isset($identifiers[0]['value'])) {
            return null;
        }

        $ico = trim($identifiers[0]['value']);
        if ($ico === '') {
            return null;
        }

        // Extract company name from fullNames array
        $fullNames = $data['fullNames'] ?? [];
        $name = ! empty($fullNames) && isset($fullNames[0]['value'])
            ? trim($fullNames[0]['value'])
            : '';

        // Extract address from addresses array
        $addresses = $data['addresses'] ?? [];
        $address = ! empty($addresses) ? $addresses[0] : [];

        $street = $this->buildStreetAddress($address);
        $city = $address['municipality']['value'] ?? null;
        $postalCode = ! empty($address['postalCodes']) ? $address['postalCodes'][0] : null;
        $country = $address['country']['value'] ?? null;

        return [
            'ico' => $ico,
            'name' => $name,
            'street' => $street ? trim($street) : null,
            'city' => $city ? trim($city) : null,
            'postal_code' => $postalCode ? trim($postalCode) : null,
            'country' => $country ? trim($country) : null,
            'dic' => null, // DIC is not in Oracle data, will be filled from Phase 2
            'ic_dph' => null, // IC DPH is not in Oracle data, will be filled from Phase 2
        ];
    }

    /**
     * Build street address from Oracle address components.
     * Matches the logic from the scraper.
     *
     * @param  array<string, mixed>  $address
     */
    private function buildStreetAddress(array $address): ?string
    {
        if (empty($address)) {
            return null;
        }

        $ulica = null;

        // Format 1: street with optional regNumber/buildingNumber
        if (isset($address['street']) && trim($address['street']) !== '') {
            $ulica = trim($address['street']);

            // Add house numbers in format: regNumber/buildingNumber
            $numbers = [];
            if (isset($address['regNumber']) && $address['regNumber'] !== 0) {
                $numbers[] = (string) $address['regNumber'];
            }
            if (isset($address['buildingNumber']) && $address['buildingNumber'] !== 0) {
                $numbers[] = (string) $address['buildingNumber'];
            }

            if (! empty($numbers)) {
                $ulica .= ' '.implode('/', $numbers);
            }
        }
        // Format 2: district + regNumber (for živnostníci)
        elseif (isset($address['district']['value']) && trim($address['district']['value']) !== '') {
            $ulica = trim($address['district']['value']);
            if (isset($address['regNumber']) && $address['regNumber'] !== 0) {
                $ulica .= ' '.$address['regNumber'];
            }
        }
        // Format 3: only house number available
        elseif (isset($address['regNumber']) && $address['regNumber'] !== 0) {
            $ulica = (string) $address['regNumber'];
        }

        return $ulica;
    }

    /**
     * Filter duplicate ICOs from batch, keeping only the last occurrence.
     *
     * @param  array<int, array<string, mixed>>  $batch
     * @return array<int, array<string, mixed>>
     */
    private function filterDuplicateIcos(array $batch): array
    {
        $seen = [];

        foreach ($batch as $company) {
            $ico = $company['ico'] ?? null;

            if (! $ico) {
                continue;
            }

            $seen[$ico] = $company;
        }

        return array_values($seen);
    }
}
