<?php

declare(strict_types=1);

namespace App\Modules\Attendance\Factories;

use App\Models\User;
use App\Models\UserCompany;
use App\Modules\Attendance\Models\WorkSchedule;
use Illuminate\Database\Eloquent\Factories\Factory;

class WorkScheduleFactory extends Factory
{
    protected $model = WorkSchedule::class;

    public function definition(): array
    {
        return [
            'company_id' => UserCompany::factory(),
            'user_id' => User::factory(),
            'day_of_week' => $this->faker->numberBetween(1, 5), // Monday to Friday
            'start_time' => '08:00:00',
            'end_time' => '16:00:00',
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function weekend(): static
    {
        return $this->state(fn (array $attributes) => [
            'day_of_week' => $this->faker->randomElement([0, 6]), // Sunday or Saturday
        ]);
    }
}
