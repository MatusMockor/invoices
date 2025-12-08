<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\VatPayerStatus;
use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Company>
 */
class CompanyFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Company::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $companyTypes = ['s.r.o.', 'a.s.', 'k.s.', 'v.o.s.', 'živnosť'];
        $icDph = fake()->optional(0.7)->passthrough('SK'.fake()->numerify('##########'));

        return [
            'name' => fake()->company(),
            'ico' => fake()->unique()->numerify('########'),
            'street' => fake()->streetAddress(),
            'city' => fake()->city(),
            'postal_code' => fake()->numerify('#####'),
            'country' => 'Slovakia',
            'dic' => fake()->numerify('##########'),
            'ic_dph' => $icDph,
            'vat_payer_status' => $icDph ? fake()->randomElement([
                VatPayerStatus::VAT_PAYER->value,
                VatPayerStatus::VAT_PAYER_PARAGRAPH_7->value,
                VatPayerStatus::REGISTERED_PARAGRAPH_7A->value,
            ]) : VatPayerStatus::NOT_VAT_PAYER->value,
            'iban' => fake()->iban('SK'),
            'swift' => fake()->swiftBicNumber(),
            'bank_name' => fake()->randomElement(['Slovenská sporiteľňa', 'VÚB banka', 'Tatra banka', 'ČSOB', 'Poštová banka', 'UniCredit Bank']),
            'phone' => fake()->phoneNumber(),
            'email' => fake()->companyEmail(),
            'website' => fake()->optional()->url(),
            'company_type' => fake()->randomElement($companyTypes),
            'registration_office' => fake()->randomElement(['Okresný súd Bratislava I', 'Okresný súd Košice', 'Okresný súd Žilina', 'Okresný súd Prešov', 'Okresný súd Banská Bystrica']),
            'registration_number' => 'Oddiel: '.fake()->randomElement(['Sro', 'Sa']).', Vložka č. '.fake()->numerify('######/B'),
        ];
    }

    /**
     * Indicate that the business entity is from Slovakia.
     */
    public function slovak(): Factory
    {
        return $this->state(fn (array $attributes): array => [
            'country' => 'Slovakia',
            'postal_code' => fake()->numerify('#####'),
        ]);
    }

    /**
     * Create a sole proprietorship (živnosť) that is not a VAT payer.
     * Živnosť cannot have IC DPH.
     */
    public function soleProprietorship(): Factory
    {
        return $this->state(fn (array $attributes): array => [
            'company_type' => 'živnosť',
            'ic_dph' => null,
            'vat_payer_status' => VatPayerStatus::NOT_VAT_PAYER,
            'registration_office' => fake()->randomElement([
                'Okresný úrad Bratislava',
                'Okresný úrad Košice',
                'Okresný úrad Žilina',
                'Okresný úrad Prešov',
                'Okresný úrad Banská Bystrica',
            ]),
            'registration_number' => fake()->numerify('######-####'),
        ]);
    }

    /**
     * Create an s.r.o. (limited liability company) that is registered for VAT under §7a.
     */
    public function sroRegisteredParagraph7a(): Factory
    {
        return $this->state(fn (array $attributes): array => [
            'company_type' => 's.r.o.',
            'ic_dph' => 'SK'.fake()->numerify('##########'),
            'vat_payer_status' => VatPayerStatus::REGISTERED_PARAGRAPH_7A,
            'registration_office' => fake()->randomElement([
                'Okresný súd Bratislava I',
                'Okresný súd Košice I',
                'Okresný súd Žilina',
                'Okresný súd Prešov',
                'Okresný súd Banská Bystrica',
            ]),
            'registration_number' => 'Oddiel: Sro, Vložka č. '.fake()->numerify('######/B'),
        ]);
    }

    /**
     * Create an s.r.o. (limited liability company) that is not a VAT payer.
     * Does not have IC DPH.
     */
    public function sroNotVatPayer(): Factory
    {
        return $this->state(fn (array $attributes): array => [
            'company_type' => 's.r.o.',
            'ic_dph' => null,
            'vat_payer_status' => VatPayerStatus::NOT_VAT_PAYER,
            'registration_office' => fake()->randomElement([
                'Okresný súd Bratislava I',
                'Okresný súd Košice I',
                'Okresný súd Žilina',
                'Okresný súd Prešov',
                'Okresný súd Banská Bystrica',
            ]),
            'registration_number' => 'Oddiel: Sro, Vložka č. '.fake()->numerify('######/B'),
        ]);
    }
}
