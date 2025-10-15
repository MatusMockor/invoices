<?php

declare(strict_types=1);

namespace App\Modules\Attendance\Factories;

use App\Modules\Attendance\Enums\BreakType;
use App\Modules\Attendance\Models\Attendance;
use App\Modules\Attendance\Models\AttendanceBreak;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttendanceBreakFactory extends Factory
{
    protected $model = AttendanceBreak::class;

    public function definition(): array
    {
        $breakStart = $this->faker->dateTimeBetween('-8 hours', 'now');
        $breakEnd = $this->faker->optional(0.9)->dateTimeBetween($breakStart, '+60 minutes');

        return [
            'attendance_id' => Attendance::factory(),
            'break_start' => $breakStart,
            'break_end' => $breakEnd,
            'break_type' => $this->faker->randomElement(BreakType::cases()),
            'note' => $this->faker->optional(0.2)->sentence(),
            'duration_minutes' => $breakEnd ? $breakStart->diffInMinutes($breakEnd) : null,
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'break_end' => null,
            'duration_minutes' => null,
        ]);
    }

    public function completed(): static
    {
        return $this->state(function (array $attributes) {
            $breakStart = $attributes['break_start'];
            $breakEnd = $this->faker->dateTimeBetween($breakStart, '+60 minutes');

            return [
                'break_end' => $breakEnd,
                'duration_minutes' => $breakStart->diffInMinutes($breakEnd),
            ];
        });
    }
}
