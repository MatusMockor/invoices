<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Vehicle>
 */
class VehicleFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Vehicle::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'type' => $this->faker->randomElement(['Car', 'Van', 'Truck', 'Motorcycle']),
            'license_plate' => strtoupper($this->faker->bothify('??###??')),
        ];
    }

    /**
     * Indicate that the vehicle belongs to the given company.
     */
    public function forCompany(Company $company): self
    {
        return $this->state(function (array $attributes) use ($company) {
            return [
                'company_id' => $company->id,
            ];
        });
    }
}
