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

/**
 * Sync companies from Oracle Cloud Storage.
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 * @SuppressWarnings(PHPMD.TooManyMethods)
 */
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
     * @return array{created: int, errors: int, files_processed: int}
     */
    public function handle(): array
    {
        $syncData = $this->determineSyncSource();

        if ($syncData === null) {
            return $this->emptyStats();
        }

        $existingSync = $this->companySyncLogRepository->findByDate($syncData['date']);
        if ($existingSync && $existingSync->status === CompanySyncStatus::COMPLETED) {
            return $this->statsFromExistingSync($existingSync);
        }

        $syncLog = $this->createSyncLog($syncData['date'], $syncData['type']);

        return $this->processSync($syncLog, $syncData['files']);
    }

    /**
     * @return array{date: string, type: CompanySyncType, files: array<string>}|null
     */
    private function determineSyncSource(): ?array
    {
        $today = today()->toDateString();

        $fileKeys = $this->oracleCloudStorageService->getBatchInitFileList($today);
        if (! empty($fileKeys)) {
            return ['date' => $today, 'type' => CompanySyncType::BATCHINIT, 'files' => $fileKeys];
        }

        $latestDate = $this->oracleCloudStorageService->getLatestBatchInitDate();
        if ($latestDate) {
            $fileKeys = $this->oracleCloudStorageService->getBatchInitFileList($latestDate);
            if (! empty($fileKeys)) {
                return ['date' => $latestDate, 'type' => CompanySyncType::BATCHINIT, 'files' => $fileKeys];
            }
        }

        $dailyFile = $this->oracleCloudStorageService->getLatestDailyFile();
        if ($dailyFile) {
            return ['date' => $today, 'type' => CompanySyncType::BATCHDAILY, 'files' => [$dailyFile['key']]];
        }

        return null;
    }

    /**
     * @return array{created: int, errors: int, files_processed: int}
     */
    private function emptyStats(): array
    {
        return ['created' => 0, 'errors' => 0, 'files_processed' => 0];
    }

    /**
     * @return array{created: int, errors: int, files_processed: int}
     */
    private function statsFromExistingSync(object $existingSync): array
    {
        return [
            'created' => $existingSync->companies_created,
            'errors' => $existingSync->errors,
            'files_processed' => $existingSync->files_processed,
        ];
    }

    private function createSyncLog(string $syncDate, CompanySyncType $syncType): object
    {
        return $this->companySyncLogRepository->updateOrCreate(
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
    }

    /**
     * @param  array<string>  $fileKeys
     * @return array{created: int, errors: int, files_processed: int}
     */
    private function processSync(object $syncLog, array $fileKeys): array
    {
        $stats = $this->emptyStats();

        try {
            foreach ($fileKeys as $fileKey) {
                $this->processFile($fileKey, $stats);
                $stats['files_processed']++;

                $this->oracleCloudStorageService->cleanupFile($fileKey);
                $this->updateSyncProgress($syncLog, $stats);
            }

            $this->markSyncCompleted($syncLog, $stats);

            return $stats;
        } catch (Throwable $e) {
            $this->markSyncFailed($syncLog);
            throw $e;
        } finally {
            $this->oracleCloudStorageService->cleanup();
        }
    }

    /**
     * @param  array{created: int, errors: int, files_processed: int}  $stats
     */
    private function updateSyncProgress(object $syncLog, array $stats): void
    {
        $this->companySyncLogRepository->update($syncLog, [
            'files_processed' => $stats['files_processed'],
            'companies_created' => $stats['created'],
            'errors' => $stats['errors'],
        ]);
    }

    /**
     * @param  array{created: int, errors: int, files_processed: int}  $stats
     */
    private function markSyncCompleted(object $syncLog, array $stats): void
    {
        $this->companySyncLogRepository->update($syncLog, [
            'status' => CompanySyncStatus::COMPLETED->value,
            'completed_at' => now(),
            'files_processed' => $stats['files_processed'],
            'companies_created' => $stats['created'],
            'errors' => $stats['errors'],
        ]);
    }

    private function markSyncFailed(object $syncLog): void
    {
        $this->companySyncLogRepository->update($syncLog, [
            'status' => CompanySyncStatus::FAILED->value,
            'completed_at' => now(),
        ]);
    }

    /**
     * Process a single file.
     *
     * @param  array{created: int, errors: int, files_processed: int}  $stats
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
     * @param  array{created: int, errors: int, files_processed: int}  $stats
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
        if (! empty($data['termination'])) {
            return null;
        }

        $ico = $this->extractIco($data);
        if ($ico === null) {
            return null;
        }

        $name = $this->extractCompanyName($data);
        $addressData = $this->extractAddressData($data);
        $registrationData = $this->extractRegistrationData($data);
        $type = $this->determineCompanyType($data, $registrationData['raw_number'], $name);

        return [
            'ico' => $ico,
            'name' => $name,
            'street' => $addressData['street'],
            'city' => $addressData['city'],
            'postal_code' => $addressData['postal_code'],
            'country' => $addressData['country'],
            'dic' => null,
            'ic_dph' => null,
            'registration_office' => $registrationData['office'],
            'registration_number' => $registrationData['number'],
            'type' => $type->value,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function extractIco(array $data): ?string
    {
        $identifiers = $data['identifiers'] ?? [];
        $currentIdentifier = $this->findCurrentlyValidEntry($identifiers);

        if ($currentIdentifier === null || ! isset($currentIdentifier['value'])) {
            return null;
        }

        $ico = trim($currentIdentifier['value']);

        return $ico !== '' ? $ico : null;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function extractCompanyName(array $data): string
    {
        $fullNames = $data['fullNames'] ?? [];
        $currentName = $this->findCurrentlyValidEntry($fullNames);

        return $currentName !== null && isset($currentName['value'])
            ? trim($currentName['value'])
            : '';
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{street: string|null, city: string|null, postal_code: string|null, country: string|null}
     */
    private function extractAddressData(array $data): array
    {
        $addresses = $data['addresses'] ?? [];
        $address = $this->findCurrentlyValidEntry($addresses) ?? [];

        $street = $this->buildStreetAddress($address);
        $city = $address['municipality']['value'] ?? null;
        $postalCode = ! empty($address['postalCodes']) ? $address['postalCodes'][0] : null;
        $country = $address['country']['value'] ?? null;

        return [
            'street' => $street ? trim($street) : null,
            'city' => $city ? trim($city) : null,
            'postal_code' => $postalCode ? trim($postalCode) : null,
            'country' => $country ? trim($country) : null,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{office: string|null, number: string|null, raw_number: string|null}
     */
    private function extractRegistrationData(array $data): array
    {
        $registrationOffices = $data['sourceRegister']['registrationOffices'] ?? [];
        $currentRegistrationOffice = $this->findCurrentlyValidEntry($registrationOffices);
        $registrationOffice = $currentRegistrationOffice !== null && isset($currentRegistrationOffice['value'])
            ? trim($currentRegistrationOffice['value'])
            : null;

        $registrationNumbers = $data['sourceRegister']['registrationNumbers'] ?? [];
        $currentRegistrationNumber = $this->findCurrentlyValidEntry($registrationNumbers);
        $rawRegistrationNumber = $currentRegistrationNumber !== null && isset($currentRegistrationNumber['value'])
            ? trim($currentRegistrationNumber['value'])
            : null;

        $registrationNumber = $rawRegistrationNumber !== null
            ? $this->removeTypePrefix($rawRegistrationNumber)
            : null;

        return [
            'office' => $registrationOffice,
            'number' => $registrationNumber,
            'raw_number' => $rawRegistrationNumber,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function determineCompanyType(array $data, ?string $rawRegistrationNumber, string $name): CompanyType
    {
        $registerType = $data['sourceRegister']['value']['value'] ?? null;

        if ($registerType === 'Živnostenský register') {
            return CompanyType::SOLE_PROPRIETOR;
        }

        $type = $this->extractCompanyType($rawRegistrationNumber);

        if ($type !== null) {
            return $type;
        }

        $type = $this->extractCompanyTypeFromName($name);

        if ($type !== null) {
            return $type;
        }

        return CompanyType::OTHER;
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

        // Format 1: street with optional regNumber/buildingNumber
        if ($this->hasStreet($address)) {
            return $this->buildStreetWithNumbers($address);
        }

        // Format 2: district + regNumber (for zivnostnici)
        if ($this->hasDistrict($address)) {
            return $this->buildDistrictWithNumber($address);
        }

        // Format 3: only house number available
        if ($this->hasRegNumber($address)) {
            return (string) $address['regNumber'];
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $address
     */
    private function hasStreet(array $address): bool
    {
        return isset($address['street']) && trim($address['street']) !== '';
    }

    /**
     * @param  array<string, mixed>  $address
     */
    private function hasDistrict(array $address): bool
    {
        return isset($address['district']['value']) && trim($address['district']['value']) !== '';
    }

    /**
     * @param  array<string, mixed>  $address
     */
    private function hasRegNumber(array $address): bool
    {
        return isset($address['regNumber']) && $address['regNumber'] !== 0;
    }

    /**
     * @param  array<string, mixed>  $address
     */
    private function hasBuildingNumber(array $address): bool
    {
        return isset($address['buildingNumber']) && $address['buildingNumber'] !== 0;
    }

    /**
     * @param  array<string, mixed>  $address
     */
    private function buildStreetWithNumbers(array $address): string
    {
        $streetAddress = trim($address['street']);
        $numbers = $this->collectHouseNumbers($address);

        if (empty($numbers)) {
            return $streetAddress;
        }

        return $streetAddress.' '.implode('/', $numbers);
    }

    /**
     * @param  array<string, mixed>  $address
     * @return array<int, string>
     */
    private function collectHouseNumbers(array $address): array
    {
        $numbers = [];

        if ($this->hasRegNumber($address)) {
            $numbers[] = (string) $address['regNumber'];
        }

        if ($this->hasBuildingNumber($address)) {
            $numbers[] = (string) $address['buildingNumber'];
        }

        return $numbers;
    }

    /**
     * @param  array<string, mixed>  $address
     */
    private function buildDistrictWithNumber(array $address): string
    {
        $streetAddress = trim($address['district']['value']);

        if ($this->hasRegNumber($address)) {
            return $streetAddress.' '.$address['regNumber'];
        }

        return $streetAddress;
    }

    /**
     * Find the currently valid entry from an array of temporal entries.
     * Returns entry that is currently valid based on validTo date:
     * - If validTo is null - valid (no expiration)
     * - If validTo >= today - still valid
     * - If validTo < today - expired (skip)
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

        $validEntries = $this->filterValidEntries($entries);

        if (empty($validEntries)) {
            return null;
        }

        if (count($validEntries) === 1) {
            return reset($validEntries);
        }

        return $this->findLatestValidEntry($validEntries);
    }

    /**
     * Filter entries to find currently valid ones.
     *
     * @param  array<int, array<string, mixed>>  $entries
     * @return array<int, array<string, mixed>>
     */
    private function filterValidEntries(array $entries): array
    {
        $today = today()->toDateString();

        return array_filter($entries, static function (array $entry) use ($today): bool {
            $validTo = $entry['validTo'] ?? null;

            // Entry is valid if validTo is null OR validTo >= today
            return $validTo === null || $validTo >= $today;
        });
    }

    /**
     * Find the entry with the latest validFrom date.
     *
     * @param  array<int, array<string, mixed>>  $validEntries
     * @return array<string, mixed>|null
     */
    private function findLatestValidEntry(array $validEntries): ?array
    {
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
     * Only removes recognized prefixes (Sa/, Sro/, Dr/, Po/, etc.) - only actual number is stored.
     * If the prefix is not a recognized company type prefix, the registration number is returned unchanged.
     *
     * @param  string  $registrationNumber  The registration number (e.g., 'Sa/6266/B', 'Sro/81134/B', '2112/B')
     * @return string The registration number without prefix (e.g., '6266/B', '81134/B', '2112/B')
     */
    private function removeTypePrefix(string $registrationNumber): string
    {
        // Find the position of the first slash
        $slashPosition = strpos($registrationNumber, '/');

        if ($slashPosition === false) {
            return $registrationNumber;
        }

        // Extract prefix (including the slash)
        $prefix = substr($registrationNumber, 0, $slashPosition + 1);

        // Only remove if it's a recognized company type prefix
        if (CompanyType::fromPrefix($prefix) === null) {
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
     * Extract company type from company name.
     * Case-insensitive matching of company type indicators in the name.
     * This is a fallback when the type cannot be determined from registration number prefix.
     *
     * @param  string|null  $name  The company name to analyze
     * @return CompanyType|null The matching CompanyType or null if not found
     */
    private function extractCompanyTypeFromName(?string $name): ?CompanyType
    {
        if ($name === null || $name === '') {
            return null;
        }

        $lowerName = mb_strtolower($name, 'UTF-8');

        foreach ($this->getCompanyTypePatterns() as $pattern => $type) {
            if (str_contains($lowerName, $pattern)) {
                return $type;
            }
        }

        return null;
    }

    /**
     * Get company type patterns mapping.
     * Order matters: more specific patterns should come first.
     *
     * @return array<string, CompanyType>
     */
    private function getCompanyTypePatterns(): array
    {
        return [
            // General Partnership (v.o.s.)
            'v.o.s.' => CompanyType::GENERAL_PARTNERSHIP,
            'verejná obchodná spoločnosť' => CompanyType::GENERAL_PARTNERSHIP,
            // Limited Partnership (k.s.)
            'k.s.' => CompanyType::LIMITED_PARTNERSHIP,
            'komanditná spoločnosť' => CompanyType::LIMITED_PARTNERSHIP,
            // Limited Liability Company (s.r.o.) - check full forms first
            'spoločnosť s ručením obmedzeným' => CompanyType::LIMITED_LIABILITY_COMPANY,
            'spol. s r.o.' => CompanyType::LIMITED_LIABILITY_COMPANY,
            's.r.o.' => CompanyType::LIMITED_LIABILITY_COMPANY,
            // Joint Stock Company (a.s.)
            'akciová spoločnosť' => CompanyType::JOINT_STOCK_COMPANY,
            'a.s.' => CompanyType::JOINT_STOCK_COMPANY,
            // Cooperative
            'družstvo' => CompanyType::COOPERATIVE,
            // Foundation
            'nadácia' => CompanyType::FOUNDATION,
            // Civic Association
            'občianske združenie' => CompanyType::CIVIC_ASSOCIATION,
            'o.z.' => CompanyType::CIVIC_ASSOCIATION,
        ];
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
