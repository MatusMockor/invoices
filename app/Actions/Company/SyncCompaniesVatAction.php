<?php

declare(strict_types=1);

namespace App\Actions\Company;

use App\Repositories\Interfaces\CompanyRepository as CompanyRepositoryContract;
use App\Services\Interfaces\FinancialDataService as FinancialDataServiceContract;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

final class SyncCompaniesVatAction
{
    public function __construct(
        private readonly FinancialDataServiceContract $financialDataService,
        private readonly CompanyRepositoryContract $companyRepository,
    ) {}

    /**
     * Sync VAT data for companies from financial data source.
     *
     * @return array{updated: int, not_found: int, errors: int}
     *
     * @throws Throwable
     */
    public function handle(?callable $progressCallback = null): array
    {
        Log::info('Starting VAT data sync from financial data source');

        // Disable query log for better performance during bulk operations
        DB::connection()->disableQueryLog();

        $stats = [
            'updated' => 0,
            'not_found' => 0,
            'errors' => 0,
        ];

        $batchSize = config('financial_data.batch_size', 5000);
        $batch = [];
        $totalProcessed = 0;

        try {
            foreach ($this->financialDataService->downloadAndExtractVatData() as $vatData) {
                $batch[] = $vatData;

                if (count($batch) >= $batchSize) {
                    $this->processBatch($batch, $stats);
                    $totalProcessed += count($batch);
                    $batch = [];

                    // Update progress bar if callback provided
                    if ($progressCallback !== null) {
                        $progressCallback($totalProcessed);
                    }

                    Log::info("Processed {$totalProcessed} VAT records so far");
                }
            }

            if (! empty($batch)) {
                $this->processBatch($batch, $stats);
                $totalProcessed += count($batch);

                // Final progress update
                if ($progressCallback !== null) {
                    $progressCallback($totalProcessed);
                }
            }

            Log::info('VAT data sync completed', [
                'total_processed' => $totalProcessed,
                'updated' => $stats['updated'],
                'not_found' => $stats['not_found'],
                'errors' => $stats['errors'],
            ]);

            return $stats;
        } catch (Throwable $e) {
            Log::error('VAT data sync failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        } finally {
            // Re-enable query log
            DB::connection()->enableQueryLog();
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
        $companyRepository = $this->companyRepository;

        foreach ($batch as $vatData) {
            try {
                DB::transaction(static function () use ($vatData, &$stats, $companyRepository): void {
                    $ico = $vatData['ico'];
                    unset($vatData['ico']);

                    $updated = $companyRepository->updateVatData($ico, $vatData);

                    if ($updated) {
                        $stats['updated']++;

                        return;
                    }

                    $stats['not_found']++;
                    Log::debug('Company not found for VAT update', ['ico' => $ico]);
                });
            } catch (Throwable $e) {
                $stats['errors']++;

                Log::error('VAT record processing failed', [
                    'ico' => $vatData['ico'] ?? 'unknown',
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
}
