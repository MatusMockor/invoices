<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\InvoiceStatus;
use App\Enums\VatPayerStatus;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\User;
use App\Models\UserCompany;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Invoice>
 */
class InvoiceFactory extends Factory
{
    /**
     * Counter for generating sequential invoice numbers.
     *
     * @var int
     */
    protected static $invoiceCounter = 1;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Invoice::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $year = 2025;
        $invoiceNumber = $year.str_pad((string) self::$invoiceCounter++, 4, '0', STR_PAD_LEFT);

        // Generate VAT-compliant amounts
        $subtotal = fake()->randomFloat(2, 100, 10000);
        $taxRate = fake()->randomElement([20.0, 10.0, 0.0]); // Slovak VAT rates
        $taxAmount = round($subtotal * ($taxRate / 100), 2);
        $totalAmount = round($subtotal + $taxAmount, 2);

        return [
            'user_id' => User::factory(),
            'invoice_number' => $invoiceNumber,
            'issue_date' => fake()->dateTimeBetween('-30 days', 'now'),
            'due_date' => fake()->dateTimeBetween('now', '+30 days'),
            'delivery_date' => fake()->dateTimeBetween('-15 days', '+15 days'),
            'company_id' => Company::factory(),
            'supplier_company_id' => UserCompany::factory(),
            // Supplier snapshot fields - will be set in configure()
            'supplier_registry_office' => null,
            'supplier_registry_number' => null,
            'supplier_vat_payer_status' => null,
            'supplier_vat_period' => null,
            'company_ico' => null,
            'company_dic' => null,
            'company_ic_dph' => null,
            'company_name' => null,
            'company_address' => null,
            'company_city' => null,
            'company_zip' => null,
            'company_country' => null,
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'tax_rate' => $taxRate,
            'total_amount' => $totalAmount,
            'discount_amount' => null,
            'discount_percentage' => null,
            'reverse_charge' => false,
            'tax_exemption_reason' => null,
            'special_text' => null,
            'notes' => null,
            'currency' => 'EUR',
            'constant_symbol' => fake()->optional(0.7)->numerify('####'),
            'note' => fake()->optional(0.7)->sentence(),
            'status' => fake()->randomElement([
                InvoiceStatus::DRAFT,
                InvoiceStatus::SENT,
                InvoiceStatus::PAID,
                InvoiceStatus::CANCELLED,
            ]),
        ];
    }

    /**
     * Configure the factory to copy company data after creation.
     */
    public function configure(): Factory
    {
        return $this->afterCreating(function (Invoice $invoice): void {
            // Copy customer company data
            if ($invoice->company_id && ! $invoice->company_name) {
                $company = Company::find($invoice->company_id);

                if ($company) {
                    $invoice->update([
                        'company_ico' => $company->ico,
                        'company_dic' => $company->dic,
                        'company_ic_dph' => $company->ic_dph,
                        'company_name' => $company->name,
                        'company_address' => $company->street,
                        'company_city' => $company->city,
                        'company_zip' => $company->postal_code,
                        'company_country' => $company->country,
                    ]);
                }
            }

            // Copy supplier snapshot data (registry, VAT status) only if not explicitly set
            if ($invoice->supplier_company_id) {
                $supplierCompany = UserCompany::find($invoice->supplier_company_id);

                if ($supplierCompany) {
                    $updateData = [];

                    // Only copy if not explicitly set
                    if (! $invoice->supplier_registry_office) {
                        $updateData['supplier_registry_office'] = $supplierCompany->registration_office;
                        $updateData['supplier_registry_number'] = $supplierCompany->registration_number;
                    }

                    if ($invoice->supplier_vat_payer_status === null) {
                        $updateData['supplier_vat_payer_status'] = $supplierCompany->vat_payer_status;
                        $updateData['supplier_vat_period'] = $supplierCompany->vat_period;
                    }

                    if (! empty($updateData)) {
                        $invoice->update($updateData);
                    }
                }
            }
        });
    }

    /**
     * Indicate that the invoice is in draft status.
     */
    public function draft(): Factory
    {
        return $this->state(fn (array $attributes): array => [
            'status' => InvoiceStatus::DRAFT,
        ]);
    }

    /**
     * Indicate that the invoice has been sent.
     */
    public function sent(): Factory
    {
        return $this->state(fn (array $attributes): array => [
            'status' => InvoiceStatus::SENT,
        ]);
    }

    /**
     * Indicate that the invoice has been paid.
     */
    public function paid(): Factory
    {
        return $this->state(fn (array $attributes): array => [
            'status' => InvoiceStatus::PAID,
        ]);
    }

    /**
     * Indicate that the invoice has been cancelled.
     */
    public function cancelled(): Factory
    {
        return $this->state(fn (array $attributes): array => [
            'status' => InvoiceStatus::CANCELLED,
        ]);
    }

    /**
     * Indicate that the invoice uses custom company data instead of a company_id.
     */
    public function withCustomCompany(): Factory
    {
        return $this->state(fn (array $attributes): array => [
            'company_id' => null,
            'company_ico' => fake()->numerify('########'),
            'company_dic' => fake()->numerify('##########'),
            'company_ic_dph' => fake()->optional(0.8)->regexify('SK[0-9]{10}'),
            'company_name' => fake()->company(),
            'company_address' => fake()->streetAddress(),
            'company_city' => fake()->city(),
            'company_zip' => fake()->postcode(),
            'company_country' => fake()->country(),
        ]);
    }

    /**
     * Create an invoice with supplier registry snapshot data.
     */
    public function withSupplierRegistry(): Factory
    {
        return $this->state(fn (array $attributes): array => [
            'supplier_registry_office' => fake()->randomElement([
                'Okresny sud Bratislava I',
                'Okresny sud Kosice I',
                'Okresny sud Zilina',
            ]),
            'supplier_registry_number' => 'Oddiel: Sro, Vlozka c. '.fake()->numerify('######/B'),
        ]);
    }

    /**
     * Create an invoice without supplier registry data (for backwards compatibility testing).
     */
    public function withoutSupplierRegistry(): Factory
    {
        return $this->state(fn (array $attributes): array => [
            'supplier_registry_office' => null,
            'supplier_registry_number' => null,
        ]);
    }

    /**
     * Create an invoice with VAT payer status.
     */
    public function vatPayer(): Factory
    {
        return $this->state(fn (array $attributes): array => [
            'supplier_vat_payer_status' => VatPayerStatus::VAT_PAYER,
        ]);
    }

    /**
     * Create an invoice with non-VAT payer status.
     */
    public function notVatPayer(): Factory
    {
        return $this->state(fn (array $attributes): array => [
            'supplier_vat_payer_status' => VatPayerStatus::NOT_VAT_PAYER,
            'tax_amount' => 0,
            'tax_rate' => 0,
        ]);
    }

    /**
     * Create an invoice with §7a registration status.
     */
    public function registeredParagraph7a(): Factory
    {
        return $this->state(fn (array $attributes): array => [
            'supplier_vat_payer_status' => VatPayerStatus::REGISTERED_PARAGRAPH_7A,
        ]);
    }

    /**
     * Create an invoice with §7 VAT payer status.
     */
    public function vatPayerParagraph7(): Factory
    {
        return $this->state(fn (array $attributes): array => [
            'supplier_vat_payer_status' => VatPayerStatus::VAT_PAYER_PARAGRAPH_7,
        ]);
    }

    /**
     * Create an invoice with reverse charge.
     */
    public function withReverseCharge(): Factory
    {
        return $this->state(fn (array $attributes): array => [
            'reverse_charge' => true,
            'tax_amount' => 0,
        ]);
    }

    /**
     * Create an invoice with special text.
     */
    public function withSpecialText(string $text = 'Špeciálna poznámka'): Factory
    {
        return $this->state(fn (array $attributes): array => [
            'special_text' => $text,
        ]);
    }
}
