<?php

declare(strict_types=1);

namespace App\Modules\CRM\Factories;

use App\Models\Company;
use App\Models\User;
use App\Modules\CRM\Enums\ContactStatus;
use App\Modules\CRM\Models\CrmContact;
use Illuminate\Database\Eloquent\Factories\Factory;

class CrmContactFactory extends Factory
{
    protected $model = CrmContact::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'user_id' => User::factory(),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'primary_email' => fake()->unique()->safeEmail(),
            'primary_phone' => fake()->phoneNumber(),
            'job_title' => fake()->jobTitle(),
            'notes' => fake()->optional(0.3)->paragraph(),
            'metadata' => fake()->optional(0.2)->randomElements([
                'source' => fake()->randomElement(['website', 'referral', 'cold_call', 'event']),
                'priority' => fake()->randomElement(['low', 'medium', 'high']),
                'lead_score' => fake()->numberBetween(1, 100),
            ]),
            'is_active' => fake()->randomElement(ContactStatus::values()) === ContactStatus::ACTIVE->value,
            'last_contacted_at' => fake()->optional(0.4)->dateTimeBetween('-6 months', 'now'),
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function recentlyContacted(): static
    {
        return $this->state(fn (array $attributes) => [
            'last_contacted_at' => fake()->dateTimeBetween('-30 days', 'now'),
        ]);
    }

    public function withCompany(Company $company): static
    {
        return $this->state(fn (array $attributes) => [
            'company_id' => $company->id,
        ]);
    }

    public function withUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }
}
