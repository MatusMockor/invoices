<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Invoice;

use App\Actions\Company\CompanyFetchOrCreateAction;
use App\Actions\Invoice\InvoiceCreateAction;
use App\DTOs\Invoice\InvoiceCreateDTO;
use App\Models\Company;
use App\Models\User;
use App\Models\UserCompany;
use App\Repositories\Interfaces\InvoiceItemRepository;
use App\Repositories\Interfaces\InvoiceRepository;
use App\Services\Invoice\InvoiceTotalCalculatorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Test to verify that DIČ and IČ DPH are properly saved when creating invoices.
 */
class InvoiceCreateActionDicTest extends TestCase
{
    use RefreshDatabase;

    private InvoiceCreateAction $action;

    private InvoiceRepository $invoiceRepository;

    private InvoiceItemRepository $invoiceItemRepository;

    private CompanyFetchOrCreateAction $companyFetchOrCreate;

    private InvoiceTotalCalculatorService $totalCalculator;

    private User $user;

    private UserCompany $userCompany;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->userCompany = UserCompany::factory()->forUser($this->user)->create();

        $this->invoiceRepository = app(InvoiceRepository::class);
        $this->invoiceItemRepository = app(InvoiceItemRepository::class);
        $this->companyFetchOrCreate = app(CompanyFetchOrCreateAction::class);
        $this->totalCalculator = app(InvoiceTotalCalculatorService::class);

        $this->action = new InvoiceCreateAction(
            $this->invoiceRepository,
            $this->invoiceItemRepository,
            $this->companyFetchOrCreate,
            $this->totalCalculator
        );
    }

    public function test_creates_invoice_with_dic_and_ic_dph_from_company(): void
    {
        // Create a company with DIČ and IČ DPH
        $company = Company::factory()->create([
            'ico' => '12345678',
            'name' => 'Test Company s.r.o.',
            'dic' => '2012345678',
            'ic_dph' => 'SK2012345678',
            'street' => 'Test Street 123',
            'city' => 'Bratislava',
            'postal_code' => '81101',
            'country' => 'SK',
        ]);

        $dto = new InvoiceCreateDTO(
            clientName: $company->name,
            clientIco: $company->ico,
            clientDic: $company->dic,
            clientIcDph: $company->ic_dph,
            clientStreet: $company->street,
            clientCity: $company->city,
            clientPostalCode: $company->postal_code,
            clientCountry: $company->country,
            invoiceNumber: 'INV-2025-001',
            issueDate: '2025-11-06',
            dueDate: '2025-11-20',
            deliveryDate: '2025-11-06',
            variableSymbol: '2025001',
            constantSymbol: '0308',
            specificSymbol: null,
            currency: 'EUR',
            notes: 'Test invoice',
            status: 'draft',
            items: [
                [
                    'description' => 'Test Service',
                    'quantity' => 2,
                    'price' => 100.00,
                ],
            ],
            useCustomCompany: false
        );

        $invoice = $this->action->handle($dto, $this->user->id, $this->userCompany->id);

        // Assert that DIČ and IČ DPH are saved in the invoice
        $this->assertEquals($company->dic, $invoice->company_dic);
        $this->assertEquals($company->ic_dph, $invoice->company_ic_dph);
        $this->assertEquals($company->ico, $invoice->company_ico);
        $this->assertEquals($company->name, $invoice->company_name);

        // Assert database has the correct data
        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'company_id' => $company->id,
            'company_ico' => '12345678',
            'company_dic' => '2012345678',
            'company_ic_dph' => 'SK2012345678',
            'company_name' => 'Test Company s.r.o.',
        ]);
    }

    public function test_creates_invoice_with_custom_company_dic_and_ic_dph(): void
    {
        $dto = new InvoiceCreateDTO(
            clientName: null,
            clientIco: null,
            clientDic: null,
            clientIcDph: null,
            clientStreet: null,
            clientCity: null,
            clientPostalCode: null,
            clientCountry: null,
            invoiceNumber: 'INV-2025-002',
            issueDate: '2025-11-06',
            dueDate: '2025-11-20',
            deliveryDate: '2025-11-06',
            variableSymbol: '2025002',
            constantSymbol: '0308',
            specificSymbol: null,
            currency: 'EUR',
            notes: 'Test invoice with custom company',
            status: 'draft',
            items: [
                [
                    'description' => 'Test Service',
                    'quantity' => 1,
                    'price' => 150.00,
                ],
            ],
            useCustomCompany: true,
            customCompanyIco: '87654321',
            customCompanyDic: '2087654321',
            customCompanyIcDph: 'SK2087654321',
            customCompanyName: 'Custom Company Ltd.',
            customCompanyAddress: 'Custom Street 456',
            customCompanyCity: 'Košice',
            customCompanyZip: '04001',
            customCompanyCountry: 'SK'
        );

        $invoice = $this->action->handle($dto, $this->user->id, $this->userCompany->id);

        // Assert that custom DIČ and IČ DPH are saved in the invoice
        $this->assertEquals('2087654321', $invoice->company_dic);
        $this->assertEquals('SK2087654321', $invoice->company_ic_dph);
        $this->assertEquals('87654321', $invoice->company_ico);
        $this->assertEquals('Custom Company Ltd.', $invoice->company_name);
        $this->assertNull($invoice->company_id); // No company_id for custom companies

        // Assert database has the correct data
        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'company_id' => null,
            'company_ico' => '87654321',
            'company_dic' => '2087654321',
            'company_ic_dph' => 'SK2087654321',
            'company_name' => 'Custom Company Ltd.',
        ]);
    }

    public function test_creates_invoice_with_null_dic_and_ic_dph(): void
    {
        // Create a company without DIČ and IČ DPH
        $company = Company::factory()->create([
            'ico' => '99999999',
            'name' => 'Company Without DIC',
            'dic' => null,
            'ic_dph' => null,
            'street' => 'Some Street',
            'city' => 'Some City',
            'postal_code' => '12345',
            'country' => 'SK',
        ]);

        $dto = new InvoiceCreateDTO(
            clientName: $company->name,
            clientIco: $company->ico,
            clientDic: null,
            clientIcDph: null,
            clientStreet: $company->street,
            clientCity: $company->city,
            clientPostalCode: $company->postal_code,
            clientCountry: $company->country,
            invoiceNumber: 'INV-2025-003',
            issueDate: '2025-11-06',
            dueDate: '2025-11-20',
            deliveryDate: '2025-11-06',
            variableSymbol: '2025003',
            constantSymbol: null,
            specificSymbol: null,
            currency: 'EUR',
            notes: null,
            status: 'draft',
            items: [
                [
                    'description' => 'Test Service',
                    'quantity' => 1,
                    'price' => 50.00,
                ],
            ],
            useCustomCompany: false
        );

        $invoice = $this->action->handle($dto, $this->user->id, $this->userCompany->id);

        // Assert that DIČ and IČ DPH are null in the invoice
        $this->assertNull($invoice->company_dic);
        $this->assertNull($invoice->company_ic_dph);
        $this->assertEquals($company->ico, $invoice->company_ico);

        // Assert database has the correct data
        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'company_id' => $company->id,
            'company_ico' => '99999999',
            'company_dic' => null,
            'company_ic_dph' => null,
        ]);
    }
}
