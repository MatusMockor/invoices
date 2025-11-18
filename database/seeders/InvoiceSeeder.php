<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\InvoiceStatus;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\User;
use App\Models\UserCompany;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class InvoiceSeeder extends Seeder
{
    private const TOTAL_INVOICES = 3_000;

    private const BATCH_SIZE = 500;

    private const PROGRESS_INTERVAL = 500;

    private const ITEMS_PER_INVOICE_MIN = 1;

    private const ITEMS_PER_INVOICE_MAX = 8;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $testUser = User::where('email', 'test@example.com')->first();

        if (! $testUser) {
            $this->command->warn('Test user (test@example.com) not found. Skipping invoice seeding.');

            return;
        }

        $userCompany = $testUser->currentCompany;

        if (! $userCompany) {
            $this->command->warn('Test user has no current company. Skipping invoice seeding.');

            return;
        }

        $externalCompanies = Company::factory(3)->create();

        $this->command->info('Creating 4 specific test invoices for test@example.com...');

        $this->createDraftInvoice($testUser, $userCompany, $externalCompanies->first());
        $this->createSentInvoice($testUser, $userCompany, $externalCompanies->skip(1)->first() ?? $externalCompanies->first());
        $this->createPaidInvoice($testUser, $userCompany, $externalCompanies->skip(2)->first() ?? $externalCompanies->first());
        $this->createCustomCompanyInvoice($testUser, $userCompany);

        $this->command->info('Starting to generate '.number_format(self::TOTAL_INVOICES).' invoices with items...');

        $startTime = microtime(true);
        $this->generateBulkInvoices();
        $duration = round(microtime(true) - $startTime, 2);

        $this->command->info('Successfully generated '.number_format(self::TOTAL_INVOICES)." invoices in {$duration} seconds");
    }

    /**
     * Create a draft invoice with items.
     * Scenario: Draft invoice with standard services for a client.
     */
    private function createDraftInvoice(User $user, UserCompany $userCompany, Company $company): void
    {
        $invoice = Invoice::factory()->create([
            'user_id' => $user->id,
            'supplier_company_id' => $userCompany->id,
            'company_id' => $company->id,
            'company_ico' => $company->ico,
            'company_dic' => $company->dic,
            'company_ic_dph' => $company->ic_dph,
            'company_name' => $company->name,
            'company_address' => $company->street,
            'company_city' => $company->city,
            'company_zip' => $company->postal_code,
            'company_country' => $company->country,
            'invoice_number' => '20250001',
            'issue_date' => Carbon::now(),
            'due_date' => Carbon::now()->addDays(14),
            'status' => InvoiceStatus::DRAFT,
            'total_amount' => 0, // Will be calculated from items
        ]);

        $items = [
            [
                'description' => 'Web Development Services',
                'quantity' => 10,
                'unit_price' => 50.00,
            ],
            [
                'description' => 'UI/UX Design',
                'quantity' => 5,
                'unit_price' => 60.00,
            ],
        ];

        $this->createInvoiceItems($invoice, $items);
    }

    /**
     * Create a sent invoice with items.
     * Scenario: Sent invoice with recurring hosting and domain services.
     */
    private function createSentInvoice(User $user, UserCompany $userCompany, Company $company): void
    {
        $invoice = Invoice::factory()->create([
            'user_id' => $user->id,
            'supplier_company_id' => $userCompany->id,
            'company_id' => $company->id,
            'company_ico' => $company->ico,
            'company_dic' => $company->dic,
            'company_ic_dph' => $company->ic_dph,
            'company_name' => $company->name,
            'company_address' => $company->street,
            'company_city' => $company->city,
            'company_zip' => $company->postal_code,
            'company_country' => $company->country,
            'invoice_number' => '20250002',
            'issue_date' => Carbon::now()->subDays(7),
            'due_date' => Carbon::now()->addDays(7),
            'status' => InvoiceStatus::SENT,
            'total_amount' => 0, // Will be calculated from items
        ]);

        $items = [
            [
                'description' => 'Monthly Hosting Services',
                'quantity' => 1,
                'unit_price' => 99.99,
            ],
            [
                'description' => 'Domain Renewal',
                'quantity' => 2,
                'unit_price' => 15.00,
            ],
            [
                'description' => 'SSL Certificate',
                'quantity' => 1,
                'unit_price' => 45.00,
            ],
        ];

        $this->createInvoiceItems($invoice, $items);
    }

    /**
     * Create a paid invoice with items.
     * Scenario: Paid invoice with consultation and project management services.
     */
    private function createPaidInvoice(User $user, UserCompany $userCompany, Company $company): void
    {
        $invoice = Invoice::factory()->create([
            'user_id' => $user->id,
            'supplier_company_id' => $userCompany->id,
            'company_id' => $company->id,
            'company_ico' => $company->ico,
            'company_dic' => $company->dic,
            'company_ic_dph' => $company->ic_dph,
            'company_name' => $company->name,
            'company_address' => $company->street,
            'company_city' => $company->city,
            'company_zip' => $company->postal_code,
            'company_country' => $company->country,
            'invoice_number' => '20250003',
            'issue_date' => Carbon::now()->subDays(30),
            'due_date' => Carbon::now()->subDays(15),
            'status' => InvoiceStatus::PAID,
            'total_amount' => 0, // Will be calculated from items
        ]);

        $items = [
            [
                'description' => 'Software Development Consultation',
                'quantity' => 8,
                'unit_price' => 120.00,
            ],
            [
                'description' => 'Project Management',
                'quantity' => 4,
                'unit_price' => 80.00,
            ],
        ];

        $this->createInvoiceItems($invoice, $items);
    }

    /**
     * Create an invoice with custom company data (not linked to companies table).
     * Scenario: Invoice with manually entered client company details.
     */
    private function createCustomCompanyInvoice(User $user, UserCompany $userCompany): void
    {
        $invoice = Invoice::factory()->withCustomCompany()->create([
            'user_id' => $user->id,
            'supplier_company_id' => $userCompany->id,
            'invoice_number' => '20250004',
            'issue_date' => Carbon::now()->subDays(5),
            'due_date' => Carbon::now()->addDays(10),
            'status' => InvoiceStatus::DRAFT,
            'total_amount' => 0, // Will be calculated from items
            'company_ico' => '12345678',
            'company_dic' => '2023456789',
            'company_ic_dph' => 'SK2023456789',
            'company_name' => 'Custom Client Company s.r.o.',
            'company_address' => 'Bratislavská 123',
            'company_city' => 'Bratislava',
            'company_zip' => '81101',
            'company_country' => 'Slovakia',
        ]);

        $items = [
            [
                'description' => 'Custom Company Service',
                'quantity' => 3,
                'unit_price' => 150.00,
            ],
            [
                'description' => 'Additional Support',
                'quantity' => 2,
                'unit_price' => 75.00,
            ],
        ];

        $this->createInvoiceItems($invoice, $items);
    }

    /**
     * Create invoice items and update invoice total with VAT.
     *
     * @param  array<int, array{description: string, quantity: int|float, unit_price: float, tax_rate?: float}>  $items
     */
    private function createInvoiceItems(Invoice $invoice, array $items): void
    {
        $invoiceSubtotal = 0;
        $invoiceTaxAmount = 0;

        foreach ($items as $item) {
            $taxRate = $item['tax_rate'] ?? 20.0; // Default Slovak VAT rate
            $subtotal = round($item['quantity'] * $item['unit_price'], 2);
            $taxAmount = round($subtotal * ($taxRate / 100), 2);
            $totalPrice = round($subtotal + $taxAmount, 2);

            $invoiceSubtotal += $subtotal;
            $invoiceTaxAmount += $taxAmount;

            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'description' => $item['description'],
                'quantity' => $item['quantity'],
                'unit_price_without_tax' => $item['unit_price'],
                'tax_rate' => $taxRate,
                'tax_amount' => $taxAmount,
                'subtotal' => $subtotal,
                'discount_amount' => null,
                'total_price' => $totalPrice,
            ]);
        }

        $invoice->update([
            'subtotal' => round($invoiceSubtotal, 2),
            'tax_amount' => round($invoiceTaxAmount, 2),
            'tax_rate' => 20.0, // Default rate
            'total_amount' => round($invoiceSubtotal + $invoiceTaxAmount, 2),
        ]);
    }

    /**
     * Generate bulk invoices with items for performance testing.
     */
    private function generateBulkInvoices(): void
    {
        $users = User::with('currentCompany')->get();
        $companies = Company::inRandomOrder()->limit(500)->get();
        $userCompanies = UserCompany::pluck('id')->toArray();

        if ($users->isEmpty() || $companies->isEmpty() || empty($userCompanies)) {
            $this->command->warn('Insufficient data: need users, companies, and user companies');

            return;
        }

        $statuses = [
            InvoiceStatus::DRAFT,
            InvoiceStatus::SENT,
            InvoiceStatus::PAID,
            InvoiceStatus::CANCELLED,
        ];
        $invoiceCounter = 20250005;
        $batches = (int) ceil(self::TOTAL_INVOICES / self::BATCH_SIZE);

        for ($batchIndex = 0; $batchIndex < $batches; $batchIndex++) {
            $invoicesBatch = [];
            $batchSize = min(self::BATCH_SIZE, self::TOTAL_INVOICES - ($batchIndex * self::BATCH_SIZE));

            for ($i = 0; $i < $batchSize; $i++) {
                $user = $users->random();
                $company = $companies->random();
                $supplierCompanyId = $userCompanies[array_rand($userCompanies)];

                $issueDate = Carbon::now()->subDays(fake()->numberBetween(0, 180));
                $dueDate = (clone $issueDate)->addDays(fake()->numberBetween(7, 30));
                $deliveryDate = (clone $issueDate)->addDays(fake()->numberBetween(0, 7));

                $invoicesBatch[] = [
                    'user_id' => $user->id,
                    'supplier_company_id' => $supplierCompanyId,
                    'company_id' => $company->id,
                    'company_ico' => $company->ico,
                    'company_dic' => $company->dic,
                    'company_ic_dph' => $company->ic_dph,
                    'company_name' => $company->name,
                    'company_address' => $company->street,
                    'company_city' => $company->city,
                    'company_zip' => $company->postal_code,
                    'company_country' => $company->country,
                    'invoice_number' => (string) $invoiceCounter++,
                    'issue_date' => $issueDate,
                    'due_date' => $dueDate,
                    'delivery_date' => $deliveryDate,
                    'subtotal' => 0, // Will be calculated from items
                    'tax_amount' => 0, // Will be calculated from items
                    'tax_rate' => 20.0, // Default Slovak VAT rate
                    'total_amount' => 0, // Will be calculated from items
                    'discount_amount' => null,
                    'discount_percentage' => null,
                    'reverse_charge' => false,
                    'tax_exemption_reason' => null,
                    'special_text' => null,
                    'notes' => null,
                    'currency' => 'EUR',
                    'status' => $statuses[array_rand($statuses)],
                    'constant_symbol' => fake()->optional(0.7)->numerify('####'),
                    'variable_symbol' => fake()->optional(0.8)->numerify('########'),
                    'note' => fake()->optional(0.5)->sentence(),
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ];
            }

            Invoice::insert($invoicesBatch);

            $invoiceIds = Invoice::query()
                ->latest('id')
                ->limit($batchSize)
                ->pluck('id')
                ->toArray();

            $this->generateInvoiceItemsForBatch($invoiceIds);

            $processedCount = ($batchIndex + 1) * self::BATCH_SIZE;

            if ($processedCount % self::PROGRESS_INTERVAL === 0 || $processedCount >= self::TOTAL_INVOICES) {
                $actualCount = min($processedCount, self::TOTAL_INVOICES);
                $this->command->info("Generated {$actualCount} / ".self::TOTAL_INVOICES.' invoices');
            }
        }
    }

    /**
     * Generate invoice items for a batch of invoices with VAT calculations.
     *
     * @param  array<int>  $invoiceIds
     */
    private function generateInvoiceItemsForBatch(array $invoiceIds): void
    {
        $itemsBatch = [];
        $invoiceTotals = [];

        $slovakVatRates = [20.0, 10.0, 0.0]; // Slovak VAT rates

        foreach ($invoiceIds as $invoiceId) {
            $itemCount = fake()->numberBetween(self::ITEMS_PER_INVOICE_MIN, self::ITEMS_PER_INVOICE_MAX);
            $invoiceSubtotal = 0;
            $invoiceTaxAmount = 0;

            for ($i = 0; $i < $itemCount; $i++) {
                $quantity = fake()->numberBetween(1, 20);
                $unitPriceWithoutTax = fake()->randomFloat(2, 10, 500);
                $taxRate = $slovakVatRates[array_rand($slovakVatRates)];

                // Calculate VAT-compliant amounts
                $subtotal = round($quantity * $unitPriceWithoutTax, 2);
                $taxAmount = round($subtotal * ($taxRate / 100), 2);
                $totalPrice = round($subtotal + $taxAmount, 2);

                $invoiceSubtotal += $subtotal;
                $invoiceTaxAmount += $taxAmount;

                $itemsBatch[] = [
                    'invoice_id' => $invoiceId,
                    'description' => fake()->sentence(fake()->numberBetween(3, 8)),
                    'quantity' => $quantity,
                    'unit_price_without_tax' => $unitPriceWithoutTax,
                    'tax_rate' => $taxRate,
                    'tax_amount' => $taxAmount,
                    'subtotal' => $subtotal,
                    'discount_amount' => null,
                    'total_price' => $totalPrice,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ];
            }

            $invoiceTotals[$invoiceId] = [
                'subtotal' => round($invoiceSubtotal, 2),
                'tax_amount' => round($invoiceTaxAmount, 2),
                'total_amount' => round($invoiceSubtotal + $invoiceTaxAmount, 2),
            ];
        }

        InvoiceItem::insert($itemsBatch);

        // Update invoice totals with VAT breakdown using Eloquent
        foreach ($invoiceTotals as $invoiceId => $totals) {
            Invoice::where('id', $invoiceId)->update($totals);
        }
    }
}
