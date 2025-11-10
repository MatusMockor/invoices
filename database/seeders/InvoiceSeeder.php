<?php

declare(strict_types=1);

namespace Database\Seeders;

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
            'status' => 'draft',
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
            'status' => 'sent',
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
            'status' => 'paid',
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
            'status' => 'draft',
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
     * Create invoice items and update invoice total.
     *
     * @param  array<int, array{description: string, quantity: int|float, unit_price: float}>  $items
     */
    private function createInvoiceItems(Invoice $invoice, array $items): void
    {
        $totalAmount = 0;

        foreach ($items as $item) {
            $totalPrice = $item['quantity'] * $item['unit_price'];
            $totalAmount += $totalPrice;

            InvoiceItem::factory()->create([
                'invoice_id' => $invoice->id,
                'description' => $item['description'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'total_price' => $totalPrice,
            ]);
        }

        $invoice->update(['total_amount' => $totalAmount]);
    }

    /**
     * Generate bulk invoices with items for performance testing.
     */
    private function generateBulkInvoices(): void
    {
        $users = User::with('currentCompany')->get();
        $companies = Company::inRandomOrder()->limit(500)->pluck('id')->toArray();
        $userCompanies = UserCompany::pluck('id')->toArray();

        if ($users->isEmpty() || empty($companies) || empty($userCompanies)) {
            $this->command->warn('Insufficient data: need users, companies, and user companies');

            return;
        }

        $statuses = ['draft', 'sent', 'paid', 'cancelled'];
        $invoiceCounter = 20250005;
        $batches = (int) ceil(self::TOTAL_INVOICES / self::BATCH_SIZE);

        for ($batchIndex = 0; $batchIndex < $batches; $batchIndex++) {
            $invoicesBatch = [];
            $batchSize = min(self::BATCH_SIZE, self::TOTAL_INVOICES - ($batchIndex * self::BATCH_SIZE));

            for ($i = 0; $i < $batchSize; $i++) {
                $user = $users->random();
                $companyId = $companies[array_rand($companies)];
                $supplierCompanyId = $userCompanies[array_rand($userCompanies)];

                $issueDate = Carbon::now()->subDays(fake()->numberBetween(0, 180));
                $dueDate = (clone $issueDate)->addDays(fake()->numberBetween(7, 30));
                $deliveryDate = (clone $issueDate)->addDays(fake()->numberBetween(0, 7));

                $invoicesBatch[] = [
                    'user_id' => $user->id,
                    'supplier_company_id' => $supplierCompanyId,
                    'company_id' => $companyId,
                    'invoice_number' => (string) $invoiceCounter++,
                    'issue_date' => $issueDate,
                    'due_date' => $dueDate,
                    'delivery_date' => $deliveryDate,
                    'total_amount' => 0,
                    'currency' => 'EUR',
                    'status' => $statuses[array_rand($statuses)],
                    'constant_symbol' => fake()->optional(0.7)->numerify('####'),
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
     * Generate invoice items for a batch of invoices.
     *
     * @param  array<int>  $invoiceIds
     */
    private function generateInvoiceItemsForBatch(array $invoiceIds): void
    {
        $itemsBatch = [];
        $invoiceTotals = [];

        foreach ($invoiceIds as $invoiceId) {
            $itemCount = fake()->numberBetween(self::ITEMS_PER_INVOICE_MIN, self::ITEMS_PER_INVOICE_MAX);
            $invoiceTotal = 0;

            for ($i = 0; $i < $itemCount; $i++) {
                $quantity = fake()->numberBetween(1, 20);
                $unitPrice = fake()->randomFloat(2, 10, 500);
                $totalPrice = round($quantity * $unitPrice, 2);
                $invoiceTotal += $totalPrice;

                $itemsBatch[] = [
                    'invoice_id' => $invoiceId,
                    'description' => fake()->sentence(fake()->numberBetween(3, 8)),
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'total_price' => $totalPrice,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ];
            }

            $invoiceTotals[$invoiceId] = round($invoiceTotal, 2);
        }

        InvoiceItem::insert($itemsBatch);

        // Update invoice totals using Eloquent
        foreach ($invoiceTotals as $invoiceId => $total) {
            Invoice::where('id', $invoiceId)->update(['total_amount' => $total]);
        }
    }
}
