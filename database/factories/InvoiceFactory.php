<?php

declare(strict_types=1);

namespace Database\Factories;

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

        // Create company first so we can copy its data
        $company = Company::factory()->create();

        return [
            'user_id' => User::factory(),
            'invoice_number' => $invoiceNumber,
            'issue_date' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'due_date' => $this->faker->dateTimeBetween('now', '+30 days'),
            'delivery_date' => $this->faker->dateTimeBetween('-15 days', '+15 days'),
            'company_id' => $company->id,
            'supplier_company_id' => UserCompany::factory(),
            'company_ico' => $company->ico,
            'company_dic' => $company->dic,
            'company_ic_dph' => $company->ic_dph,
            'company_name' => $company->name,
            'company_address' => $company->street,
            'company_city' => $company->city,
            'company_zip' => $company->postal_code,
            'company_country' => $company->country,
            'total_amount' => $this->faker->randomFloat(2, 100, 10000),
            'currency' => 'EUR',
            'constant_symbol' => $this->faker->optional(0.7)->numerify('####'),
            'note' => $this->faker->optional(0.7)->sentence(),
            'status' => $this->faker->randomElement(['draft', 'sent', 'paid', 'cancelled']),
        ];
    }

    /**
     * Indicate that the invoice is in draft status.
     */
    public function draft(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'draft',
            ];
        });
    }

    /**
     * Indicate that the invoice has been sent.
     */
    public function sent(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'sent',
            ];
        });
    }

    /**
     * Indicate that the invoice has been paid.
     */
    public function paid(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'paid',
            ];
        });
    }

    /**
     * Indicate that the invoice has been cancelled.
     */
    public function cancelled(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'cancelled',
            ];
        });
    }

    /**
     * Indicate that the invoice uses custom company data instead of a company_id.
     */
    public function withCustomCompany(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'company_id' => null,
                'company_ico' => $this->faker->numerify('########'),
                'company_dic' => $this->faker->numerify('##########'),
                'company_ic_dph' => $this->faker->optional(0.8)->regexify('SK[0-9]{10}'),
                'company_name' => $this->faker->company(),
                'company_address' => $this->faker->streetAddress(),
                'company_city' => $this->faker->city(),
                'company_zip' => $this->faker->postcode(),
                'company_country' => $this->faker->country(),
            ];
        });
    }
}
