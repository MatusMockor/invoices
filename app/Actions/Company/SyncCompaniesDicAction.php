<?php

declare(strict_types=1);

namespace App\Actions\Company;

use App\Enums\CompanySyncStatus;
use App\Enums\CompanySyncType;
use App\Repositories\Interfaces\CompanyRepository as CompanyRepositoryContract;
use App\Repositories\Interfaces\CompanySyncLogRepository as CompanySyncLogRepositoryContract;
use App\Services\Interfaces\FinancialDataService as FinancialDataServiceContract;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

final class SyncCompaniesDicAction
{
    public function __construct(
        private readonly FinancialDataServiceContract $financialDataService,
        private readonly CompanyRepositoryContract $companyRepository,
        private readonly CompanySyncLogRepositoryContract $syncLogRepository,
    ) {}

    /**
     * Sync DIC data for companies from financial data source.
     *
     * @return array{updated: int, not_found: int, errors: int}
     *
     * @throws Throwable
     */
    public function handle(): array
    {
        Log::info('Starting DIC data sync from financial data source');

        $today = today()->toDateString();

        // Check if DIC sync already completed for today
        $existingSync = $this->syncLogRepository->findByDateAndType($today, CompanySyncType::DICUPDATE->value);

        if ($existingSync?->status === CompanySyncStatus::COMPLETED) {
            Log::info('DIC sync already completed for today', ['date' => $today]);

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
                'sync_type' => CompanySyncType::DICUPDATE->value,
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
            foreach ($this->financialDataService->downloadAndExtractDicData() as $dicData) {
                $batch[] = $dicData;

                if (count($batch) >= $batchSize) {
                    $this->processBatch($batch, $stats);
                    $totalProcessed += count($batch);
                    $batch = [];

                    Log::info("Processed {$totalProcessed} DIC records so far");
                }
            }

            if (! empty($batch)) {
                $this->processBatch($batch, $stats);
                $totalProcessed += count($batch);
            }

            Log::info('DIC data sync completed', [
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

            Log::error('DIC data sync failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Process a batch of DIC data records.
     *
     * @param  array<int, array<string, mixed>>  $batch
     * @param  array{updated: int, not_found: int, errors: int}  $stats
     */
    private function processBatch(array $batch, array &$stats): void
    {
        $companyRepository = $this->companyRepository;

        try {
            DB::transaction(static function () use ($batch, &$stats, $companyRepository): void {
                foreach ($batch as $dicData) {
                    $ico = $dicData['ico'];
                    unset($dicData['ico']);

                    $updated = $companyRepository->updateDicData($ico, $dicData);

                    if ($updated) {
                        $stats['updated']++;
                    } else {
                        $stats['not_found']++;
                        Log::debug('Company not found for DIC update', ['ico' => $ico]);
                    }
                }
            });
        } catch (Throwable $e) {
            $stats['errors'] += count($batch);

            Log::error('DIC batch processing failed', [
                'batch_size' => count($batch),
                'error' => $e->getMessage(),
            ]);
        }
    }
}
