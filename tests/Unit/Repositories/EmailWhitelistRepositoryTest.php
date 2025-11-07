<?php

declare(strict_types=1);

namespace Tests\Unit\Repositories;

use App\Models\EmailWhitelist;
use App\Repositories\Contracts\EmailWhitelistRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class EmailWhitelistRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private EmailWhitelistRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = app(EmailWhitelistRepository::class);
    }

    public function test_exists_returns_true_when_email_exists(): void
    {
        $email = fake()->email();
        EmailWhitelist::factory()->create(['email' => $email]);

        $this->assertTrue($this->repository->exists($email));
    }

    public function test_exists_returns_false_when_email_does_not_exist(): void
    {
        $this->assertFalse($this->repository->exists(fake()->email()));
    }
}
