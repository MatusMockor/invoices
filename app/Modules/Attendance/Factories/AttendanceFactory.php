<?php

declare(strict_types=1);

namespace App\Modules\Attendance\Factories;

use App\Models\Company;
use App\Models\User;
use App\Modules\Attendance\Enums\AttendanceStatus;
use App\Modules\Attendance\Enums\WorkType;
use App\Modules\Attendance\Models\Attendance;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttendanceFactory extends Factory
{
    protected $model = Attendance::class;

    public function definition(): array
    {
        $checkIn = \Carbon\Carbon::instance($this->faker->dateTimeBetween('-30 days', 'now'));
        $hasCheckOut = $this->faker->boolean(80);
        $checkOut = $hasCheckOut ? \Carbon\Carbon::instance($this->faker->dateTimeBetween($checkIn, $checkIn->copy()->addHours(8))) : null;

        return [
            'company_id' => Company::factory(),
            'user_id' => User::factory(),
            'check_in' => $checkIn,
            'check_out' => $checkOut,
            'work_type' => $this->faker->randomElement(WorkType::cases()),
            'status' => $this->faker->randomElement(AttendanceStatus::cases()),
            'note' => $this->faker->optional(0.3)->sentence(),
            'check_in_ip' => $this->faker->ipv4(),
            'check_out_ip' => $checkOut ? $this->faker->ipv4() : null,
            'total_minutes' => $checkOut ? $checkIn->diffInMinutes($checkOut) : null,
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'check_out' => null,
            'total_minutes' => null,
        ]);
    }

    public function completed(): static
    {
        return $this->state(function (array $attributes) {
            $checkIn = \Carbon\Carbon::parse($attributes['check_in']);
            $checkOut = \Carbon\Carbon::instance($this->faker->dateTimeBetween($checkIn, $checkIn->copy()->addHours(8)));

            return [
                'check_out' => $checkOut,
                'total_minutes' => $checkIn->diffInMinutes($checkOut),
            ];
        });
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => AttendanceStatus::APPROVED,
            'approved_by' => User::factory(),
            'approved_at' => now(),
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => AttendanceStatus::PENDING,
        ]);
    }
}
