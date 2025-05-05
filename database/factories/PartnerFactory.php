<?php

namespace Database\Factories;

use App\Models\Partner;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Partner>
 */
class PartnerFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Partner::class;

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
     * Indicate that the partner is from Slovakia.
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
