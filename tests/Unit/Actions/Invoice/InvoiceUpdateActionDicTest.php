<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Invoice;

use App\Actions\Company\CompanyFetchOrCreateAction;
use App\Actions\Invoice\InvoiceUpdateAction;
use App\DTOs\Invoice\InvoiceUpdateDTO;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\User;
use App\Models\UserCompany;
use App\Repositories\Interfaces\InvoiceItemRepository;
use App\Repositories\Interfaces\InvoiceRepository;
use App\Services\Invoice\InvoiceTotalCalculatorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Test to verify that DIČ and IČ DPH are properly saved when updating invoices.
 */
class InvoiceUpdateActionDicTest extends TestCase
{
    use RefreshDatabase;

    private InvoiceUpdateAction $action;

    private InvoiceRepository $invoiceRepository;

    private InvoiceItemRepository $invoiceItemRepository;

    private CompanyFetchOrCreateAction $companyFetchOrCreate;

    private InvoiceTotalCalculatorService $totalCalculator;

    private User $user;

    private UserCompany $supplierCompany;

    protected function setUp(): void
    {
        parent::setUp();

        $this->invoiceRepository = app(InvoiceRepository::class);
        $this->invoiceItemRepository = app(InvoiceItemRepository::class);
        $this->companyFetchOrCreate = app(CompanyFetchOrCreateAction::class);
        $this->totalCalculator = app(InvoiceTotalCalculatorService::class);

        $this->action = new InvoiceUpdateAction(
            $this->invoiceRepository,
            $this->invoiceItemRepository,
            $this->companyFetchOrCreate,
            $this->totalCalculator
        );

        $this->user = User::factory()->create();
        $this->supplierCompany = UserCompany::factory()->create();
    }

    public function test_updates_invoice_with_new_company_dic_and_ic_dph(): void
    {
        // Create original company and invoice
        $originalCompany = Company::factory()->create([
            'ico' => '11111111',
            'name' => 'Original Company',
            'dic' => '2011111111',
            'ic_dph' => 'SK2011111111',
        ]);

        $invoice = Invoice::factory()->create([
            'user_id' => $this->user->id,
            'supplier_company_id' => $this->supplierCompany->id,
            'company_id' => $originalCompany->id,
            'company_ico' => $originalCompany->ico,
            'company_dic' => $originalCompany->dic,
            'company_ic_dph' => $originalCompany->ic_dph,
            'company_name' => $originalCompany->name,
        ]);

        // Create new company with different DIČ and IČ DPH
        $newCompany = Company::factory()->create([
            'ico' => '22222222',
            'name' => 'New Company',
            'dic' => '2022222222',
            'ic_dph' => 'SK2022222222',
            'street' => 'New Street',
            'city' => 'New City',
            'postal_code' => '99999',
            'country' => 'SK',
        ]);

        $dto = new InvoiceUpdateDTO(
            clientName: $newCompany->name,
            clientIco: $newCompany->ico,
            clientDic: $newCompany->dic,
            clientIcDph: $newCompany->ic_dph,
            clientStreet: $newCompany->street,
            clientCity: $newCompany->city,
            clientPostalCode: $newCompany->postal_code,
            clientCountry: $newCompany->country,
            invoiceNumber: 'INV-2025-UPDATED',
            issueDate: '2025-11-06',
            dueDate: '2025-11-20',
            deliveryDate: '2025-11-06',
            variableSymbol: null,
            constantSymbol: null,
            specificSymbol: null,
            currency: 'EUR',
            notes: 'Updated invoice',
            status: 'draft',
            items: null,
            useCustomCompany: false
        );

        $updatedInvoice = $this->action->handle($invoice, $dto, $this->supplierCompany->id);

        // Assert that new DIČ and IČ DPH are saved
        $this->assertEquals($newCompany->dic, $updatedInvoice->company_dic);
        $this->assertEquals($newCompany->ic_dph, $updatedInvoice->company_ic_dph);
        $this->assertEquals($newCompany->ico, $updatedInvoice->company_ico);
        $this->assertEquals($newCompany->name, $updatedInvoice->company_name);

        // Assert database has the correct data
        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'company_id' => $newCompany->id,
            'company_ico' => '22222222',
            'company_dic' => '2022222222',
            'company_ic_dph' => 'SK2022222222',
            'company_name' => 'New Company',
        ]);
    }

    public function test_updates_invoice_with_custom_company_dic_and_ic_dph(): void
    {
        // Create original invoice with a company
        $originalCompany = Company::factory()->create([
            'ico' => '33333333',
            'name' => 'Original Company',
        ]);

        $invoice = Invoice::factory()->create([
            'user_id' => $this->user->id,
            'supplier_company_id' => $this->supplierCompany->id,
            'company_id' => $originalCompany->id,
            'company_ico' => $originalCompany->ico,
            'company_name' => $originalCompany->name,
        ]);

        // Update to custom company
        $dto = new InvoiceUpdateDTO(
            clientName: null,
            clientIco: null,
            clientDic: null,
            clientIcDph: null,
            clientStreet: null,
            clientCity: null,
            clientPostalCode: null,
            clientCountry: null,
            invoiceNumber: null,
            issueDate: null,
            dueDate: null,
            deliveryDate: null,
            variableSymbol: null,
            constantSymbol: null,
            specificSymbol: null,
            currency: null,
            notes: null,
            status: null,
            items: null,
            useCustomCompany: true,
            customCompanyIco: '44444444',
            customCompanyDic: '2044444444',
            customCompanyIcDph: 'SK2044444444',
            customCompanyName: 'Custom Updated Company',
            customCompanyAddress: 'Custom Address',
            customCompanyCity: 'Custom City',
            customCompanyZip: '11111',
            customCompanyCountry: 'SK'
        );

        $updatedInvoice = $this->action->handle($invoice, $dto, $this->supplierCompany->id);

        // Assert that custom DIČ and IČ DPH are saved
        $this->assertEquals('2044444444', $updatedInvoice->company_dic);
        $this->assertEquals('SK2044444444', $updatedInvoice->company_ic_dph);
        $this->assertEquals('44444444', $updatedInvoice->company_ico);
        $this->assertEquals('Custom Updated Company', $updatedInvoice->company_name);
        $this->assertNull($updatedInvoice->company_id); // No company_id for custom

        // Assert database has the correct data
        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'company_id' => null,
            'company_ico' => '44444444',
            'company_dic' => '2044444444',
            'company_ic_dph' => 'SK2044444444',
            'company_name' => 'Custom Updated Company',
        ]);
    }

    public function test_preserves_dic_and_ic_dph_when_not_updating_company(): void
    {
        $company = Company::factory()->create([
            'ico' => '55555555',
            'name' => 'Company',
            'dic' => '2055555555',
            'ic_dph' => 'SK2055555555',
        ]);

        $invoice = Invoice::factory()->create([
            'user_id' => $this->user->id,
            'supplier_company_id' => $this->supplierCompany->id,
            'company_id' => $company->id,
            'company_ico' => $company->ico,
            'company_dic' => $company->dic,
            'company_ic_dph' => $company->ic_dph,
            'company_name' => $company->name,
            'invoice_number' => 'INV-2025-ORIGINAL',
        ]);

        // Update only the invoice number, not the company
        $dto = new InvoiceUpdateDTO(
            clientName: null,
            clientIco: null,
            clientDic: null,
            clientIcDph: null,
            clientStreet: null,
            clientCity: null,
            clientPostalCode: null,
            clientCountry: null,
            invoiceNumber: 'INV-2025-CHANGED',
            issueDate: null,
            dueDate: null,
            deliveryDate: null,
            variableSymbol: null,
            constantSymbol: null,
            specificSymbol: null,
            currency: null,
            notes: null,
            status: null,
            items: null,
            useCustomCompany: null // null means don't change company fields
        );

        $updatedInvoice = $this->action->handle($invoice, $dto, $this->supplierCompany->id);

        // Assert that DIČ and IČ DPH are preserved
        $this->assertEquals($company->dic, $updatedInvoice->company_dic);
        $this->assertEquals($company->ic_dph, $updatedInvoice->company_ic_dph);
        $this->assertEquals($company->ico, $updatedInvoice->company_ico);

        // Assert database has the correct data
        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'company_dic' => '2055555555',
            'company_ic_dph' => 'SK2055555555',
            'invoice_number' => 'INV-2025-CHANGED',
        ]);
    }
}
