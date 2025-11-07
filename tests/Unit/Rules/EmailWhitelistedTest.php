<?php

declare(strict_types=1);

namespace Tests\Unit\Rules;

use App\Models\EmailWhitelist;
use App\Rules\EmailWhitelisted;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class EmailWhitelistedTest extends TestCase
{
    use RefreshDatabase;

    public function test_passes_when_whitelist_is_disabled(): void
    {
        config(['registration.email_whitelist_enabled' => false]);

        $rule = app(EmailWhitelisted::class);
        $email = fake()->safeEmail();

        $failed = false;
        $rule->validate('email', $email, static function () use (&$failed): void {
            $failed = true;
        });

        $this->assertFalse($failed);
    }

    public function test_passes_when_whitelist_is_enabled_and_email_exists(): void
    {
        config(['registration.email_whitelist_enabled' => true]);

        $email = fake()->safeEmail();
        EmailWhitelist::factory()->create(['email' => $email]);

        $rule = app(EmailWhitelisted::class);

        $failed = false;
        $rule->validate('email', $email, static function () use (&$failed): void {
            $failed = true;
        });

        $this->assertFalse($failed);
    }

    public function test_fails_when_whitelist_is_enabled_and_email_does_not_exist(): void
    {
        config(['registration.email_whitelist_enabled' => true]);

        $email = fake()->safeEmail();

        $rule = app(EmailWhitelisted::class);

        $failed = false;
        $errorMessage = '';
        $rule->validate('email', $email, static function (string $message) use (&$failed, &$errorMessage): void {
            $failed = true;
            $errorMessage = $message;
        });

        $this->assertTrue($failed);
        $this->assertEquals('Tento email nie je autorizovaný pre registráciu.', $errorMessage);
    }

    public function test_passes_when_whitelist_is_disabled_even_if_email_not_in_list(): void
    {
        config(['registration.email_whitelist_enabled' => false]);

        $email = fake()->safeEmail();

        $rule = app(EmailWhitelisted::class);

        $failed = false;
        $rule->validate('email', $email, static function () use (&$failed): void {
            $failed = true;
        });

        $this->assertFalse($failed);
    }
}
