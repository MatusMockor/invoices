<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\VatPayerStatus;
use App\Models\UserCompany;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UserCompany>
 */
class UserCompanyFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = UserCompany::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $companyTypes = ['zivnost', 's.r.o.'];
        $icDph = fake()->optional(0.7)->passthrough('SK'.fake()->numerify('##########'));

        return [
            'user_id' => \App\Models\User::factory(),
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
            'phone' => fake()->phoneNumber(),
            'email' => fake()->companyEmail(),
            'website' => fake()->url(),
            'company_type' => fake()->randomElement($companyTypes),
            'registration_number' => 'Oddiel: '.fake()->randomElement(['Sro', 'Sa']).', Vlozka c. '.fake()->numerify('######/B'),
            'registration_office' => fake()->randomElement([
                'Okresny sud Bratislava I',
                'Okresny sud Kosice I',
                'Okresny sud Zilina',
                'Okresny sud Presov',
                'Okresny sud Banska Bystrica',
                'Okresny sud Trencin',
                'Okresny sud Nitra',
                'Okresny sud Trnava',
            ]),
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
     * Indicate the user that owns the company.
     */
    public function forUser(\App\Models\User $user): Factory
    {
        return $this->state(fn (array $attributes): array => [
            'user_id' => $user->id,
        ]);
    }

    /**
     * Create a sole proprietorship (zivnost) with appropriate registry data.
     */
    public function soleProprietorship(): Factory
    {
        return $this->state(fn (array $attributes): array => [
            'company_type' => 'zivnost',
            'ic_dph' => null,
            'vat_payer_status' => VatPayerStatus::NOT_VAT_PAYER->value,
            'registration_office' => fake()->randomElement([
                'Okresny urad Bratislava, odbor zivnostenskeho podnikania',
                'Okresny urad Kosice, odbor zivnostenskeho podnikania',
                'Okresny urad Zilina, odbor zivnostenskeho podnikania',
            ]),
            'registration_number' => 'Cislo zivnostenskeho registra: '.fake()->numerify('###-#####'),
        ]);
    }

    /**
     * Create an s.r.o. with appropriate registry data.
     */
    public function sro(): Factory
    {
        return $this->state(fn (array $attributes): array => [
            'company_type' => 's.r.o.',
            'registration_office' => fake()->randomElement([
                'Okresny sud Bratislava I',
                'Okresny sud Kosice I',
                'Okresny sud Zilina',
            ]),
            'registration_number' => 'Oddiel: Sro, Vlozka c. '.fake()->numerify('######/B'),
        ]);
    }

    /**
     * Create a company with VAT payer status.
     */
    public function vatPayer(): Factory
    {
        return $this->state(fn (array $attributes): array => [
            'vat_payer_status' => VatPayerStatus::VAT_PAYER,
            'ic_dph' => 'SK'.fake()->numerify('##########'),
        ]);
    }

    /**
     * Create a company with non-VAT payer status.
     */
    public function notVatPayer(): Factory
    {
        return $this->state(fn (array $attributes): array => [
            'vat_payer_status' => VatPayerStatus::NOT_VAT_PAYER,
            'ic_dph' => null,
        ]);
    }

    /**
     * Create a company with §7a registration status.
     */
    public function registeredParagraph7a(): Factory
    {
        return $this->state(fn (array $attributes): array => [
            'vat_payer_status' => VatPayerStatus::REGISTERED_PARAGRAPH_7A,
            'ic_dph' => 'SK'.fake()->numerify('##########'),
        ]);
    }

    /**
     * Create a company with §7 VAT payer status.
     */
    public function vatPayerParagraph7(): Factory
    {
        return $this->state(fn (array $attributes): array => [
            'vat_payer_status' => VatPayerStatus::VAT_PAYER_PARAGRAPH_7,
            'ic_dph' => 'SK'.fake()->numerify('##########'),
        ]);
    }
}
