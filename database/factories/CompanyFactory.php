<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\User;
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
        $companyTypes = ['živnosť', 's.r.o.'];

        return [
            'user_id' => User::factory(),
            'name' => $this->faker->company(),
            'street' => $this->faker->streetAddress(),
            'city' => $this->faker->city(),
            'postal_code' => $this->faker->postcode(),
            'country' => $this->faker->country(),
            'ico' => $this->faker->numerify('########'), // 8-digit company ID commonly used in Slovakia
            'dic' => $this->faker->numerify('##########'), // 10-digit tax ID
            'ic_dph' => 'SK'.$this->faker->numerify('##########'), // VAT ID with SK prefix
            'iban' => 'SK'.$this->faker->numerify('##############'), // Slovak IBAN format with numbers only
            'swift' => $this->faker->regexify('[A-Z]{6}[A-Z0-9]{2}[A-Z0-9]{3}'), // SWIFT/BIC code format
            'phone' => $this->faker->phoneNumber(),
            'email' => $this->faker->companyEmail(),
            'website' => $this->faker->url(),
            'company_type' => $this->faker->randomElement($companyTypes),
            'registration_number' => 'OR '.$this->faker->randomElement(['Bratislava I', 'Košice', 'Žilina', 'Prešov', 'Banská Bystrica']).', Oddiel: '.$this->faker->randomElement(['Sro', 'Sa']).', Vložka č. '.$this->faker->numerify('######'),
        ];
    }

    /**
     * Indicate that the model's company belongs to the given user.
     */
    public function forUser(User|int $user): Factory
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => is_int($user) ? $user : $user->id,
        ]);
    }

    /**
     * Indicate that the company is from Slovakia (with Slovak specific data).
     */
    public function slovak(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'country' => 'Slovakia',
                'postal_code' => $this->faker->numerify('#####'), // Slovak postal code format
                'iban' => $this->faker->iban('SK'), // Slovak IBAN format (24 chars total)
                'swift' => $this->faker->swiftBicNumber('[A-Z0-9]{4}'), // Example of a Slovak bank SWIFT
                'phone' => $this->faker->phoneNumber, // Slovak phone number format
            ];
        });
    }
}
