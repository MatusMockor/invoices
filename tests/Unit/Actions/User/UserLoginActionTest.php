<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\User;

use App\Actions\User\UserLoginAction;
use App\DTOs\User\LoginDTO;
use App\DTOs\User\LoginResultDTO;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

final class UserLoginActionTest extends TestCase
{
    use RefreshDatabase;

    private UserLoginAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(UserLoginAction::class);
    }

    public function test_authenticates_user_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $dto = new LoginDTO(
            email: 'test@example.com',
            password: 'password123',
        );

        $result = $this->action->handle($dto);

        $this->assertInstanceOf(LoginResultDTO::class, $result);
        $this->assertEquals($user->id, $result->user->id);
        $this->assertNotEmpty($result->token);
        $this->assertIsString($result->token);
    }

    public function test_throws_exception_when_user_not_found(): void
    {
        $dto = new LoginDTO(
            email: 'nonexistent@example.com',
            password: 'password123',
        );

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('The provided credentials are incorrect.');

        $this->action->handle($dto);
    }

    public function test_throws_exception_when_password_is_incorrect(): void
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('correct-password'),
        ]);

        $dto = new LoginDTO(
            email: 'test@example.com',
            password: 'wrong-password',
        );

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('The provided credentials are incorrect.');

        $this->action->handle($dto);
    }

    public function test_creates_new_token_for_user(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $initialTokenCount = $user->tokens()->count();

        $dto = new LoginDTO(
            email: 'test@example.com',
            password: 'password123',
        );

        $this->action->handle($dto);

        $this->assertEquals($initialTokenCount + 1, $user->tokens()->count());
    }
}
