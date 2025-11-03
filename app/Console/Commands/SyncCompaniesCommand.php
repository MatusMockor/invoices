<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Actions\Company\SyncCompaniesFromOracleAction;
use App\Actions\Company\SyncCompaniesVatAction;
use Illuminate\Console\Command;
use Throwable;

class SyncCompaniesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-companies';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync Slovak company data: Phase 1 from Oracle Cloud, Phase 2 VAT from Financial Administration';

    /**
     * Execute the console command.
     */
    public function handle(
        SyncCompaniesFromOracleAction $oracleAction,
        SyncCompaniesVatAction $vatAction
    ): int {
        $this->info('Company synchronization started...');
        $this->newLine();

        $overallStartTime = microtime(true);

        // Phase 1: Sync company data from Oracle Cloud
        $phase1Result = $this->executePhase1($oracleAction);

        if ($phase1Result !== self::SUCCESS) {
            return $phase1Result;
        }

        dd(123);
        $this->newLine();

        // Phase 2: Sync VAT data from Financial Administration
        $phase2Result = $this->executePhase2($vatAction);

        if ($phase2Result !== self::SUCCESS) {
            return $phase2Result;
        }

        $totalDuration = $this->formatDuration(microtime(true) - $overallStartTime);

        $this->newLine();
        $this->info("Total duration: {$totalDuration}");

        return self::SUCCESS;
    }

    /**
     * Execute Phase 1: Sync company data from Oracle Cloud.
     */
    private function executePhase1(SyncCompaniesFromOracleAction $action): int
    {
        $this->info('Phase 1: Syncing company data from Oracle Cloud');

        $startTime = microtime(true);

        try {
            $stats = $action->handle();

            $duration = $this->formatDuration(microtime(true) - $startTime);

            $this->table(
                ['Metric', 'Value'],
                [
                    ['Files', number_format($stats['files_processed'] ?? 0)],
                    ['Processed', number_format($stats['created'])],
                    ['Errors', number_format($stats['errors'])],
                    ['Duration', $duration],
                ]
            );

            return self::SUCCESS;
        } catch (Throwable $e) {
            $this->error('Phase 1 failed: '.$e->getMessage());

            return self::FAILURE;
        }
    }

    /**
     * Execute Phase 2: Sync VAT data.
     */
    private function executePhase2(SyncCompaniesVatAction $action): int
    {
        $this->info('Phase 2: Syncing VAT data');

        $startTime = microtime(true);

        try {
            $stats = $action->handle();

            $duration = $this->formatDuration(microtime(true) - $startTime);

            $this->table(
                ['Metric', 'Value'],
                [
                    ['Updated', number_format($stats['updated'])],
                    ['Not found', number_format($stats['not_found'])],
                    ['Errors', number_format($stats['errors'])],
                    ['Duration', $duration],
                ]
            );

            return self::SUCCESS;
        } catch (Throwable $e) {
            $this->error('Phase 2 failed: '.$e->getMessage());

            return self::FAILURE;
        }
    }

    /**
     * Format duration in seconds to human-readable format.
     */
    private function formatDuration(float $seconds): string
    {
        $minutes = floor($seconds / 60);
        $remainingSeconds = round($seconds % 60, 2);

        if ($minutes > 0) {
            return "{$minutes}m {$remainingSeconds}s";
        }

        return "{$remainingSeconds}s";
    }
}
