<?php

declare(strict_types=1);

namespace App\Modules\TaskManagement\Factories;

use App\Models\Company;
use App\Models\User;
use App\Modules\TaskManagement\Enums\TaskPriority;
use App\Modules\TaskManagement\Enums\TaskStatus;
use App\Modules\TaskManagement\Models\Task;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaskFactory extends Factory
{
    protected $model = Task::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'user_id' => User::factory(),
            'assigned_to' => fake()->boolean(70) ? User::factory() : null,
            'title' => fake()->sentence(),
            'description' => fake()->boolean(80) ? fake()->paragraph() : null,
            'status' => fake()->randomElement(TaskStatus::cases())->value,
            'priority' => fake()->randomElement(TaskPriority::cases())->value,
            'due_date' => fake()->boolean(70) ? fake()->dateTimeBetween('now', '+30 days') : null,
            'completed_at' => null,
            'metadata' => fake()->boolean(30) ? ['custom_field' => fake()->word()] : null,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TaskStatus::PENDING->value,
            'completed_at' => null,
        ]);
    }

    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TaskStatus::IN_PROGRESS->value,
            'completed_at' => null,
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TaskStatus::COMPLETED->value,
            'completed_at' => fake()->dateTimeBetween('-30 days', 'now'),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TaskStatus::CANCELLED->value,
        ]);
    }

    public function urgent(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => TaskPriority::URGENT->value,
        ]);
    }

    public function high(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => TaskPriority::HIGH->value,
        ]);
    }

    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TaskStatus::PENDING->value,
            'due_date' => fake()->dateTimeBetween('-30 days', '-1 day'),
            'completed_at' => null,
        ]);
    }
}
