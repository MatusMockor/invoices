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

        // Check if VAT sync already completed for today
        $existingSync = $this->syncLogRepository->findByDateAndType($today, CompanySyncType::VATUPDATE->value);

        if ($existingSync?->status === CompanySyncStatus::COMPLETED) {
            Log::info('VAT sync already completed for today', ['date' => $today]);

            return [
                'updated' => $existingSync->companies_updated ?? 0,
                'not_found' => $existingSync->companies_not_found ?? 0,
                'errors' => $existingSync->errors,
            ];
        }

        // Create or update sync log
        $syncLog = $this->syncLogRepository->updateOrCreate(
            [
                'sync_date' => $today,
                'sync_type' => CompanySyncType::VATUPDATE->value,
            ],
            [
                'status' => CompanySyncStatus::PROCESSING->value,
                'started_at' => now(),
                'companies_updated' => 0,
                'companies_not_found' => 0,
                'errors' => 0,
            ]
        );

        $stats = [
            'updated' => 0,
            'not_found' => 0,
            'errors' => 0,
        ];

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

            Log::info('VAT data sync completed', [
                'total_processed' => $totalProcessed,
                'updated' => $stats['updated'],
                'not_found' => $stats['not_found'],
                'errors' => $stats['errors'],
            ]);

            // Mark sync as completed
            $this->syncLogRepository->update($syncLog, [
                'status' => CompanySyncStatus::COMPLETED->value,
                'completed_at' => now(),
                'companies_updated' => $stats['updated'],
                'companies_not_found' => $stats['not_found'],
                'errors' => $stats['errors'],
            ]);

            return $stats;
        } catch (Throwable $e) {
            // Mark sync as failed
            $this->syncLogRepository->update($syncLog, [
                'status' => CompanySyncStatus::FAILED->value,
                'completed_at' => now(),
            ]);

            Log::error('VAT data sync failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
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
                // Note: We default to REGISTERED_PARAGRAPH_7A when ic_dph is present from sync
                // as we don't have information about whether it's mandatory or voluntary registration
                $vatData['vat_payer_status'] = ! empty($vatData['ic_dph'])
                    ? VatPayerStatus::VAT_PAYER->value
                    : VatPayerStatus::NOT_VAT_PAYER->value;

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
}
