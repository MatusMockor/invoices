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
            'name' => $this->faker->company(),
            'ico' => $this->faker->unique()->numerify('########'),
            'street' => $this->faker->streetAddress(),
            'city' => $this->faker->city(),
            'postal_code' => $this->faker->postcode(),
            'country' => $this->faker->country(),
            'dic' => $this->faker->numerify('##########'),
            'ic_dph' => 'SK'.$this->faker->numerify('##########'),
            'iban' => $this->faker->iban('SK'),
            'swift' => $this->faker->swiftBicNumber(),
            'phone' => $this->faker->phoneNumber(),
            'email' => $this->faker->companyEmail(),
            'website' => $this->faker->url(),
            'company_type' => $this->faker->randomElement($companyTypes),
            'registration_number' => 'OR '.$this->faker->randomElement(['Bratislava I', 'Košice', 'Žilina', 'Prešov', 'Banská Bystrica']).', Oddiel: '.$this->faker->randomElement(['Sro', 'Sa']).', Vložka č. '.$this->faker->numerify('######'),
        ];
    }

    /**
     * Indicate that the business entity is from Slovakia.
     */
    public function slovak(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'country' => 'Slovakia',
                'postal_code' => $this->faker->numerify('#####'), // Slovak postal code format
            ];
        });
    }

    /**
     * Indicate the user that owns the company.
     */
    public function forUser($user): Factory
    {
        return $this->state(function (array $attributes) use ($user) {
            return [
                'user_id' => $user->id,
            ];
        });
    }
}
