<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\CompanyType;
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
            'bank_name' => fake()->randomElement(['Slovenska sporitelna', 'VUB banka', 'Tatra banka', 'CSOB', 'Postova banka', 'UniCredit Bank']),
            'phone' => fake()->phoneNumber(),
            'email' => fake()->companyEmail(),
            'website' => fake()->optional()->url(),
            'type' => fake()->randomElement(CompanyType::cases()),
            'registration_office' => fake()->randomElement(['Okresny sud Bratislava I', 'Okresny sud Kosice', 'Okresny sud Zilina', 'Okresny sud Presov', 'Okresny sud Banska Bystrica']),
            'registration_number' => 'Oddiel: '.fake()->randomElement(['Sro', 'Sa']).', Vlozka c. '.fake()->numerify('######/B'),
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
     * Create a sole proprietorship (zivnost) that is not a VAT payer.
     * Zivnost cannot have IC DPH.
     */
    public function soleProprietorship(): Factory
    {
        return $this->state(fn (array $attributes): array => [
            'type' => CompanyType::SOLE_PROPRIETOR,
            'ic_dph' => null,
            'vat_payer_status' => VatPayerStatus::NOT_VAT_PAYER,
            'registration_office' => fake()->randomElement([
                'Okresny urad Bratislava',
                'Okresny urad Kosice',
                'Okresny urad Zilina',
                'Okresny urad Presov',
                'Okresny urad Banska Bystrica',
            ]),
            'registration_number' => fake()->numerify('######-####'),
        ]);
    }

    /**
     * Create an s.r.o. (limited liability company) that is registered for VAT under 7a.
     */
    public function sroRegisteredParagraph7a(): Factory
    {
        return $this->state(fn (array $attributes): array => [
            'type' => CompanyType::LIMITED_LIABILITY_COMPANY,
            'ic_dph' => 'SK'.fake()->numerify('##########'),
            'vat_payer_status' => VatPayerStatus::REGISTERED_PARAGRAPH_7A,
            'registration_office' => fake()->randomElement([
                'Okresny sud Bratislava I',
                'Okresny sud Kosice I',
                'Okresny sud Zilina',
                'Okresny sud Presov',
                'Okresny sud Banska Bystrica',
            ]),
            'registration_number' => 'Oddiel: Sro, Vlozka c. '.fake()->numerify('######/B'),
        ]);
    }

    /**
     * Create an s.r.o. (limited liability company) that is not a VAT payer.
     * Does not have IC DPH.
     */
    public function sroNotVatPayer(): Factory
    {
        return $this->state(fn (array $attributes): array => [
            'type' => CompanyType::LIMITED_LIABILITY_COMPANY,
            'ic_dph' => null,
            'vat_payer_status' => VatPayerStatus::NOT_VAT_PAYER,
            'registration_office' => fake()->randomElement([
                'Okresny sud Bratislava I',
                'Okresny sud Kosice I',
                'Okresny sud Zilina',
                'Okresny sud Presov',
                'Okresny sud Banska Bystrica',
            ]),
            'registration_number' => 'Oddiel: Sro, Vlozka c. '.fake()->numerify('######/B'),
        ]);
    }
}
