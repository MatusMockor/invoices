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

        return [
            'user_id' => User::factory(),
            'invoice_number' => $invoiceNumber,
            'issue_date' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'due_date' => $this->faker->dateTimeBetween('now', '+30 days'),
            'delivery_date' => $this->faker->dateTimeBetween('-15 days', '+15 days'),
            'company_id' => Company::factory(),
            'supplier_company_id' => UserCompany::factory(),
            'total_amount' => $this->faker->randomFloat(2, 100, 10000),
            'currency' => 'EUR',
            'constant_symbol' => $this->faker->optional(0.7)->numerify('####'),
            'note' => $this->faker->optional(0.7)->sentence(),
            'status' => $this->faker->randomElement(['draft', 'sent', 'paid', 'cancelled']),
            'customer_name' => $this->faker->company(),
            'customer_ico' => $this->faker->numerify('########'),
            'customer_dic' => $this->faker->numerify('##########'),
            'customer_ic_dph' => $this->faker->optional(0.7)->numerify('SK##########'),
            'customer_street' => $this->faker->streetAddress(),
            'customer_city' => $this->faker->city(),
            'customer_postal_code' => $this->faker->postcode(),
            'customer_country' => $this->faker->randomElement(['Slovakia', 'Czech Republic', 'Austria', 'Hungary']),
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
}
