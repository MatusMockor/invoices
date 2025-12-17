<?php

declare(strict_types=1);

namespace App\Actions\Company;

use App\Actions\Company\Concerns\BuildsStreetAddress;
use App\Actions\Company\Concerns\DeterminesCompanyType;
use App\Actions\Company\Concerns\ValidatesTemporalEntries;
use App\Enums\CompanySyncStatus;
use App\Enums\CompanySyncType;
use App\Repositories\Contracts\CompanyRepository as CompanyRepositoryContract;
use App\Repositories\Contracts\CompanySyncLogRepository as CompanySyncLogRepositoryContract;
use App\Services\Interfaces\OracleCloudStorageService as OracleCloudStorageServiceContract;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Sync companies from Oracle Cloud Storage.
 */
final class SyncCompaniesFromOracleAction
{
    use BuildsStreetAddress;
    use DeterminesCompanyType;
    use ValidatesTemporalEntries;

    public function __construct(
        private readonly OracleCloudStorageServiceContract $oracleCloudStorageService,
        private readonly CompanyRepositoryContract $companyRepository,
        private readonly CompanySyncLogRepositoryContract $companySyncLogRepository,
    ) {}

    /**
     * Sync companies from Oracle Cloud Storage.
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
            }
        }

        if (! empty($batch)) {
            $this->processBatch($batch, $stats);
        }
    }

    /**
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
