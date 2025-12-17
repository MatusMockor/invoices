<?php

declare(strict_types=1);

namespace App\Actions\Company;

use App\Enums\CompanySyncStatus;
use App\Enums\CompanySyncType;
use App\Enums\VatPayerStatus;
use App\Repositories\Contracts\CompanyRepository as CompanyRepositoryContract;
use App\Repositories\Contracts\CompanySyncLogRepository as CompanySyncLogRepositoryContract;
use App\Services\Interfaces\FinancialDataService as FinancialDataServiceContract;
use Illuminate\Support\Facades\Log;
use Throwable;

final class SyncCompaniesVatAction
{
    public function __construct(
        private readonly FinancialDataServiceContract $financialDataService,
        private readonly CompanyRepositoryContract $companyRepository,
        private readonly CompanySyncLogRepositoryContract $syncLogRepository,
    ) {}

    /**
     * Sync VAT data for companies from financial data source.
     *
     * @return array{updated: int, not_found: int, errors: int}
     *
     * @throws Throwable
     */
    public function handle(): array
    {
        Log::info('Starting VAT data sync from financial data source');

        $today = today()->toDateString();
        $existingSync = $this->syncLogRepository->findByDateAndType($today, CompanySyncType::VATUPDATE->value);

        if ($this->isSyncAlreadyCompleted($existingSync)) {
            return $this->statsFromExistingSync($existingSync);
        }

        $syncLog = $this->createSyncLog($today);

        return $this->processSync($syncLog);
    }

    private function isSyncAlreadyCompleted(?object $existingSync): bool
    {
        if ($existingSync?->status !== CompanySyncStatus::COMPLETED) {
            return false;
        }

        Log::info('VAT sync already completed for today', ['date' => today()->toDateString()]);

        return true;
    }

    /**
     * @return array{updated: int, not_found: int, errors: int}
     */
    private function statsFromExistingSync(object $existingSync): array
    {
        return [
            'updated' => $existingSync->companies_updated ?? 0,
            'not_found' => $existingSync->companies_not_found ?? 0,
            'errors' => $existingSync->errors,
        ];
    }

    private function createSyncLog(string $today): object
    {
        return $this->syncLogRepository->updateOrCreate(
            ['sync_date' => $today, 'sync_type' => CompanySyncType::VATUPDATE->value],
            [
                'status' => CompanySyncStatus::PROCESSING->value,
                'started_at' => now(),
                'companies_updated' => 0,
                'companies_not_found' => 0,
                'errors' => 0,
            ]
        );
    }

    /**
     * @return array{updated: int, not_found: int, errors: int}
     */
    private function processSync(object $syncLog): array
    {
        $stats = ['updated' => 0, 'not_found' => 0, 'errors' => 0];
        $batchSize = config('financial_data.batch_size', 1000);
        $batch = [];
        $totalProcessed = 0;

        try {
            foreach ($this->financialDataService->downloadAndExtractVatData() as $vatData) {
                $batch[] = $vatData;

                if (count($batch) >= $batchSize) {
                    $this->processBatch($batch, $stats);
                    $totalProcessed += count($batch);
                    $batch = [];
                    Log::info("Processed {$totalProcessed} VAT records so far");
                }
            }

            if (! empty($batch)) {
                $this->processBatch($batch, $stats);
                $totalProcessed += count($batch);
            }

            $this->markSyncCompleted($syncLog, $stats, $totalProcessed);

            return $stats;
        } catch (Throwable $e) {
            $this->markSyncFailed($syncLog, $e);
            throw $e;
        }
    }

    /**
     * @param  array{updated: int, not_found: int, errors: int}  $stats
     */
    private function markSyncCompleted(object $syncLog, array $stats, int $totalProcessed): void
    {
        Log::info('VAT data sync completed', [
            'total_processed' => $totalProcessed,
            'updated' => $stats['updated'],
            'not_found' => $stats['not_found'],
            'errors' => $stats['errors'],
        ]);

        $this->syncLogRepository->update($syncLog, [
            'status' => CompanySyncStatus::COMPLETED->value,
            'completed_at' => now(),
            'companies_updated' => $stats['updated'],
            'companies_not_found' => $stats['not_found'],
            'errors' => $stats['errors'],
        ]);
    }

    private function markSyncFailed(object $syncLog, Throwable $e): void
    {
        $this->syncLogRepository->update($syncLog, [
            'status' => CompanySyncStatus::FAILED->value,
            'completed_at' => now(),
        ]);

        Log::error('VAT data sync failed', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);
    }

    /**
     * Process a batch of VAT data records.
     *
     * @param  array<int, array<string, mixed>>  $batch
     * @param  array{updated: int, not_found: int, errors: int}  $stats
     */
    private function processBatch(array $batch, array &$stats): void
    {
        try {
            // Prepare batch data with VAT status determination
            $batchData = [];
            foreach ($batch as $vatData) {
                $ico = $vatData['ico'];
                unset($vatData['ico']);

                // Determine VAT payer status based on ic_dph presence
                $vatData['vat_payer_status'] = $this->determineVatPayerStatus($vatData['ic_dph'] ?? null);

                $batchData[$ico] = $vatData;
            }

            // Process batch using repository method with transaction
            $batchStats = $this->companyRepository->updateVatDataBatch($batchData);

            $stats['updated'] += $batchStats['updated'];
            $stats['not_found'] += $batchStats['not_found'];
        } catch (Throwable $e) {
            $stats['errors'] += count($batch);

            Log::error('VAT batch processing failed', [
                'batch_size' => count($batch),
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Determine VAT payer status based on ic_dph presence.
     *
     * If ic_dph is present (not NULL, not empty, not whitespace-only) -> VAT_PAYER
     * If ic_dph is absent (NULL, empty, whitespace-only) -> NOT_VAT_PAYER
     */
    private function determineVatPayerStatus(?string $icDph): string
    {
        if ($icDph === null) {
            return VatPayerStatus::NOT_VAT_PAYER->value;
        }

        $trimmedIcDph = trim($icDph);

        if ($trimmedIcDph === '') {
            return VatPayerStatus::NOT_VAT_PAYER->value;
        }

        return VatPayerStatus::VAT_PAYER->value;
    }
}
