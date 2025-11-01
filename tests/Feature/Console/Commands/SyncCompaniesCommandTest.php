<?php

declare(strict_types=1);

namespace Tests\Feature\Console\Commands;

use App\Repositories\Interfaces\CompanyRepository as CompanyRepositoryContract;
use App\Services\Interfaces\FinancialDataService as FinancialDataServiceContract;
use Exception;
use Generator;
use Tests\TestCase;

final class SyncCompaniesCommandTest extends TestCase
{
    public function test_successful_execution_of_both_phases(): void
    {
        $financialDataService = $this->createMock(FinancialDataServiceContract::class);
        $financialDataService
            ->method('downloadAndExtractCompanyData')
            ->willReturn($this->arrayToGenerator([
                ['ico' => '12345678', 'name' => 'Test Company 1'],
                ['ico' => '87654321', 'name' => 'Test Company 2'],
            ]));
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

        $this->app->instance(FinancialDataServiceContract::class, $financialDataService);
        $this->app->instance(CompanyRepositoryContract::class, $companyRepository);

        $this->artisan('app:sync-companies')
            ->expectsOutput('Company synchronization started...')
            ->expectsOutput('Phase 1: Syncing company data')
            ->expectsOutput('Phase 2: Syncing VAT data')
            ->expectsOutputToContain('Total duration:')
            ->assertExitCode(0);
    }

    public function test_phase_1_failure_returns_failure_code(): void
    {
        $financialDataService = $this->createMock(FinancialDataServiceContract::class);
        $financialDataService
            ->method('downloadAndExtractCompanyData')
            ->willThrowException(new Exception('Database connection failed'));

        $companyRepository = $this->createMock(CompanyRepositoryContract::class);

        $this->app->instance(FinancialDataServiceContract::class, $financialDataService);
        $this->app->instance(CompanyRepositoryContract::class, $companyRepository);

        $this->artisan('app:sync-companies')
            ->expectsOutput('Company synchronization started...')
            ->expectsOutput('Phase 1: Syncing company data')
            ->expectsOutput('Phase 1 failed: Database connection failed')
            ->assertExitCode(1);
    }

    public function test_phase_2_failure_returns_failure_code(): void
    {
        $financialDataService = $this->createMock(FinancialDataServiceContract::class);
        $financialDataService
            ->method('downloadAndExtractCompanyData')
            ->willReturn($this->arrayToGenerator([
                ['ico' => '12345678', 'name' => 'Test Company'],
            ]));
        $financialDataService
            ->method('downloadAndExtractVatData')
            ->willThrowException(new Exception('VAT data download failed'));

        $companyRepository = $this->createMock(CompanyRepositoryContract::class);
        $companyRepository
            ->method('upsertBatch')
            ->willReturn(1);

        $this->app->instance(FinancialDataServiceContract::class, $financialDataService);
        $this->app->instance(CompanyRepositoryContract::class, $companyRepository);

        $this->artisan('app:sync-companies')
            ->expectsOutput('Company synchronization started...')
            ->expectsOutput('Phase 1: Syncing company data')
            ->expectsOutput('Phase 2: Syncing VAT data')
            ->expectsOutput('Phase 2 failed: VAT data download failed')
            ->assertExitCode(1);
    }

    public function test_console_output_displays_formatted_statistics(): void
    {
        $companyData = array_fill(0, 100, ['ico' => '12345678', 'name' => 'Test Company']);
        $vatData = array_fill(0, 50, ['ico' => '12345678', 'ic_dph' => 'SK1234567890']);

        $financialDataService = $this->createMock(FinancialDataServiceContract::class);
        $financialDataService
            ->method('downloadAndExtractCompanyData')
            ->willReturn($this->arrayToGenerator($companyData));
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

        $this->app->instance(FinancialDataServiceContract::class, $financialDataService);
        $this->app->instance(CompanyRepositoryContract::class, $companyRepository);

        $this->artisan('app:sync-companies')
            ->assertExitCode(0);
    }

    public function test_phase_1_stops_execution_when_exception_is_thrown(): void
    {
        $financialDataService = $this->createMock(FinancialDataServiceContract::class);
        $financialDataService
            ->method('downloadAndExtractCompanyData')
            ->willThrowException(new Exception('Critical error in Phase 1'));

        $companyRepository = $this->createMock(CompanyRepositoryContract::class);

        $this->app->instance(FinancialDataServiceContract::class, $financialDataService);
        $this->app->instance(CompanyRepositoryContract::class, $companyRepository);

        $this->artisan('app:sync-companies')
            ->expectsOutput('Phase 1 failed: Critical error in Phase 1')
            ->doesntExpectOutput('Phase 2: Syncing VAT data')
            ->assertExitCode(1);
    }

    public function test_successful_execution_displays_total_duration(): void
    {
        $financialDataService = $this->createMock(FinancialDataServiceContract::class);
        $financialDataService
            ->method('downloadAndExtractCompanyData')
            ->willReturn($this->arrayToGenerator([
                ['ico' => '12345678', 'name' => 'Test Company'],
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
            ->method('updateVatData')
            ->willReturn(true);

        $this->app->instance(FinancialDataServiceContract::class, $financialDataService);
        $this->app->instance(CompanyRepositoryContract::class, $companyRepository);

        $this->artisan('app:sync-companies')
            ->expectsOutputToContain('Total duration:')
            ->assertExitCode(0);
    }

    public function test_command_handles_zero_statistics(): void
    {
        $financialDataService = $this->createMock(FinancialDataServiceContract::class);
        $financialDataService
            ->method('downloadAndExtractCompanyData')
            ->willReturn($this->arrayToGenerator([]));
        $financialDataService
            ->method('downloadAndExtractVatData')
            ->willReturn($this->arrayToGenerator([]));

        $companyRepository = $this->createMock(CompanyRepositoryContract::class);

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

        $financialDataService = $this->createMock(FinancialDataServiceContract::class);
        $financialDataService
            ->method('downloadAndExtractCompanyData')
            ->willReturn($this->arrayToGenerator($companyData));
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

        $this->app->instance(FinancialDataServiceContract::class, $financialDataService);
        $this->app->instance(CompanyRepositoryContract::class, $companyRepository);

        $this->artisan('app:sync-companies')
            ->assertExitCode(0);
    }

    public function test_return_code_is_success_when_both_phases_complete(): void
    {
        $financialDataService = $this->createMock(FinancialDataServiceContract::class);
        $financialDataService
            ->method('downloadAndExtractCompanyData')
            ->willReturn($this->arrayToGenerator([
                ['ico' => '12345678', 'name' => 'Test Company'],
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
            ->method('updateVatData')
            ->willReturn(true);

        $this->app->instance(FinancialDataServiceContract::class, $financialDataService);
        $this->app->instance(CompanyRepositoryContract::class, $companyRepository);

        $this->artisan('app:sync-companies')
            ->assertExitCode(0);
    }

    public function test_return_code_is_failure_when_phase_1_fails(): void
    {
        $financialDataService = $this->createMock(FinancialDataServiceContract::class);
        $financialDataService
            ->method('downloadAndExtractCompanyData')
            ->willThrowException(new Exception('Phase 1 error'));

        $companyRepository = $this->createMock(CompanyRepositoryContract::class);

        $this->app->instance(FinancialDataServiceContract::class, $financialDataService);
        $this->app->instance(CompanyRepositoryContract::class, $companyRepository);

        $this->artisan('app:sync-companies')
            ->assertExitCode(1);
    }

    public function test_return_code_is_failure_when_phase_2_fails(): void
    {
        $financialDataService = $this->createMock(FinancialDataServiceContract::class);
        $financialDataService
            ->method('downloadAndExtractCompanyData')
            ->willReturn($this->arrayToGenerator([
                ['ico' => '12345678', 'name' => 'Test Company'],
            ]));
        $financialDataService
            ->method('downloadAndExtractVatData')
            ->willThrowException(new Exception('Phase 2 error'));

        $companyRepository = $this->createMock(CompanyRepositoryContract::class);
        $companyRepository
            ->method('upsertBatch')
            ->willReturn(1);

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
