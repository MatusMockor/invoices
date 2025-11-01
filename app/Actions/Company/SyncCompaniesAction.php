<?php

declare(strict_types=1);

namespace App\Actions\Company;

use App\Repositories\Interfaces\CompanyRepository as CompanyRepositoryContract;
use App\Services\Interfaces\FinancialDataService as FinancialDataServiceContract;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

final class SyncCompaniesAction
{
    public function __construct(
        private readonly FinancialDataServiceContract $financialDataService,
        private readonly CompanyRepositoryContract $companyRepository,
    ) {}

    /**
     * Sync companies from financial data source.
     *
     * @return array{created: int, errors: int}
     */
    public function handle(): array
    {
        Log::info('Starting company sync from financial data source');

        $stats = [
            'created' => 0,
            'errors' => 0,
        ];

        $batchSize = config('financial_data.batch_size');
        $batch = [];
        $totalProcessed = 0;

        try {
            foreach ($this->financialDataService->downloadAndExtractCompanyData() as $companyData) {
                $batch[] = $companyData;

                if (count($batch) >= $batchSize) {
                    $this->processBatch($batch, $stats);
                    $totalProcessed += count($batch);
                    $batch = [];

                    Log::info("Processed {$totalProcessed} companies so far");
                }
            }

            if (! empty($batch)) {
                $this->processBatch($batch, $stats);
                $totalProcessed += count($batch);
            }

            Log::info('Company sync completed', [
                'total_processed' => $totalProcessed,
                'created' => $stats['created'],
                'errors' => $stats['errors'],
            ]);

            return $stats;
        } catch (Throwable $e) {
            Log::error('Company sync failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
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
        try {
            DB::transaction(function () use ($batch, &$stats): void {
                $affectedRows = $this->companyRepository->upsertBatch($batch);

                // Laravel's upsert() returns total affected rows (both created and updated)
                // We cannot distinguish between creates and updates, so we track all as processed
                $stats['created'] += $affectedRows;
            });
        } catch (Throwable $e) {
            $stats['errors'] += count($batch);

            Log::error('Batch processing failed', [
                'batch_size' => count($batch),
                'error' => $e->getMessage(),
            ]);
        }
    }
}
