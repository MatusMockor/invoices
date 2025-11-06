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
use Illuminate\Support\Facades\Facade;

class InvoiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the test user
        $testUser = User::where('email', 'test@example.com')->first();

        if (! $testUser) {
            return;
        }

        // Get the user's company (supplier)
        $userCompany = $testUser->currentCompany;

        if (! $userCompany) {
            return;
        }

        // Create some external companies (customers)
        $externalCompanies = Company::factory(3)->create();

        // Create invoices with different statuses
        $this->createDraftInvoice($testUser, $userCompany, $externalCompanies->first());
        $this->createSentInvoice($testUser, $userCompany, $externalCompanies->skip(1)->first() ?? $externalCompanies->first());
        $this->createPaidInvoice($testUser, $userCompany, $externalCompanies->skip(2)->first() ?? $externalCompanies->first());
        $this->createCustomCompanyInvoice($testUser, $userCompany);

        // Create some random invoices with items
        Invoice::factory(5)
            ->has(InvoiceItem::factory()->count(3), 'items')
            ->create();
    }

    /**
     * Create a draft invoice with items
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

        // Create invoice items
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

        // Update the invoice total
        $invoice->update(['total_amount' => $totalAmount]);
    }

    /**
     * Create a sent invoice with items
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

        // Create invoice items
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

        // Update the invoice total
        $invoice->update(['total_amount' => $totalAmount]);
    }

    /**
     * Create a paid invoice with items
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

        // Create invoice items
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

        // Update the invoice total
        $invoice->update(['total_amount' => $totalAmount]);
    }

    /**
     * Create an invoice with custom company data (not linked to companies table)
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

        // Create invoice items
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

        // Update the invoice total
        $invoice->update(['total_amount' => $totalAmount]);
    }
}
