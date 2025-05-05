<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Partner;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class InvoiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the test user
        $testUser = User::first();

        // Get the user's company
        $company = $testUser->currentCompany ?? Company::where('user_id', $testUser->id)->first();

        // Get some partners
        $partners = Partner::take(3)->get();

        if ($partners->count() > 0) {
            // Create invoices with different statuses
            $this->createDraftInvoice($testUser, $company, $partners->first());
            $this->createSentInvoice($testUser, $company, $partners->skip(1)->first() ?? $partners->first());
            $this->createPaidInvoice($testUser, $company, $partners->skip(2)->first() ?? $partners->first());
        }

        // Create some random invoices with items
        Invoice::factory(5)
            ->has(InvoiceItem::factory()->count(3), 'items')
            ->create();
    }

    /**
     * Create a draft invoice with items
     */
    private function createDraftInvoice(User $user, Company $company, Partner $partner): void
    {
        $invoice = Invoice::factory()->create([
            'user_id' => $user->id,
            'supplier_company_id' => $company->id,
            'partner_id' => $partner->id,
            'invoice_number' => 'INV-'.date('Y').'-001',
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
    private function createSentInvoice(User $user, Company $company, Partner $partner): void
    {
        $invoice = Invoice::factory()->create([
            'user_id' => $user->id,
            'supplier_company_id' => $company->id,
            'partner_id' => $partner->id,
            'invoice_number' => 'INV-'.date('Y').'-002',
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
    private function createPaidInvoice(User $user, Company $company, Partner $partner): void
    {
        $invoice = Invoice::factory()->create([
            'user_id' => $user->id,
            'supplier_company_id' => $company->id,
            'partner_id' => $partner->id,
            'invoice_number' => 'INV-'.date('Y').'-003',
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
}
