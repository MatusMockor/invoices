<?php

declare(strict_types=1);

namespace Tests\Feature\Actions\Invoice;

use App\Actions\Invoice\InvoiceCreateAction;
use App\Actions\Invoice\InvoiceUpdateAction;
use App\DTOs\Invoice\InvoiceCreateDTO;
use App\DTOs\Invoice\InvoiceUpdateDTO;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\User;
use App\Models\UserCompany;
use App\Repositories\Contracts\InvoiceItemRepository;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Mockery;
use Tests\TestCase;

final class InvoiceTransactionTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private UserCompany $userCompany;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->userCompany = UserCompany::factory()->create();
        $this->user->update(['current_company_id' => $this->userCompany->id]);

        // Fake HTTP responses for scraper service
        Http::fake([
            '*/scraper/company' => Http::response([
                'data' => [
                    'success' => false,
                    'message' => 'Test mode - using fallback data',
                ],
            ], 200),
        ]);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_invoice_create_rolls_back_on_item_creation_failure(): void
    {
        $initialInvoiceCount = Invoice::count();
        $initialCompanyCount = Company::count();
        $initialItemCount = InvoiceItem::count();

        $mockItemRepository = Mockery::mock(InvoiceItemRepository::class);
        $mockItemRepository->shouldReceive('create')
            ->once()
            ->andThrow(new Exception('Item creation failed'));

        $this->app->instance(InvoiceItemRepository::class, $mockItemRepository);

        $action = app(InvoiceCreateAction::class);

        $dto = new InvoiceCreateDTO(
            clientName: fake()->company(),
            clientIco: fake()->numerify('########'),
            clientDic: fake()->numerify('20########'),
            clientIcDph: null,
            clientStreet: fake()->streetAddress(),
            clientCity: fake()->city(),
            clientPostalCode: fake()->postcode(),
            clientCountry: fake()->countryCode(),
            invoiceNumber: fake()->unique()->numerify('INV-####-####'),
            issueDate: now()->format('Y-m-d'),
            dueDate: now()->addDays(14)->format('Y-m-d'),
            deliveryDate: now()->format('Y-m-d'),
            variableSymbol: null,
            constantSymbol: null,
            specificSymbol: null,
            currency: 'EUR',
            notes: null,
            status: 'draft',
            items: [
                [
                    'description' => fake()->words(2, true),
                    'quantity' => fake()->numberBetween(1, 10),
                    'price' => fake()->randomFloat(2, 10, 100),
                ],
            ]
        );

        try {
            $action->handle($dto, $this->user->id, $this->userCompany->id);
            $this->fail('Expected exception was not thrown');
        } catch (Exception $e) {
            $this->assertEquals('Item creation failed', $e->getMessage());
        }

        $this->assertEquals($initialInvoiceCount, Invoice::count(), 'Invoice should not be created');
        $this->assertEquals($initialCompanyCount, Company::count(), 'Company should not be created');
        $this->assertEquals($initialItemCount, InvoiceItem::count(), 'Items should not be created');
    }

    public function test_invoice_update_rolls_back_on_item_update_failure(): void
    {
        $company = Company::factory()->create();

        $invoice = Invoice::factory()->create([
            'supplier_company_id' => $this->userCompany->id,
            'company_id' => $company->id,
            'user_id' => $this->user->id,
            'invoice_number' => fake()->unique()->numerify('INV-####'),
            'total_amount' => 100.00,
        ]);

        $item = InvoiceItem::factory()->create([
            'invoice_id' => $invoice->id,
            'description' => fake()->words(2, true),
            'quantity' => 2,
            'unit_price' => 50.00,
            'total_price' => 100.00,
        ]);

        $originalInvoiceNumber = $invoice->invoice_number;
        $originalItemDescription = $item->description;

        $mockItemRepository = Mockery::mock(InvoiceItemRepository::class);
        $mockItemRepository->shouldReceive('deleteItemsNotInIds')->once();
        $mockItemRepository->shouldReceive('upsert')
            ->once()
            ->andThrow(new Exception('Item update failed'));

        $this->app->instance(InvoiceItemRepository::class, $mockItemRepository);

        $action = app(InvoiceUpdateAction::class);

        $dto = new InvoiceUpdateDTO(
            clientName: null,
            clientIco: null,
            clientDic: null,
            clientIcDph: null,
            clientStreet: null,
            clientCity: null,
            clientPostalCode: null,
            clientCountry: null,
            invoiceNumber: fake()->unique()->numerify('INV-UPDATED-####'),
            issueDate: null,
            dueDate: null,
            deliveryDate: null,
            variableSymbol: null,
            constantSymbol: null,
            specificSymbol: null,
            currency: null,
            notes: null,
            status: null,
            items: [
                [
                    'id' => $item->id,
                    'description' => fake()->words(2, true),
                    'quantity' => 5,
                    'price' => 100.00,
                ],
            ]
        );

        try {
            $action->handle($invoice, $dto, $this->userCompany->id);
            $this->fail('Expected exception was not thrown');
        } catch (Exception $e) {
            $this->assertEquals('Item update failed', $e->getMessage());
        }

        $invoice->refresh();
        $item->refresh();

        $this->assertEquals($originalInvoiceNumber, $invoice->invoice_number, 'Invoice number should not change');
        $this->assertEquals($originalItemDescription, $item->description, 'Item description should not change');
        $this->assertEquals(100.00, $invoice->total_amount, 'Total amount should not change');
    }

    public function test_invoice_create_commits_successfully_when_all_operations_succeed(): void
    {
        $initialInvoiceCount = Invoice::count();
        $initialCompanyCount = Company::count();
        $initialItemCount = InvoiceItem::count();

        $action = app(InvoiceCreateAction::class);

        $clientIco = fake()->numerify('########');

        $dto = new InvoiceCreateDTO(
            clientName: fake()->company(),
            clientIco: $clientIco,
            clientDic: fake()->numerify('20########'),
            clientIcDph: null,
            clientStreet: fake()->streetAddress(),
            clientCity: fake()->city(),
            clientPostalCode: fake()->postcode(),
            clientCountry: fake()->countryCode(),
            invoiceNumber: fake()->unique()->numerify('INV-####-####'),
            issueDate: now()->format('Y-m-d'),
            dueDate: now()->addDays(14)->format('Y-m-d'),
            deliveryDate: now()->format('Y-m-d'),
            variableSymbol: null,
            constantSymbol: null,
            specificSymbol: null,
            currency: 'EUR',
            notes: null,
            status: 'draft',
            items: [
                [
                    'description' => fake()->words(2, true),
                    'quantity' => 2,
                    'price' => 50.00,
                ],
                [
                    'description' => fake()->words(2, true),
                    'quantity' => 1,
                    'price' => 100.00,
                ],
            ]
        );

        $invoice = $action->handle($dto, $this->user->id, $this->userCompany->id);

        $this->assertEquals($initialInvoiceCount + 1, Invoice::count(), 'One invoice should be created');
        $this->assertEquals($initialCompanyCount + 1, Company::count(), 'One company should be created');
        $this->assertEquals($initialItemCount + 2, InvoiceItem::count(), 'Two items should be created');

        $this->assertDatabaseHas(Company::class, [
            'ico' => $clientIco,
        ]);

        $this->assertDatabaseHas(Invoice::class, [
            'id' => $invoice->id,
            'total_amount' => 200.00,
        ]);

        $this->assertCount(2, $invoice->items);
    }

    public function test_invoice_update_commits_successfully_when_all_operations_succeed(): void
    {
        $company = Company::factory()->create();

        $invoice = Invoice::factory()->create([
            'supplier_company_id' => $this->userCompany->id,
            'company_id' => $company->id,
            'user_id' => $this->user->id,
            'invoice_number' => fake()->unique()->numerify('INV-####'),
            'total_amount' => 100.00,
        ]);

        $item = InvoiceItem::factory()->create([
            'invoice_id' => $invoice->id,
            'quantity' => 2,
            'unit_price' => 50.00,
        ]);

        $action = app(InvoiceUpdateAction::class);

        $newInvoiceNumber = fake()->unique()->numerify('INV-UPDATED-####');
        $newDescription = fake()->words(2, true);

        $dto = new InvoiceUpdateDTO(
            clientName: null,
            clientIco: null,
            clientDic: null,
            clientIcDph: null,
            clientStreet: null,
            clientCity: null,
            clientPostalCode: null,
            clientCountry: null,
            invoiceNumber: $newInvoiceNumber,
            issueDate: null,
            dueDate: null,
            deliveryDate: null,
            variableSymbol: null,
            constantSymbol: null,
            specificSymbol: null,
            currency: null,
            notes: null,
            status: 'paid',
            items: [
                [
                    'id' => $item->id,
                    'description' => $newDescription,
                    'quantity' => 5,
                    'price' => 100.00,
                ],
            ]
        );

        $updatedInvoice = $action->handle($invoice, $dto, $this->userCompany->id);

        $this->assertEquals($newInvoiceNumber, $updatedInvoice->invoice_number);
        $this->assertEquals('paid', $updatedInvoice->status);
        $this->assertEquals(500.00, $updatedInvoice->total_amount);

        $item->refresh();
        $this->assertEquals($newDescription, $item->description);
        $this->assertEquals(5, $item->quantity);
    }
}
