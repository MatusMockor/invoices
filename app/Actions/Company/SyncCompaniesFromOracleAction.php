<?php

declare(strict_types=1);

namespace App\Actions\Company;

use App\Enums\CompanySyncStatus;
use App\Enums\CompanySyncType;
use App\Enums\CompanyType;
use App\Repositories\Contracts\CompanyRepository as CompanyRepositoryContract;
use App\Repositories\Contracts\CompanySyncLogRepository as CompanySyncLogRepositoryContract;
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
     * Sync companies from Oracle Cloud Storage.
     * Supports both batch-init (full import) and batch-daily (incremental updates).
     *
     * Priority logic:
     * 1. Check for batch-init for today
     * 2. If not found, check for latest available batch-init date
     * 3. If still not found, check for batch-daily for today
     * 4. If no files at all, return empty stats
     *
     * @return array{created: int, errors: int, files_processed: int}
     */
    public function handle(): array
    {
        $today = today()->toDateString();

        // Step 1: Try to find batch-init for today first (highest priority)
        $fileKeys = $this->oracleCloudStorageService->getBatchInitFileList($today);
        $syncDate = $today;
        $syncType = CompanySyncType::BATCHINIT;

        // Step 2: If no batch-init for today, find the latest available batch-init date
        if (empty($fileKeys)) {
            $latestDate = $this->oracleCloudStorageService->getLatestBatchInitDate();

            if ($latestDate) {
                $syncDate = $latestDate;
                $fileKeys = $this->oracleCloudStorageService->getBatchInitFileList($syncDate);
            }
        }

        // Step 3: If still no files, try batch-daily for today (fallback)
        if (empty($fileKeys)) {
            $dailyFile = $this->oracleCloudStorageService->getLatestDailyFile();

            if ($dailyFile) {
                $fileKeys = [$dailyFile['key']];
                $syncDate = $today;
                $syncType = CompanySyncType::BATCHDAILY;
            }
        }

        // Step 4: If no files at all, return empty stats
        if (empty($fileKeys)) {
            return [
                'created' => 0,
                'errors' => 0,
                'files_processed' => 0,
            ];
        }

        // Check if sync already exists for this date
        $existingSync = $this->companySyncLogRepository->findByDate($syncDate);

        if ($existingSync && $existingSync->status === CompanySyncStatus::COMPLETED) {

            return [
                'created' => $existingSync->companies_created,
                'errors' => $existingSync->errors,
                'files_processed' => $existingSync->files_processed,
            ];
        }

        // Create or update sync log with determined sync type
        $syncLog = $this->companySyncLogRepository->updateOrCreate(
            ['sync_date' => $syncDate],
            [
                'sync_type' => $syncType->value,
                'status' => CompanySyncStatus::PROCESSING->value,
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
            // File keys are already determined above
            if (empty($fileKeys)) {
                $this->companySyncLogRepository->update($syncLog, [
                    'status' => CompanySyncStatus::COMPLETED->value,
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
                'status' => CompanySyncStatus::COMPLETED->value,
                'completed_at' => now(),
                'files_processed' => $stats['files_processed'],
                'companies_created' => $stats['created'],
                'errors' => $stats['errors'],
            ]);

            return $stats;
        } catch (Throwable $e) {
            // Mark sync as failed
            $this->companySyncLogRepository->update($syncLog, [
                'status' => CompanySyncStatus::FAILED->value,
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

        // Extract ICO from identifiers array (use currently valid entry)
        $identifiers = $data['identifiers'] ?? [];
        $currentIdentifier = $this->findCurrentlyValidEntry($identifiers);

        if ($currentIdentifier === null || ! isset($currentIdentifier['value'])) {
            return null;
        }

        $ico = trim($currentIdentifier['value']);
        if ($ico === '') {
            return null;
        }

        // Extract company name from fullNames array (use currently valid entry)
        $fullNames = $data['fullNames'] ?? [];
        $currentName = $this->findCurrentlyValidEntry($fullNames);
        $name = $currentName !== null && isset($currentName['value'])
            ? trim($currentName['value'])
            : '';

        // Extract address from addresses array (use currently valid entry)
        $addresses = $data['addresses'] ?? [];
        $address = $this->findCurrentlyValidEntry($addresses) ?? [];

        $street = $this->buildStreetAddress($address);
        $city = $address['municipality']['value'] ?? null;
        $postalCode = ! empty($address['postalCodes']) ? $address['postalCodes'][0] : null;
        $country = $address['country']['value'] ?? null;

        // Extract registration office from sourceRegister (use currently valid entry)
        $registrationOffices = $data['sourceRegister']['registrationOffices'] ?? [];
        $currentRegistrationOffice = $this->findCurrentlyValidEntry($registrationOffices);
        $registrationOffice = $currentRegistrationOffice !== null && isset($currentRegistrationOffice['value'])
            ? trim($currentRegistrationOffice['value'])
            : null;

        // Extract registration number from sourceRegister (use currently valid entry)
        $registrationNumbers = $data['sourceRegister']['registrationNumbers'] ?? [];
        $currentRegistrationNumber = $this->findCurrentlyValidEntry($registrationNumbers);
        $rawRegistrationNumber = $currentRegistrationNumber !== null && isset($currentRegistrationNumber['value'])
            ? trim($currentRegistrationNumber['value'])
            : null;

        // Extract register type to determine if this is a sole proprietor
        $registerType = $data['sourceRegister']['value']['value'] ?? null;

        // Determine company type:
        // 1. If sourceRegister.value.value is "Živnostenský register", it's a sole proprietor
        // 2. Otherwise, extract type from registration number prefix (Sa/, Sro/, etc.)
        if ($registerType === 'Živnostenský register') {
            $type = CompanyType::SOLE_PROPRIETOR;
        } else {
            // Extract company type from registration number prefix BEFORE filtering.
            // Important: This must happen before removeTypePrefix() which removes all prefixes.
            // We need the original prefix to determine the company type correctly.
            $type = $this->extractCompanyType($rawRegistrationNumber);
        }

        // Filter registration number: Remove type prefix (Sa/, Sro/, Dr/, Po/, etc.).
        // All prefixes are removed - only the actual registration number is stored.
        $registrationNumber = $rawRegistrationNumber !== null
            ? $this->removeTypePrefix($rawRegistrationNumber)
            : null;

        return [
            'ico' => $ico,
            'name' => $name,
            'street' => $street ? trim($street) : null,
            'city' => $city ? trim($city) : null,
            'postal_code' => $postalCode ? trim($postalCode) : null,
            'country' => $country ? trim($country) : null,
            'dic' => null, // DIC is not in Oracle data, will be filled from Phase 2
            'ic_dph' => null, // IC DPH is not in Oracle data, will be filled from Phase 2
            'registration_office' => $registrationOffice,
            'registration_number' => $registrationNumber,
            'type' => $type?->value,
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
     * Find the currently valid entry from an array of temporal entries.
     * Returns entry that is currently valid based on validTo date:
     * - If validTo is null → valid (no expiration)
     * - If validTo >= today → still valid
     * - If validTo < today → expired (skip)
     * If multiple valid entries exist, returns the one with latest validFrom.
     *
     * @param  array<int, array<string, mixed>>  $entries
     * @return array<string, mixed>|null
     */
    private function findCurrentlyValidEntry(array $entries): ?array
    {
        if (empty($entries)) {
            return null;
        }

        $today = today()->toDateString();
        $validEntries = [];

        // Filter entries to find currently valid ones
        foreach ($entries as $entry) {
            $validTo = $entry['validTo'] ?? null;

            // Entry is valid if validTo is null OR validTo >= today
            if ($validTo === null || $validTo >= $today) {
                $validEntries[] = $entry;
            }
        }

        // If no valid entries found, return null
        if (empty($validEntries)) {
            return null;
        }

        // If only one valid entry, return it
        if (count($validEntries) === 1) {
            return $validEntries[0];
        }

        // Multiple valid entries: return the one with latest validFrom
        $latestEntry = null;
        $latestDate = null;

        foreach ($validEntries as $entry) {
            $validFrom = $entry['validFrom'] ?? null;

            if (! $validFrom) {
                continue;
            }

            if ($latestDate === null || $validFrom > $latestDate) {
                $latestDate = $validFrom;
                $latestEntry = $entry;
            }
        }

        return $latestEntry;
    }

    /**
     * Remove type prefix from registration number if present.
     * Removes all prefixes (Sa/, Sro/, Dr/, Po/, etc.) - only actual number is stored.
     *
     * @param  string  $registrationNumber  The registration number (e.g., 'Sa/6266/B', 'Sro/81134/B')
     * @return string The registration number without prefix (e.g., '6266/B', '81134/B')
     */
    private function removeTypePrefix(string $registrationNumber): string
    {
        // Find the position of the first slash
        $slashPosition = strpos($registrationNumber, '/');

        if ($slashPosition === false) {
            return $registrationNumber;
        }

        // Return everything after the first slash
        return substr($registrationNumber, $slashPosition + 1);
    }

    /**
     * Extract company type from registration number prefix.
     * Returns the CompanyType enum based on the prefix before the first slash.
     *
     * @param  string|null  $registrationNumber  The registration number (e.g., "Sa/6266/B", "Sro/81134/B")
     * @return CompanyType|null The matching CompanyType or null if not found
     */
    private function extractCompanyType(?string $registrationNumber): ?CompanyType
    {
        if (! $registrationNumber) {
            return null;
        }

        // Find the position of the first slash
        $slashPosition = strpos($registrationNumber, '/');

        if ($slashPosition === false) {
            return null;
        }

        // Extract prefix (including the slash)
        $prefix = substr($registrationNumber, 0, $slashPosition + 1);

        return CompanyType::fromPrefix($prefix);
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
