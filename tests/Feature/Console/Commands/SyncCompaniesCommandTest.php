<?php

declare(strict_types=1);

namespace Tests\Feature\Console\Commands;

use App\Enums\CompanySyncStatus;
use App\Models\CompanySyncLog;
use App\Repositories\Contracts\CompanyRepository as CompanyRepositoryContract;
use App\Repositories\Contracts\CompanySyncLogRepository as CompanySyncLogRepositoryContract;
use App\Services\Interfaces\FinancialDataService as FinancialDataServiceContract;
use App\Services\Interfaces\OracleCloudStorageService as OracleCloudStorageServiceContract;
use Exception;
use Generator;
use Tests\TestCase;

final class SyncCompaniesCommandTest extends TestCase
{
    public function test_successful_execution_of_all_three_phases(): void
    {
        $oracleService = $this->createMock(OracleCloudStorageServiceContract::class);
        $oracleService
            ->method('getBatchInitFileList')
            ->willReturn([
                'batch-init/init_2025-11-03_001.json.gz',
                'batch-init/init_2025-11-03_002.json.gz',
            ]);
        $oracleService
            ->method('downloadAndStreamJson')
            ->willReturnCallback(fn (): Generator => $this->arrayToGenerator([
                ['ico' => '12345678', 'name' => 'Test Company 1'],
                ['ico' => '87654321', 'name' => 'Test Company 2'],
            ]));
        $oracleService
            ->method('cleanupFile');
        $oracleService
            ->method('cleanup');

        $financialDataService = $this->createMock(FinancialDataServiceContract::class);
        $financialDataService
            ->method('downloadAndExtractDicData')
            ->willReturnCallback(fn (): Generator => $this->arrayToGenerator([
                ['ico' => '12345678', 'dic' => '1234567890'],
            ]));
        $financialDataService
            ->method('downloadAndExtractVatData')
            ->willReturnCallback(fn (): Generator => $this->arrayToGenerator([
                ['ico' => '12345678', 'ic_dph' => 'SK12345678'],
            ]));

        $companyRepository = $this->createMock(CompanyRepositoryContract::class);
        $companyRepository
            ->method('upsertBatch')
            ->willReturn(2);
        $companyRepository
            ->method('updateDicData')
            ->willReturn(true);
        $companyRepository
            ->method('updateVatData')
            ->willReturn(true);

        $syncLogRepository = $this->createMock(CompanySyncLogRepositoryContract::class);
        $syncLogRepository
            ->method('findByDate')
            ->willReturn(null);
        $syncLogRepository
            ->method('findByDateAndType')
            ->willReturn(null);
        $syncLogRepository
            ->method('updateOrCreate')
            ->willReturn($this->createMockSyncLog());
        $syncLogRepository
            ->method('update');

        $this->app->instance(OracleCloudStorageServiceContract::class, $oracleService);
        $this->app->instance(FinancialDataServiceContract::class, $financialDataService);
        $this->app->instance(CompanyRepositoryContract::class, $companyRepository);
        $this->app->instance(CompanySyncLogRepositoryContract::class, $syncLogRepository);

        $this->artisan('app:sync-companies')
            ->expectsOutput('Company synchronization started...')
            ->expectsOutput('Phase 1: Syncing company data from Oracle Cloud')
            ->expectsOutput('Phase 2: Syncing DIC data from Financial Administration')
            ->expectsOutput('Phase 3: Syncing IC DPH data from Financial Administration')
            ->expectsOutputToContain('Total duration:')
            ->assertExitCode(0);
    }

    public function test_phase_1_failure_returns_failure_code(): void
    {
        $oracleService = $this->createMock(OracleCloudStorageServiceContract::class);
        $oracleService
            ->method('getBatchInitFileList')
            ->willThrowException(new Exception('Oracle Cloud connection failed'));
        $oracleService
            ->method('cleanup');

        $syncLogRepository = $this->createMock(CompanySyncLogRepositoryContract::class);
        $syncLogRepository
            ->method('findByDate')
            ->willReturn(null);
        $syncLogRepository
            ->method('updateOrCreate')
            ->willReturn($this->createMockSyncLog());
        $syncLogRepository
            ->method('update');

        $companyRepository = $this->createMock(CompanyRepositoryContract::class);

        $this->app->instance(OracleCloudStorageServiceContract::class, $oracleService);
        $this->app->instance(CompanyRepositoryContract::class, $companyRepository);
        $this->app->instance(CompanySyncLogRepositoryContract::class, $syncLogRepository);

        $this->artisan('app:sync-companies')
            ->expectsOutput('Company synchronization started...')
            ->expectsOutput('Phase 1: Syncing company data from Oracle Cloud')
            ->expectsOutput('Phase 1 failed: Oracle Cloud connection failed')
            ->assertExitCode(1);
    }

    public function test_phase_2_failure_returns_failure_code(): void
    {
        $oracleService = $this->createMock(OracleCloudStorageServiceContract::class);
        $oracleService
            ->method('getBatchInitFileList')
            ->willReturn([
                'batch-init/init_2025-11-03_001.json.gz',
            ]);
        $oracleService
            ->method('downloadAndStreamJson')
            ->willReturn($this->arrayToGenerator([
                ['ico' => '12345678', 'name' => 'Test Company'],
            ]));
        $oracleService
            ->method('cleanupFile');
        $oracleService
            ->method('cleanup');

        $financialDataService = $this->createMock(FinancialDataServiceContract::class);
        $financialDataService
            ->method('downloadAndExtractDicData')
            ->willThrowException(new Exception('DIC data download failed'));

        $companyRepository = $this->createMock(CompanyRepositoryContract::class);
        $companyRepository
            ->method('upsertBatch')
            ->willReturn(1);

        $syncLogRepository = $this->createMock(CompanySyncLogRepositoryContract::class);
        $syncLogRepository
            ->method('findByDate')
            ->willReturn(null);
        $syncLogRepository
            ->method('findByDateAndType')
            ->willReturn(null);
        $syncLogRepository
            ->method('updateOrCreate')
            ->willReturn($this->createMockSyncLog());
        $syncLogRepository
            ->method('update');

        $this->app->instance(OracleCloudStorageServiceContract::class, $oracleService);
        $this->app->instance(FinancialDataServiceContract::class, $financialDataService);
        $this->app->instance(CompanyRepositoryContract::class, $companyRepository);
        $this->app->instance(CompanySyncLogRepositoryContract::class, $syncLogRepository);

        $this->artisan('app:sync-companies')
            ->expectsOutput('Company synchronization started...')
            ->expectsOutput('Phase 1: Syncing company data from Oracle Cloud')
            ->expectsOutput('Phase 2: Syncing DIC data from Financial Administration')
            ->expectsOutput('Phase 2 failed: DIC data download failed')
            ->assertExitCode(1);
    }

    public function test_phase_3_failure_returns_failure_code(): void
    {
        $oracleService = $this->createMock(OracleCloudStorageServiceContract::class);
        $oracleService
            ->method('getBatchInitFileList')
            ->willReturn([
                'batch-init/init_2025-11-03_001.json.gz',
            ]);
        $oracleService
            ->method('downloadAndStreamJson')
            ->willReturn($this->arrayToGenerator([
                ['ico' => '12345678', 'name' => 'Test Company'],
            ]));
        $oracleService
            ->method('cleanupFile');
        $oracleService
            ->method('cleanup');

        $financialDataService = $this->createMock(FinancialDataServiceContract::class);
        $financialDataService
            ->method('downloadAndExtractDicData')
            ->willReturn($this->arrayToGenerator([
                ['ico' => '12345678', 'dic' => '1234567890'],
            ]));
        $financialDataService
            ->method('downloadAndExtractVatData')
            ->willThrowException(new Exception('VAT data download failed'));

        $companyRepository = $this->createMock(CompanyRepositoryContract::class);
        $companyRepository
            ->method('upsertBatch')
            ->willReturn(1);
        $companyRepository
            ->method('updateDicData')
            ->willReturn(true);

        $syncLogRepository = $this->createMock(CompanySyncLogRepositoryContract::class);
        $syncLogRepository
            ->method('findByDate')
            ->willReturn(null);
        $syncLogRepository
            ->method('findByDateAndType')
            ->willReturn(null);
        $syncLogRepository
            ->method('updateOrCreate')
            ->willReturn($this->createMockSyncLog());
        $syncLogRepository
            ->method('update');

        $this->app->instance(OracleCloudStorageServiceContract::class, $oracleService);
        $this->app->instance(FinancialDataServiceContract::class, $financialDataService);
        $this->app->instance(CompanyRepositoryContract::class, $companyRepository);
        $this->app->instance(CompanySyncLogRepositoryContract::class, $syncLogRepository);

        $this->artisan('app:sync-companies')
            ->expectsOutput('Company synchronization started...')
            ->expectsOutput('Phase 1: Syncing company data from Oracle Cloud')
            ->expectsOutput('Phase 2: Syncing DIC data from Financial Administration')
            ->expectsOutput('Phase 3: Syncing IC DPH data from Financial Administration')
            ->expectsOutput('Phase 3 failed: VAT data download failed')
            ->assertExitCode(1);
    }

    public function test_console_output_displays_formatted_statistics(): void
    {
        $companyData = array_fill(0, 100, ['ico' => '12345678', 'name' => 'Test Company']);
        $dicData = array_fill(0, 50, ['ico' => '12345678', 'dic' => '1234567890']);
        $vatData = array_fill(0, 50, ['ico' => '12345678', 'ic_dph' => 'SK1234567890']);

        $oracleService = $this->createMock(OracleCloudStorageServiceContract::class);
        $oracleService
            ->method('getBatchInitFileList')
            ->willReturn([
                'batch-init/init_2025-11-03_001.json.gz',
                'batch-init/init_2025-11-03_002.json.gz',
            ]);
        $oracleService
            ->method('downloadAndStreamJson')
            ->willReturnCallback(fn (): Generator => $this->arrayToGenerator($companyData));
        $oracleService
            ->method('cleanupFile');
        $oracleService
            ->method('cleanup');

        $financialDataService = $this->createMock(FinancialDataServiceContract::class);
        $financialDataService
            ->method('downloadAndExtractDicData')
            ->willReturnCallback(fn (): Generator => $this->arrayToGenerator($dicData));
        $financialDataService
            ->method('downloadAndExtractVatData')
            ->willReturnCallback(fn (): Generator => $this->arrayToGenerator($vatData));

        $companyRepository = $this->createMock(CompanyRepositoryContract::class);
        $companyRepository
            ->method('upsertBatch')
            ->willReturn(100);
        $companyRepository
            ->method('updateDicData')
            ->willReturn(true);
        $companyRepository
            ->method('updateVatData')
            ->willReturn(true);

        $syncLogRepository = $this->createMock(CompanySyncLogRepositoryContract::class);
        $syncLogRepository
            ->method('findByDate')
            ->willReturn(null);
        $syncLogRepository
            ->method('findByDateAndType')
            ->willReturn(null);
        $syncLogRepository
            ->method('updateOrCreate')
            ->willReturn($this->createMockSyncLog());
        $syncLogRepository
            ->method('update');

        $this->app->instance(OracleCloudStorageServiceContract::class, $oracleService);
        $this->app->instance(FinancialDataServiceContract::class, $financialDataService);
        $this->app->instance(CompanyRepositoryContract::class, $companyRepository);
        $this->app->instance(CompanySyncLogRepositoryContract::class, $syncLogRepository);

        $this->artisan('app:sync-companies')
            ->assertExitCode(0);
    }

    public function test_phase_1_stops_execution_when_exception_is_thrown(): void
    {
        $oracleService = $this->createMock(OracleCloudStorageServiceContract::class);
        $oracleService
            ->method('getBatchInitFileList')
            ->willThrowException(new Exception('Critical error in Phase 1'));
        $oracleService
            ->method('cleanup');

        $syncLogRepository = $this->createMock(CompanySyncLogRepositoryContract::class);
        $syncLogRepository
            ->method('findByDate')
            ->willReturn(null);
        $syncLogRepository
            ->method('updateOrCreate')
            ->willReturn($this->createMockSyncLog());
        $syncLogRepository
            ->method('update');

        $companyRepository = $this->createMock(CompanyRepositoryContract::class);

        $this->app->instance(OracleCloudStorageServiceContract::class, $oracleService);
        $this->app->instance(CompanyRepositoryContract::class, $companyRepository);
        $this->app->instance(CompanySyncLogRepositoryContract::class, $syncLogRepository);

        $this->artisan('app:sync-companies')
            ->expectsOutput('Company synchronization started...')
            ->expectsOutput('Phase 1: Syncing company data from Oracle Cloud')
            ->expectsOutput('Phase 1 failed: Critical error in Phase 1')
            ->doesntExpectOutput('Phase 2: Syncing DIC data from Financial Administration')
            ->doesntExpectOutput('Phase 3: Syncing IC DPH data from Financial Administration')
            ->assertExitCode(1);
    }

    public function test_successful_execution_displays_total_duration(): void
    {
        $oracleService = $this->createMock(OracleCloudStorageServiceContract::class);
        $oracleService
            ->method('getBatchInitFileList')
            ->willReturn([
                'batch-init/init_2025-11-03_001.json.gz',
            ]);
        $oracleService
            ->method('downloadAndStreamJson')
            ->willReturn($this->arrayToGenerator([
                ['ico' => '12345678', 'name' => 'Test Company'],
            ]));
        $oracleService
            ->method('cleanupFile');
        $oracleService
            ->method('cleanup');

        $financialDataService = $this->createMock(FinancialDataServiceContract::class);
        $financialDataService
            ->method('downloadAndExtractDicData')
            ->willReturn($this->arrayToGenerator([
                ['ico' => '12345678', 'dic' => '1234567890'],
            ]));
        $financialDataService
            ->method('downloadAndExtractVatData')
            ->willReturn($this->arrayToGenerator([
                ['ico' => '12345678', 'ic_dph' => 'SK12345678'],
            ]));

        $companyRepository = $this->createMock(CompanyRepositoryContract::class);
        $companyRepository
            ->method('upsertBatch')
            ->willReturn(1);
        $companyRepository
            ->method('updateDicData')
            ->willReturn(true);
        $companyRepository
            ->method('updateVatData')
            ->willReturn(true);

        $syncLogRepository = $this->createMock(CompanySyncLogRepositoryContract::class);
        $syncLogRepository
            ->method('findByDate')
            ->willReturn(null);
        $syncLogRepository
            ->method('findByDateAndType')
            ->willReturn(null);
        $syncLogRepository
            ->method('updateOrCreate')
            ->willReturn($this->createMockSyncLog());
        $syncLogRepository
            ->method('update');

        $this->app->instance(OracleCloudStorageServiceContract::class, $oracleService);
        $this->app->instance(FinancialDataServiceContract::class, $financialDataService);
        $this->app->instance(CompanyRepositoryContract::class, $companyRepository);
        $this->app->instance(CompanySyncLogRepositoryContract::class, $syncLogRepository);

        $this->artisan('app:sync-companies')
            ->expectsOutputToContain('Total duration:')
            ->assertExitCode(0);
    }

    public function test_command_handles_zero_statistics(): void
    {
        $oracleService = $this->createMock(OracleCloudStorageServiceContract::class);
        $oracleService
            ->method('getBatchInitFileList')
            ->willReturn([
                'batch-init/init_2025-11-03_001.json.gz',
            ]);
        $oracleService
            ->method('downloadAndStreamJson')
            ->willReturn($this->arrayToGenerator([]));
        $oracleService
            ->method('cleanupFile');
        $oracleService
            ->method('cleanup');

        $financialDataService = $this->createMock(FinancialDataServiceContract::class);
        $financialDataService
            ->method('downloadAndExtractDicData')
            ->willReturn($this->arrayToGenerator([]));
        $financialDataService
            ->method('downloadAndExtractVatData')
            ->willReturn($this->arrayToGenerator([]));

        $syncLogRepository = $this->createMock(CompanySyncLogRepositoryContract::class);
        $syncLogRepository
            ->method('findByDate')
            ->willReturn(null);
        $syncLogRepository
            ->method('findByDateAndType')
            ->willReturn(null);
        $syncLogRepository
            ->method('updateOrCreate')
            ->willReturn($this->createMockSyncLog());
        $syncLogRepository
            ->method('update');

        $companyRepository = $this->createMock(CompanyRepositoryContract::class);

        $this->app->instance(OracleCloudStorageServiceContract::class, $oracleService);
        $this->app->instance(FinancialDataServiceContract::class, $financialDataService);
        $this->app->instance(CompanyRepositoryContract::class, $companyRepository);
        $this->app->instance(CompanySyncLogRepositoryContract::class, $syncLogRepository);

        $this->artisan('app:sync-companies')
            ->expectsTable(
                ['Metric', 'Value'],
                [
                    ['Files', '1'],
                    ['Processed', '0'],
                    ['Errors', '0'],
                ]
            )
            ->expectsTable(
                ['Metric', 'Value'],
                [
                    ['Updated', '0'],
                    ['Not found', '0'],
                    ['Errors', '0'],
                ]
            )
            ->expectsTable(
                ['Metric', 'Value'],
                [
                    ['Updated', '0'],
                    ['Not found', '0'],
                    ['Errors', '0'],
                ]
            )
            ->assertExitCode(0);
    }

    public function test_command_handles_high_error_count(): void
    {
        $companyData = array_fill(0, 100, ['ico' => '12345678', 'name' => 'Test Company']);
        $dicData = array_fill(0, 50, ['ico' => '12345678', 'dic' => '1234567890']);
        $vatData = array_fill(0, 50, ['ico' => '12345678', 'ic_dph' => 'SK1234567890']);

        $oracleService = $this->createMock(OracleCloudStorageServiceContract::class);
        $oracleService
            ->method('getBatchInitFileList')
            ->willReturn([
                'batch-init/init_2025-11-03_001.json.gz',
            ]);
        $oracleService
            ->method('downloadAndStreamJson')
            ->willReturn($this->arrayToGenerator($companyData));
        $oracleService
            ->method('cleanupFile');
        $oracleService
            ->method('cleanup');

        $financialDataService = $this->createMock(FinancialDataServiceContract::class);
        $financialDataService
            ->method('downloadAndExtractDicData')
            ->willReturn($this->arrayToGenerator($dicData));
        $financialDataService
            ->method('downloadAndExtractVatData')
            ->willReturn($this->arrayToGenerator($vatData));

        $companyRepository = $this->createMock(CompanyRepositoryContract::class);
        $companyRepository
            ->method('upsertBatch')
            ->willReturn(100);
        $companyRepository
            ->method('updateDicData')
            ->willReturn(true);
        $companyRepository
            ->method('updateVatData')
            ->willReturn(true);

        $syncLogRepository = $this->createMock(CompanySyncLogRepositoryContract::class);
        $syncLogRepository
            ->method('findByDate')
            ->willReturn(null);
        $syncLogRepository
            ->method('findByDateAndType')
            ->willReturn(null);
        $syncLogRepository
            ->method('updateOrCreate')
            ->willReturn($this->createMockSyncLog());
        $syncLogRepository
            ->method('update');

        $this->app->instance(OracleCloudStorageServiceContract::class, $oracleService);
        $this->app->instance(FinancialDataServiceContract::class, $financialDataService);
        $this->app->instance(CompanyRepositoryContract::class, $companyRepository);
        $this->app->instance(CompanySyncLogRepositoryContract::class, $syncLogRepository);

        $this->artisan('app:sync-companies')
            ->assertExitCode(0);
    }

    public function test_return_code_is_success_when_all_phases_complete(): void
    {
        $oracleService = $this->createMock(OracleCloudStorageServiceContract::class);
        $oracleService
            ->method('getBatchInitFileList')
            ->willReturn([
                'batch-init/init_2025-11-03_001.json.gz',
            ]);
        $oracleService
            ->method('downloadAndStreamJson')
            ->willReturn($this->arrayToGenerator([
                ['ico' => '12345678', 'name' => 'Test Company'],
            ]));
        $oracleService
            ->method('cleanupFile');
        $oracleService
            ->method('cleanup');

        $financialDataService = $this->createMock(FinancialDataServiceContract::class);
        $financialDataService
            ->method('downloadAndExtractDicData')
            ->willReturn($this->arrayToGenerator([
                ['ico' => '12345678', 'dic' => '1234567890'],
            ]));
        $financialDataService
            ->method('downloadAndExtractVatData')
            ->willReturn($this->arrayToGenerator([
                ['ico' => '12345678', 'ic_dph' => 'SK12345678'],
            ]));

        $companyRepository = $this->createMock(CompanyRepositoryContract::class);
        $companyRepository
            ->method('upsertBatch')
            ->willReturn(1);
        $companyRepository
            ->method('updateDicData')
            ->willReturn(true);
        $companyRepository
            ->method('updateVatData')
            ->willReturn(true);

        $syncLogRepository = $this->createMock(CompanySyncLogRepositoryContract::class);
        $syncLogRepository
            ->method('findByDate')
            ->willReturn(null);
        $syncLogRepository
            ->method('findByDateAndType')
            ->willReturn(null);
        $syncLogRepository
            ->method('updateOrCreate')
            ->willReturn($this->createMockSyncLog());
        $syncLogRepository
            ->method('update');

        $this->app->instance(OracleCloudStorageServiceContract::class, $oracleService);
        $this->app->instance(FinancialDataServiceContract::class, $financialDataService);
        $this->app->instance(CompanyRepositoryContract::class, $companyRepository);
        $this->app->instance(CompanySyncLogRepositoryContract::class, $syncLogRepository);

        $this->artisan('app:sync-companies')
            ->assertExitCode(0);
    }

    public function test_return_code_is_failure_when_phase_1_fails(): void
    {
        $oracleService = $this->createMock(OracleCloudStorageServiceContract::class);
        $oracleService
            ->method('getBatchInitFileList')
            ->willThrowException(new Exception('Phase 1 error'));
        $oracleService
            ->method('cleanup');

        $syncLogRepository = $this->createMock(CompanySyncLogRepositoryContract::class);
        $syncLogRepository
            ->method('findByDate')
            ->willReturn(null);
        $syncLogRepository
            ->method('updateOrCreate')
            ->willReturn($this->createMockSyncLog());
        $syncLogRepository
            ->method('update');

        $companyRepository = $this->createMock(CompanyRepositoryContract::class);

        $this->app->instance(OracleCloudStorageServiceContract::class, $oracleService);
        $this->app->instance(CompanyRepositoryContract::class, $companyRepository);
        $this->app->instance(CompanySyncLogRepositoryContract::class, $syncLogRepository);

        $this->artisan('app:sync-companies')
            ->assertExitCode(1);
    }

    public function test_return_code_is_failure_when_phase_2_fails(): void
    {
        $oracleService = $this->createMock(OracleCloudStorageServiceContract::class);
        $oracleService
            ->method('getBatchInitFileList')
            ->willReturn([
                'batch-init/init_2025-11-03_001.json.gz',
            ]);
        $oracleService
            ->method('downloadAndStreamJson')
            ->willReturn($this->arrayToGenerator([
                ['ico' => '12345678', 'name' => 'Test Company'],
            ]));
        $oracleService
            ->method('cleanupFile');
        $oracleService
            ->method('cleanup');

        $financialDataService = $this->createMock(FinancialDataServiceContract::class);
        $financialDataService
            ->method('downloadAndExtractDicData')
            ->willThrowException(new Exception('Phase 2 error'));

        $companyRepository = $this->createMock(CompanyRepositoryContract::class);
        $companyRepository
            ->method('upsertBatch')
            ->willReturn(1);

        $syncLogRepository = $this->createMock(CompanySyncLogRepositoryContract::class);
        $syncLogRepository
            ->method('findByDate')
            ->willReturn(null);
        $syncLogRepository
            ->method('findByDateAndType')
            ->willReturn(null);
        $syncLogRepository
            ->method('updateOrCreate')
            ->willReturn($this->createMockSyncLog());
        $syncLogRepository
            ->method('update');

        $this->app->instance(OracleCloudStorageServiceContract::class, $oracleService);
        $this->app->instance(FinancialDataServiceContract::class, $financialDataService);
        $this->app->instance(CompanyRepositoryContract::class, $companyRepository);
        $this->app->instance(CompanySyncLogRepositoryContract::class, $syncLogRepository);

        $this->artisan('app:sync-companies')
            ->assertExitCode(1);
    }

    /**
     * Helper function to convert array to Generator.
     *
     * @param  array<int, mixed>  $items
     */
    private function arrayToGenerator(array $items): Generator
    {
        foreach ($items as $item) {
            yield $item;
        }
    }

    /**
     * Create a mock CompanySyncLog instance.
     */
    private function createMockSyncLog(): CompanySyncLog
    {
        $syncLog = new CompanySyncLog;
        $syncLog->id = 1;
        $syncLog->sync_date = today();
        $syncLog->status = CompanySyncStatus::PROCESSING->value;

        return $syncLog;
    }
}
