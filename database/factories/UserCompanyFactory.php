<?php

declare(strict_types=1);

namespace Database\Factories;

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
        $companyTypes = ['živnosť', 's.r.o.'];

        return [
            'user_id' => \App\Models\User::factory(),
            'name' => fake()->company(),
            'ico' => fake()->unique()->numerify('########'),
            'street' => fake()->streetAddress(),
            'city' => fake()->city(),
            'postal_code' => fake()->numerify('#####'),
            'country' => 'Slovakia',
            'dic' => fake()->numerify('##########'),
            'ic_dph' => 'SK'.fake()->numerify('##########'),
            'iban' => fake()->iban('SK'),
            'swift' => fake()->swiftBicNumber(),
            'phone' => fake()->phoneNumber(),
            'email' => fake()->companyEmail(),
            'website' => fake()->url(),
            'company_type' => fake()->randomElement($companyTypes),
            'registration_number' => 'OR '.fake()->randomElement(['Bratislava I', 'Košice', 'Žilina', 'Prešov', 'Banská Bystrica']).', Oddiel: '.fake()->randomElement(['Sro', 'Sa']).', Vložka č. '.fake()->numerify('######'),
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
        return $this->state(function (array $attributes) use ($user): array {
            return [
                'user_id' => $user->id,
            ];
        });
    }
}
