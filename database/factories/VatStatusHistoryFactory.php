<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\VatPayerStatus;
use App\Enums\VatPeriod;
use App\Models\UserCompany;
use App\Models\VatStatusHistory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<VatStatusHistory>
 */
class VatStatusHistoryFactory extends Factory
{
    protected $model = VatStatusHistory::class;

    public function definition(): array
    {
        $status = $this->faker->randomElement(VatPayerStatus::cases());

        return [
            'user_company_id' => UserCompany::factory(),
            'vat_status' => $status,
            'vat_period' => $status->requiresVatPeriod()
                ? $this->faker->randomElement(VatPeriod::cases())
                : null,
            'valid_from' => $this->faker->dateTimeBetween('-2 years', 'now'),
            'valid_to' => null,
            'notes' => $this->faker->optional()->sentence(),
        ];
    }

    /**
     * Mark the history record as closed (not current)
     */
    public function closed(): static
    {
        return $this->state(fn (array $attributes) => [
            'valid_to' => $this->faker->dateTimeBetween($attributes['valid_from'], 'now'),
        ]);
    }

    /**
     * Set as VAT payer with required period
     */
    public function vatPayer(): static
    {
        return $this->state(fn () => [
            'vat_status' => VatPayerStatus::VAT_PAYER,
            'vat_period' => $this->faker->randomElement(VatPeriod::cases()),
        ]);
    }

    /**
     * Set as non-VAT payer
     */
    public function notVatPayer(): static
    {
        return $this->state(fn () => [
            'vat_status' => VatPayerStatus::NOT_VAT_PAYER,
            'vat_period' => null,
        ]);
    }
}
