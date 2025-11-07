<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Invoice;

use App\Actions\Company\CompanyFetchOrCreateAction;
use App\Actions\Invoice\InvoiceCreateAction;
use App\DTOs\Invoice\InvoiceCreateDTO;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\User;
use App\Models\UserCompany;
use App\Repositories\Contracts\InvoiceItemRepository;
use App\Repositories\Contracts\InvoiceRepository;
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
        $company = Company::factory()->create([
            'dic' => fake()->numerify('##########'),
            'ic_dph' => 'SK'.fake()->numerify('##########'),
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
            invoiceNumber: fake()->numerify('INV-####-###'),
            issueDate: now()->toDateString(),
            dueDate: now()->addDays(14)->toDateString(),
            deliveryDate: now()->toDateString(),
            variableSymbol: fake()->numerify('######'),
            constantSymbol: fake()->numerify('####'),
            specificSymbol: null,
            currency: 'EUR',
            notes: fake()->sentence(),
            status: 'draft',
            items: [
                [
                    'description' => fake()->words(3, true),
                    'quantity' => fake()->numberBetween(1, 10),
                    'price' => fake()->randomFloat(2, 10, 1000),
                ],
            ],
            useCustomCompany: false
        );

        $invoice = $this->action->handle($dto, $this->user->id, $this->userCompany->id);

        $this->assertEquals($company->dic, $invoice->company_dic);
        $this->assertEquals($company->ic_dph, $invoice->company_ic_dph);
        $this->assertEquals($company->ico, $invoice->company_ico);
        $this->assertEquals($company->name, $invoice->company_name);

        $this->assertDatabaseHas(Invoice::class, [
            'id' => $invoice->id,
            'company_id' => $company->id,
            'company_ico' => $company->ico,
            'company_dic' => $company->dic,
            'company_ic_dph' => $company->ic_dph,
            'company_name' => $company->name,
        ]);
    }

    public function test_creates_invoice_with_custom_company_dic_and_ic_dph(): void
    {
        $customIco = fake()->numerify('########');
        $customDic = fake()->numerify('##########');
        $customIcDph = 'SK'.fake()->numerify('##########');
        $customCompanyName = fake()->company();
        $customAddress = fake()->streetAddress();
        $customCity = fake()->city();
        $customZip = fake()->postcode();
        $customCountry = fake()->countryCode();

        $dto = new InvoiceCreateDTO(
            clientName: null,
            clientIco: null,
            clientDic: null,
            clientIcDph: null,
            clientStreet: null,
            clientCity: null,
            clientPostalCode: null,
            clientCountry: null,
            invoiceNumber: fake()->numerify('INV-####-###'),
            issueDate: now()->toDateString(),
            dueDate: now()->addDays(14)->toDateString(),
            deliveryDate: now()->toDateString(),
            variableSymbol: fake()->numerify('######'),
            constantSymbol: fake()->numerify('####'),
            specificSymbol: null,
            currency: 'EUR',
            notes: fake()->sentence(),
            status: 'draft',
            items: [
                [
                    'description' => fake()->words(3, true),
                    'quantity' => fake()->numberBetween(1, 10),
                    'price' => fake()->randomFloat(2, 10, 1000),
                ],
            ],
            useCustomCompany: true,
            customCompanyIco: $customIco,
            customCompanyDic: $customDic,
            customCompanyIcDph: $customIcDph,
            customCompanyName: $customCompanyName,
            customCompanyAddress: $customAddress,
            customCompanyCity: $customCity,
            customCompanyZip: $customZip,
            customCompanyCountry: $customCountry
        );

        $invoice = $this->action->handle($dto, $this->user->id, $this->userCompany->id);

        $this->assertEquals($customDic, $invoice->company_dic);
        $this->assertEquals($customIcDph, $invoice->company_ic_dph);
        $this->assertEquals($customIco, $invoice->company_ico);
        $this->assertEquals($customCompanyName, $invoice->company_name);
        $this->assertNull($invoice->company_id);

        $this->assertDatabaseHas(Invoice::class, [
            'id' => $invoice->id,
            'company_id' => null,
            'company_ico' => $customIco,
            'company_dic' => $customDic,
            'company_ic_dph' => $customIcDph,
            'company_name' => $customCompanyName,
        ]);
    }

    public function test_creates_invoice_with_null_dic_and_ic_dph(): void
    {
        $company = Company::factory()->create([
            'dic' => null,
            'ic_dph' => null,
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
            invoiceNumber: fake()->numerify('INV-####-###'),
            issueDate: now()->toDateString(),
            dueDate: now()->addDays(14)->toDateString(),
            deliveryDate: now()->toDateString(),
            variableSymbol: fake()->numerify('######'),
            constantSymbol: null,
            specificSymbol: null,
            currency: 'EUR',
            notes: null,
            status: 'draft',
            items: [
                [
                    'description' => fake()->words(3, true),
                    'quantity' => fake()->numberBetween(1, 10),
                    'price' => fake()->randomFloat(2, 10, 1000),
                ],
            ],
            useCustomCompany: false
        );

        $invoice = $this->action->handle($dto, $this->user->id, $this->userCompany->id);

        $this->assertNull($invoice->company_dic);
        $this->assertNull($invoice->company_ic_dph);
        $this->assertEquals($company->ico, $invoice->company_ico);

        $this->assertDatabaseHas(Invoice::class, [
            'id' => $invoice->id,
            'company_id' => $company->id,
            'company_ico' => $company->ico,
            'company_dic' => null,
            'company_ic_dph' => null,
        ]);
    }
}
