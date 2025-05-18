<?php

namespace Database\Factories;

use App\Models\BusinessEntity;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Invoice>
 */
class InvoiceFactory extends Factory
{
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
        return [
            'user_id' => User::factory(),
            'invoice_number' => 'INV-'.date('Y').'-'.$this->faker->unique()->randomNumber(3),
            'issue_date' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'due_date' => $this->faker->dateTimeBetween('now', '+30 days'),
            'delivery_date' => $this->faker->dateTimeBetween('-15 days', '+15 days'),
            'business_entity_id' => BusinessEntity::factory(),
            'supplier_company_id' => Company::factory(),
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
}
