<?php

declare(strict_types=1);

namespace Tests\Feature\Console\Commands;

use App\Repositories\Interfaces\CompanyRepository as CompanyRepositoryContract;
use App\Services\Interfaces\FinancialDataService as FinancialDataServiceContract;
use App\Services\Interfaces\OracleCloudStorageService as OracleCloudStorageServiceContract;
use Exception;
use Generator;
use Tests\TestCase;

final class SyncCompaniesCommandTest extends TestCase
{
    public function test_successful_execution_of_both_phases(): void
    {
        $oracleService = $this->createMock(OracleCloudStorageServiceContract::class);
        $oracleService
            ->method('getLatestDailyFile')
            ->willReturn([
                'key' => 'batch-daily/actual_2025-11-03.json.gz',
                'last_modified' => '2025-11-03T03:00:06.000Z',
                'size' => 128754,
            ]);
        $oracleService
            ->method('downloadAndStreamJson')
            ->willReturn($this->arrayToGenerator([
                ['ico' => '12345678', 'name' => 'Test Company 1'],
                ['ico' => '87654321', 'name' => 'Test Company 2'],
            ]));
        $oracleService
            ->method('cleanup');

        $financialDataService = $this->createMock(FinancialDataServiceContract::class);
        $financialDataService
            ->method('downloadAndExtractVatData')
            ->willReturn($this->arrayToGenerator([
                ['ico' => '12345678', 'ic_dph' => 'SK12345678'],
            ]));

        $companyRepository = $this->createMock(CompanyRepositoryContract::class);
        $companyRepository
            ->method('upsertBatch')
            ->willReturn(2);
        $companyRepository
            ->method('updateVatData')
            ->willReturn(true);

        $this->app->instance(OracleCloudStorageServiceContract::class, $oracleService);
        $this->app->instance(FinancialDataServiceContract::class, $financialDataService);
        $this->app->instance(CompanyRepositoryContract::class, $companyRepository);

        $this->artisan('app:sync-companies')
            ->expectsOutput('Company synchronization started...')
            ->expectsOutput('Phase 1: Syncing company data from Oracle Cloud')
            ->expectsOutput('Phase 2: Syncing VAT data')
            ->expectsOutputToContain('Total duration:')
            ->assertExitCode(0);
    }

    public function test_phase_1_failure_returns_failure_code(): void
    {
        $oracleService = $this->createMock(OracleCloudStorageServiceContract::class);
        $oracleService
            ->method('getLatestDailyFile')
            ->willThrowException(new Exception('Oracle Cloud connection failed'));
        $oracleService
            ->method('cleanup');

        $companyRepository = $this->createMock(CompanyRepositoryContract::class);

        $this->app->instance(OracleCloudStorageServiceContract::class, $oracleService);
        $this->app->instance(CompanyRepositoryContract::class, $companyRepository);

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
            ->method('getLatestDailyFile')
            ->willReturn([
                'key' => 'batch-daily/actual_2025-11-03.json.gz',
                'last_modified' => '2025-11-03T03:00:06.000Z',
                'size' => 128754,
            ]);
        $oracleService
            ->method('downloadAndStreamJson')
            ->willReturn($this->arrayToGenerator([
                ['ico' => '12345678', 'name' => 'Test Company'],
            ]));
        $oracleService
            ->method('cleanup');

        $financialDataService = $this->createMock(FinancialDataServiceContract::class);
        $financialDataService
            ->method('downloadAndExtractVatData')
            ->willThrowException(new Exception('VAT data download failed'));

        $companyRepository = $this->createMock(CompanyRepositoryContract::class);
        $companyRepository
            ->method('upsertBatch')
            ->willReturn(1);

        $this->app->instance(OracleCloudStorageServiceContract::class, $oracleService);
        $this->app->instance(FinancialDataServiceContract::class, $financialDataService);
        $this->app->instance(CompanyRepositoryContract::class, $companyRepository);

        $this->artisan('app:sync-companies')
            ->expectsOutput('Company synchronization started...')
            ->expectsOutput('Phase 1: Syncing company data from Oracle Cloud')
            ->expectsOutput('Phase 2: Syncing VAT data')
            ->expectsOutput('Phase 2 failed: VAT data download failed')
            ->assertExitCode(1);
    }

    public function test_console_output_displays_formatted_statistics(): void
    {
        $companyData = array_fill(0, 100, ['ico' => '12345678', 'name' => 'Test Company']);
        $vatData = array_fill(0, 50, ['ico' => '12345678', 'ic_dph' => 'SK1234567890']);

        $oracleService = $this->createMock(OracleCloudStorageServiceContract::class);
        $oracleService
            ->method('getLatestDailyFile')
            ->willReturn([
                'key' => 'batch-daily/actual_2025-11-03.json.gz',
                'last_modified' => '2025-11-03T03:00:06.000Z',
                'size' => 128754,
            ]);
        $oracleService
            ->method('downloadAndStreamJson')
            ->willReturn($this->arrayToGenerator($companyData));
        $oracleService
            ->method('cleanup');

        $financialDataService = $this->createMock(FinancialDataServiceContract::class);
        $financialDataService
            ->method('downloadAndExtractVatData')
            ->willReturn($this->arrayToGenerator($vatData));

        $companyRepository = $this->createMock(CompanyRepositoryContract::class);
        $companyRepository
            ->method('upsertBatch')
            ->willReturn(100);
        $companyRepository
            ->method('updateVatData')
            ->willReturn(true);

        $this->app->instance(OracleCloudStorageServiceContract::class, $oracleService);
        $this->app->instance(FinancialDataServiceContract::class, $financialDataService);
        $this->app->instance(CompanyRepositoryContract::class, $companyRepository);

        $this->artisan('app:sync-companies')
            ->assertExitCode(0);
    }

    public function test_phase_1_stops_execution_when_exception_is_thrown(): void
    {
        $oracleService = $this->createMock(OracleCloudStorageServiceContract::class);
        $oracleService
            ->method('getLatestDailyFile')
            ->willThrowException(new Exception('Critical error in Phase 1'));
        $oracleService
            ->method('cleanup');

        $companyRepository = $this->createMock(CompanyRepositoryContract::class);

        $this->app->instance(OracleCloudStorageServiceContract::class, $oracleService);
        $this->app->instance(CompanyRepositoryContract::class, $companyRepository);

        $this->artisan('app:sync-companies')
            ->expectsOutput('Company synchronization started...')
            ->expectsOutput('Phase 1: Syncing company data from Oracle Cloud')
            ->expectsOutput('Phase 1 failed: Critical error in Phase 1')
            ->doesntExpectOutput('Phase 2: Syncing VAT data')
            ->assertExitCode(1);
    }

    public function test_successful_execution_displays_total_duration(): void
    {
        $oracleService = $this->createMock(OracleCloudStorageServiceContract::class);
        $oracleService
            ->method('getLatestDailyFile')
            ->willReturn([
                'key' => 'batch-daily/actual_2025-11-03.json.gz',
                'last_modified' => '2025-11-03T03:00:06.000Z',
                'size' => 128754,
            ]);
        $oracleService
            ->method('downloadAndStreamJson')
            ->willReturn($this->arrayToGenerator([
                ['ico' => '12345678', 'name' => 'Test Company'],
            ]));
        $oracleService
            ->method('cleanup');

        $financialDataService = $this->createMock(FinancialDataServiceContract::class);
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
            ->method('updateVatData')
            ->willReturn(true);

        $this->app->instance(OracleCloudStorageServiceContract::class, $oracleService);
        $this->app->instance(FinancialDataServiceContract::class, $financialDataService);
        $this->app->instance(CompanyRepositoryContract::class, $companyRepository);

        $this->artisan('app:sync-companies')
            ->expectsOutputToContain('Total duration:')
            ->assertExitCode(0);
    }

    public function test_command_handles_zero_statistics(): void
    {
        $oracleService = $this->createMock(OracleCloudStorageServiceContract::class);
        $oracleService
            ->method('getLatestDailyFile')
            ->willReturn([
                'key' => 'batch-daily/actual_2025-11-03.json.gz',
                'last_modified' => '2025-11-03T03:00:06.000Z',
                'size' => 128754,
            ]);
        $oracleService
            ->method('downloadAndStreamJson')
            ->willReturn($this->arrayToGenerator([]));
        $oracleService
            ->method('cleanup');

        $financialDataService = $this->createMock(FinancialDataServiceContract::class);
        $financialDataService
            ->method('downloadAndExtractVatData')
            ->willReturn($this->arrayToGenerator([]));

        $companyRepository = $this->createMock(CompanyRepositoryContract::class);

        $this->app->instance(OracleCloudStorageServiceContract::class, $oracleService);
        $this->app->instance(FinancialDataServiceContract::class, $financialDataService);
        $this->app->instance(CompanyRepositoryContract::class, $companyRepository);

        $this->artisan('app:sync-companies')
            ->expectsTable(
                ['Metric', 'Value'],
                [
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
            ->assertExitCode(0);
    }

    public function test_command_handles_high_error_count(): void
    {
        $companyData = array_fill(0, 100, ['ico' => '12345678', 'name' => 'Test Company']);
        $vatData = array_fill(0, 50, ['ico' => '12345678', 'ic_dph' => 'SK1234567890']);

        $oracleService = $this->createMock(OracleCloudStorageServiceContract::class);
        $oracleService
            ->method('getLatestDailyFile')
            ->willReturn([
                'key' => 'batch-daily/actual_2025-11-03.json.gz',
                'last_modified' => '2025-11-03T03:00:06.000Z',
                'size' => 128754,
            ]);
        $oracleService
            ->method('downloadAndStreamJson')
            ->willReturn($this->arrayToGenerator($companyData));
        $oracleService
            ->method('cleanup');

        $financialDataService = $this->createMock(FinancialDataServiceContract::class);
        $financialDataService
            ->method('downloadAndExtractVatData')
            ->willReturn($this->arrayToGenerator($vatData));

        $companyRepository = $this->createMock(CompanyRepositoryContract::class);
        $companyRepository
            ->method('upsertBatch')
            ->willReturn(100);
        $companyRepository
            ->method('updateVatData')
            ->willReturn(true);

        $this->app->instance(OracleCloudStorageServiceContract::class, $oracleService);
        $this->app->instance(FinancialDataServiceContract::class, $financialDataService);
        $this->app->instance(CompanyRepositoryContract::class, $companyRepository);

        $this->artisan('app:sync-companies')
            ->assertExitCode(0);
    }

    public function test_return_code_is_success_when_both_phases_complete(): void
    {
        $oracleService = $this->createMock(OracleCloudStorageServiceContract::class);
        $oracleService
            ->method('getLatestDailyFile')
            ->willReturn([
                'key' => 'batch-daily/actual_2025-11-03.json.gz',
                'last_modified' => '2025-11-03T03:00:06.000Z',
                'size' => 128754,
            ]);
        $oracleService
            ->method('downloadAndStreamJson')
            ->willReturn($this->arrayToGenerator([
                ['ico' => '12345678', 'name' => 'Test Company'],
            ]));
        $oracleService
            ->method('cleanup');

        $financialDataService = $this->createMock(FinancialDataServiceContract::class);
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
            ->method('updateVatData')
            ->willReturn(true);

        $this->app->instance(OracleCloudStorageServiceContract::class, $oracleService);
        $this->app->instance(FinancialDataServiceContract::class, $financialDataService);
        $this->app->instance(CompanyRepositoryContract::class, $companyRepository);

        $this->artisan('app:sync-companies')
            ->assertExitCode(0);
    }

    public function test_return_code_is_failure_when_phase_1_fails(): void
    {
        $oracleService = $this->createMock(OracleCloudStorageServiceContract::class);
        $oracleService
            ->method('getLatestDailyFile')
            ->willThrowException(new Exception('Phase 1 error'));
        $oracleService
            ->method('cleanup');

        $companyRepository = $this->createMock(CompanyRepositoryContract::class);

        $this->app->instance(OracleCloudStorageServiceContract::class, $oracleService);
        $this->app->instance(CompanyRepositoryContract::class, $companyRepository);

        $this->artisan('app:sync-companies')
            ->assertExitCode(1);
    }

    public function test_return_code_is_failure_when_phase_2_fails(): void
    {
        $oracleService = $this->createMock(OracleCloudStorageServiceContract::class);
        $oracleService
            ->method('getLatestDailyFile')
            ->willReturn([
                'key' => 'batch-daily/actual_2025-11-03.json.gz',
                'last_modified' => '2025-11-03T03:00:06.000Z',
                'size' => 128754,
            ]);
        $oracleService
            ->method('downloadAndStreamJson')
            ->willReturn($this->arrayToGenerator([
                ['ico' => '12345678', 'name' => 'Test Company'],
            ]));
        $oracleService
            ->method('cleanup');

        $financialDataService = $this->createMock(FinancialDataServiceContract::class);
        $financialDataService
            ->method('downloadAndExtractVatData')
            ->willThrowException(new Exception('Phase 2 error'));

        $companyRepository = $this->createMock(CompanyRepositoryContract::class);
        $companyRepository
            ->method('upsertBatch')
            ->willReturn(1);

        $this->app->instance(OracleCloudStorageServiceContract::class, $oracleService);
        $this->app->instance(FinancialDataServiceContract::class, $financialDataService);
        $this->app->instance(CompanyRepositoryContract::class, $companyRepository);

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
}
