<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\EmailWhitelist;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EmailWhitelist>
 */
final class EmailWhitelistFactory extends Factory
{
    protected $model = EmailWhitelist::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'email' => fake()->unique()->safeEmail(),
        ];
    }
}
