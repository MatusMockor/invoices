<?php

declare(strict_types=1);

namespace App\Modules\TaskManagement\Factories;

use App\Models\User;
use App\Modules\TaskManagement\Models\FollowUp;
use App\Modules\TaskManagement\Models\Task;
use Illuminate\Database\Eloquent\Factories\Factory;

class FollowUpFactory extends Factory
{
    protected $model = FollowUp::class;

    public function definition(): array
    {
        return [
            'task_id' => Task::factory(),
            'user_id' => User::factory(),
            'note' => fake()->paragraph(),
            'follow_up_date' => fake()->boolean(70) ? fake()->dateTimeBetween('now', '+14 days') : null,
            'is_completed' => fake()->boolean(30),
            'completed_at' => null,
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_completed' => true,
            'completed_at' => fake()->dateTimeBetween('-30 days', 'now'),
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_completed' => false,
            'completed_at' => null,
        ]);
    }

    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_completed' => false,
            'follow_up_date' => fake()->dateTimeBetween('-14 days', '-1 day'),
            'completed_at' => null,
        ]);
    }
}
