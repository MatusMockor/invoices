<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Modules\VehicleLogbook\Models\Trip;
use App\Modules\VehicleLogbook\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Modules\VehicleLogbook\Models\Trip>
 */
class TripFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Trip::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startOdometer = fake()->numberBetween(10000, 100000);
        $endOdometer = $startOdometer + fake()->numberBetween(10, 500);
        $distance = $endOdometer - $startOdometer;

        return [
            'vehicle_id' => Vehicle::factory(),
            'date' => fake()->dateTimeBetween('-1 year', 'now'),
            'start_location' => fake()->city(),
            'end_location' => fake()->city(),
            'purpose' => fake()->sentence(4),
            'start_odometer' => $startOdometer,
            'end_odometer' => $endOdometer,
            'distance' => $distance,
            'driver_name' => fake()->name(),
            'fuel_amount' => fake()->optional(0.7)->randomFloat(2, 10, 100),
            'fuel_cost' => fake()->optional(0.7)->randomFloat(2, 20, 200),
            'fuel_receipt_number' => fake()->optional(0.5)->bothify('REC-####-????'),
        ];
    }

    /**
     * Indicate that the trip belongs to the given vehicle.
     */
    public function forVehicle(Vehicle $vehicle): self
    {
        return $this->state(function (array $attributes) use ($vehicle): array {
            return [
                'vehicle_id' => $vehicle->id,
            ];
        });
    }

    /**
     * Indicate that the trip has fuel information.
     */
    public function withFuel(): self
    {
        return $this->state(fn (array $attributes): array => [
            'fuel_amount' => fake()->randomFloat(2, 10, 100),
            'fuel_cost' => fake()->randomFloat(2, 20, 200),
            'fuel_receipt_number' => fake()->bothify('REC-####-????'),
        ]);
    }

    /**
     * Indicate that the trip has no fuel information.
     */
    public function withoutFuel(): self
    {
        return $this->state(fn (array $attributes): array => [
            'fuel_amount' => null,
            'fuel_cost' => null,
            'fuel_receipt_number' => null,
        ]);
    }
}
