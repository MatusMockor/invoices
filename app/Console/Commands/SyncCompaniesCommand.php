<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Actions\Company\SyncCompaniesDicAction;
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
    protected $description = 'Sync Slovak company data: Phase 1 from Oracle Cloud, Phase 2 DIC, Phase 3 IC DPH from Financial Administration';

    /**
     * Execute the console command.
     */
    public function handle(
        SyncCompaniesFromOracleAction $oracleAction,
        SyncCompaniesDicAction $dicAction,
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

        $this->newLine();

        // Phase 2: Sync DIC data from Financial Administration
        $phase2Result = $this->executePhase2($dicAction);

        if ($phase2Result !== self::SUCCESS) {
            return $phase2Result;
        }

        $this->newLine();

        // Phase 3: Sync IC DPH data from Financial Administration
        $phase3Result = $this->executePhase3($vatAction);

        if ($phase3Result !== self::SUCCESS) {
            return $phase3Result;
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
        return $this->executePhase(
            'Phase 1: Syncing company data from Oracle Cloud',
            fn (): array => $action->handle(),
            [
                'Files' => 'files_processed',
                'Processed' => 'created',
                'Errors' => 'errors',
            ]
        );
    }

    /**
     * Execute Phase 2: Sync DIC data from Financial Administration.
     */
    private function executePhase2(SyncCompaniesDicAction $action): int
    {
        return $this->executePhase(
            'Phase 2: Syncing DIC data from Financial Administration',
            fn (): array => $action->handle(),
            [
                'Updated' => 'updated',
                'Not found' => 'not_found',
                'Errors' => 'errors',
            ]
        );
    }

    /**
     * Execute Phase 3: Sync IC DPH data from Financial Administration.
     */
    private function executePhase3(SyncCompaniesVatAction $action): int
    {
        return $this->executePhase(
            'Phase 3: Syncing IC DPH data from Financial Administration',
            fn (): array => $action->handle(),
            [
                'Updated' => 'updated',
                'Not found' => 'not_found',
                'Errors' => 'errors',
            ]
        );
    }

    /**
     * Execute a sync phase with common error handling and stats display.
     *
     * @param  array<string, string>  $statsKeys
     */
    private function executePhase(string $phaseName, callable $action, array $statsKeys): int
    {
        $this->info($phaseName);

        $startTime = microtime(true);

        try {
            $stats = $action();

            $duration = $this->formatDuration(microtime(true) - $startTime);

            $tableData = [];
            foreach ($statsKeys as $label => $key) {
                $tableData[] = [$label, number_format($stats[$key] ?? 0)];
            }
            $tableData[] = ['Duration', $duration];

            $this->table(['Metric', 'Value'], $tableData);

            return self::SUCCESS;
        } catch (Throwable $e) {
            $phaseNumber = explode(':', $phaseName)[0];
            $this->error("{$phaseNumber} failed: ".$e->getMessage());

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
