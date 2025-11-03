<?php

declare(strict_types=1);

namespace App\Actions\Company;

use App\Enums\CompanySyncStatus;
use App\Enums\CompanySyncType;
use App\Repositories\Interfaces\CompanyRepository as CompanyRepositoryContract;
use App\Repositories\Interfaces\CompanySyncLogRepository as CompanySyncLogRepositoryContract;
use App\Services\Interfaces\OracleCloudStorageService as OracleCloudStorageServiceContract;
use Illuminate\Support\Facades\DB;
use Throwable;

final class SyncCompaniesFromOracleAction
{
    public function __construct(
        private readonly OracleCloudStorageServiceContract $oracleCloudStorageService,
        private readonly CompanyRepositoryContract $companyRepository,
        private readonly CompanySyncLogRepositoryContract $companySyncLogRepository,
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

        // Try to find batch-init for today first
        $fileKeys = $this->oracleCloudStorageService->getBatchInitFileList($today);

        // Default to today's date
        $syncDate = $today;

        // If no files for today, find the latest available date
        if (empty($fileKeys)) {
            $latestDate = $this->oracleCloudStorageService->getLatestBatchInitDate();

            if (! $latestDate) {
                return [
                    'created' => 0,
                    'errors' => 0,
                    'files_processed' => 0,
                ];
            }

            $syncDate = $latestDate;
        }

        // Check if sync already exists for this date
        $existingSync = $this->companySyncLogRepository->findByDate($syncDate);

        if ($existingSync && $existingSync->status === CompanySyncStatus::Completed) {

            return [
                'created' => $existingSync->companies_created,
                'errors' => $existingSync->errors,
                'files_processed' => $existingSync->files_processed,
            ];
        }

        // Create or update sync log
        $syncLog = $this->companySyncLogRepository->updateOrCreate(
            ['sync_date' => $syncDate],
            [
                'sync_type' => CompanySyncType::BatchInit->value,
                'status' => CompanySyncStatus::Processing->value,
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
                $this->companySyncLogRepository->update($syncLog, [
                    'status' => CompanySyncStatus::Completed->value,
                    'completed_at' => now(),
                ]);

                return $stats;
            }

            // Process each file from the list
            foreach ($fileKeys as $fileKey) {
                $this->processFile($fileKey, $stats);
                $stats['files_processed']++;

                // Clean up the file immediately to free memory
                $this->oracleCloudStorageService->cleanupFile($fileKey);

                // Update progress in database
                $this->companySyncLogRepository->update($syncLog, [
                    'files_processed' => $stats['files_processed'],
                    'companies_created' => $stats['created'],
                    'errors' => $stats['errors'],
                ]);
            }

            // Mark sync as completed
            $this->companySyncLogRepository->update($syncLog, [
                'status' => CompanySyncStatus::Completed->value,
                'completed_at' => now(),
                'files_processed' => $stats['files_processed'],
                'companies_created' => $stats['created'],
                'errors' => $stats['errors'],
            ]);

            return $stats;
        } catch (Throwable $e) {
            // Mark sync as failed
            $this->companySyncLogRepository->update($syncLog, [
                'status' => CompanySyncStatus::Failed->value,
                'completed_at' => now(),
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
        $batchSize = config('oracle_cloud.batch_size', 10000);
        $batch = [];

        foreach ($this->oracleCloudStorageService->downloadAndStreamJson($fileKey) as $companyData) {
            $parsedData = $this->parseCompanyData($companyData);

            if (! $parsedData) {
                continue;
            }

            $batch[] = $parsedData;

            if (count($batch) >= $batchSize) {
                $this->processBatch($batch, $stats);
                $batch = [];
                // Removed progress logging for better performance
            }
        }

        if (! empty($batch)) {
            $this->processBatch($batch, $stats);
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

            logger()->error('Failed to process batch in company sync', [
                'exception' => $e->getMessage(),
                'batch_size' => count($batch),
                'trace' => $e->getTraceAsString(),
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
        // Skip terminated companies (those that no longer exist)
        if (! empty($data['termination'])) {
            return null;
        }

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

        $streetAddress = null;

        // Format 1: street with optional regNumber/buildingNumber
        if (isset($address['street']) && trim($address['street']) !== '') {
            $streetAddress = trim($address['street']);

            // Add house numbers in format: regNumber/buildingNumber
            $numbers = [];
            if (isset($address['regNumber']) && $address['regNumber'] !== 0) {
                $numbers[] = (string) $address['regNumber'];
            }
            if (isset($address['buildingNumber']) && $address['buildingNumber'] !== 0) {
                $numbers[] = (string) $address['buildingNumber'];
            }

            if (! empty($numbers)) {
                $streetAddress .= ' '.implode('/', $numbers);
            }
        }
        // Format 2: district + regNumber (for živnostníci)
        elseif (isset($address['district']['value']) && trim($address['district']['value']) !== '') {
            $streetAddress = trim($address['district']['value']);
            if (isset($address['regNumber']) && $address['regNumber'] !== 0) {
                $streetAddress .= ' '.$address['regNumber'];
            }
        }
        // Format 3: only house number available
        elseif (isset($address['regNumber']) && $address['regNumber'] !== 0) {
            $streetAddress = (string) $address['regNumber'];
        }

        return $streetAddress;
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
