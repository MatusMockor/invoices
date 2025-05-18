<?php

namespace Database\Factories;

use App\Models\BusinessEntity;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BusinessEntity>
 */
class BusinessEntityFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = BusinessEntity::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $companyTypes = ['živnosť', 's.r.o.'];

        return [
            'name' => $this->faker->company(),
            'ico' => $this->faker->unique()->numerify('########'),
            'street' => $this->faker->streetAddress(),
            'city' => $this->faker->city(),
            'postal_code' => $this->faker->postcode(),
            'country' => $this->faker->country(),
            'dic' => $this->faker->numerify('##########'),
            'ic_dph' => 'SK'.$this->faker->numerify('##########'),
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
}
