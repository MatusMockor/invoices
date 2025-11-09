<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Company;
use App\Modules\VehicleLogbook\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Modules\VehicleLogbook\Models\Vehicle>
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
            'type' => fake()->randomElement(['Car', 'Van', 'Truck', 'Motorcycle']),
            'license_plate' => strtoupper(fake()->bothify('??###??')),
        ];
    }

    /**
     * Indicate that the vehicle belongs to the given company.
     */
    public function forCompany(Company $company): self
    {
        return $this->state(function (array $attributes) use ($company): array {
            return [
                'company_id' => $company->id,
            ];
        });
    }
}
