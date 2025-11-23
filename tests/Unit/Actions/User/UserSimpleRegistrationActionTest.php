<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\User;

use App\Actions\User\UserSimpleRegistrationAction;
use App\DTOs\User\SimpleUserRegistrationDTO;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

final class UserSimpleRegistrationActionTest extends TestCase
{
    use RefreshDatabase;

    private UserSimpleRegistrationAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(UserSimpleRegistrationAction::class);
    }

    public function test_registers_user_successfully(): void
    {
        $dto = new SimpleUserRegistrationDTO(
            firstName: fake()->firstName(),
            lastName: fake()->lastName(),
            email: fake()->unique()->safeEmail(),
            password: 'password123',
        );

        $user = $this->action->handle($dto);

        $this->assertInstanceOf(User::class, $user);
        $this->assertDatabaseHas(User::class, [
            'email' => $dto->email,
            'first_name' => $dto->firstName,
            'last_name' => $dto->lastName,
        ]);
    }

    public function test_hashes_password_before_storing(): void
    {
        $dto = new SimpleUserRegistrationDTO(
            firstName: fake()->firstName(),
            lastName: fake()->lastName(),
            email: fake()->unique()->safeEmail(),
            password: 'plain-text-password',
        );

        $user = $this->action->handle($dto);

        $this->assertNotEquals('plain-text-password', $user->password);
        $this->assertTrue(Hash::check('plain-text-password', $user->password));
    }

    public function test_wraps_registration_in_transaction(): void
    {
        DB::shouldReceive('transaction')
            ->once()
            ->andReturnUsing(function ($callback) {
                return $callback();
            });

        $dto = new SimpleUserRegistrationDTO(
            firstName: fake()->firstName(),
            lastName: fake()->lastName(),
            email: fake()->unique()->safeEmail(),
            password: 'password123',
        );

        app(UserSimpleRegistrationAction::class)->handle($dto);
    }

    public function test_returns_created_user_instance(): void
    {
        $email = fake()->unique()->safeEmail();

        $dto = new SimpleUserRegistrationDTO(
            firstName: 'John',
            lastName: 'Doe',
            email: $email,
            password: 'password123',
        );

        $user = $this->action->handle($dto);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('John', $user->first_name);
        $this->assertEquals('Doe', $user->last_name);
        $this->assertEquals($email, $user->email);
        $this->assertNotNull($user->id);
    }
}
