<?php

declare(strict_types=1);

namespace Database\Factories;

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
        return [
            'name' => $this->faker->company(),
            'ico' => $this->faker->unique()->numerify('########'),
            'street' => $this->faker->streetAddress(),
            'city' => $this->faker->city(),
            'postal_code' => $this->faker->postcode(),
            'country' => $this->faker->country(),
            'dic' => $this->faker->numerify('##########'),
            'ic_dph' => 'SK'.$this->faker->numerify('##########'),
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
