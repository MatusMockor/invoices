<?php

namespace Database\Factories;

use App\Models\Trip;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Trip>
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
        $startOdometer = $this->faker->numberBetween(10000, 100000);
        $endOdometer = $startOdometer + $this->faker->numberBetween(10, 500);
        $distance = $endOdometer - $startOdometer;

        return [
            'vehicle_id' => Vehicle::factory(),
            'date' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'start_location' => $this->faker->city,
            'end_location' => $this->faker->city,
            'purpose' => $this->faker->sentence(4),
            'start_odometer' => $startOdometer,
            'end_odometer' => $endOdometer,
            'distance' => $distance,
            'driver_name' => $this->faker->name,
            'fuel_amount' => $this->faker->optional(0.7)->randomFloat(2, 10, 100),
            'fuel_cost' => $this->faker->optional(0.7)->randomFloat(2, 20, 200),
            'fuel_receipt_number' => $this->faker->optional(0.5)->bothify('REC-####-????'),
        ];
    }

    /**
     * Indicate that the trip belongs to the given vehicle.
     */
    public function forVehicle(Vehicle $vehicle): self
    {
        return $this->state(function (array $attributes) use ($vehicle) {
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
        return $this->state(function (array $attributes) {
            return [
                'fuel_amount' => $this->faker->randomFloat(2, 10, 100),
                'fuel_cost' => $this->faker->randomFloat(2, 20, 200),
                'fuel_receipt_number' => $this->faker->bothify('REC-####-????'),
            ];
        });
    }

    /**
     * Indicate that the trip has no fuel information.
     */
    public function withoutFuel(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'fuel_amount' => null,
                'fuel_cost' => null,
                'fuel_receipt_number' => null,
            ];
        });
    }
}
