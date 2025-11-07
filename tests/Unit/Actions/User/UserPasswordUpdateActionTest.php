<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\User;

use App\Actions\User\UserPasswordUpdateAction;
use App\DTOs\User\UserPasswordUpdateDTO;
use App\Models\User;
use App\Repositories\Contracts\UserRepository as UserRepositoryContract;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Mockery;
use Tests\TestCase;

final class UserPasswordUpdateActionTest extends TestCase
{
    use RefreshDatabase;

    private UserPasswordUpdateAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(UserPasswordUpdateAction::class);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_updates_user_password_with_hashed_value(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('old-password'),
        ]);

        $newPassword = fake()->password(8);
        $dto = new UserPasswordUpdateDTO(
            password: $newPassword
        );

        $result = $this->action->handle($user, $dto);

        $this->assertTrue($result);

        // Verify password was updated and hashed
        $user->refresh();
        $this->assertTrue(Hash::check($newPassword, $user->password));
        $this->assertNotEquals($newPassword, $user->password); // Should be hashed, not plain text
    }

    public function test_uses_transaction_wrapper(): void
    {
        DB::shouldReceive('transaction')
            ->once()
            ->andReturnUsing(static function ($callback) {
                return $callback();
            });

        $user = User::factory()->create();
        $dto = new UserPasswordUpdateDTO(
            password: fake()->password(8)
        );

        $result = app(UserPasswordUpdateAction::class)->handle($user, $dto);

        $this->assertTrue($result);
    }

    public function test_returns_true_on_success(): void
    {
        $user = User::factory()->create();
        $dto = new UserPasswordUpdateDTO(
            password: fake()->password(8)
        );

        $result = $this->action->handle($user, $dto);

        $this->assertTrue($result);
    }

    public function test_accepts_user_password_update_dto(): void
    {
        $user = User::factory()->create();
        $newPassword = fake()->password(8);

        $dto = new UserPasswordUpdateDTO(
            password: $newPassword
        );

        $this->assertInstanceOf(UserPasswordUpdateDTO::class, $dto);

        $result = $this->action->handle($user, $dto);

        $this->assertTrue($result);
        $user->refresh();
        $this->assertTrue(Hash::check($newPassword, $user->password));
    }

    public function test_calls_repository_update_method(): void
    {
        $user = User::factory()->create();
        $newPassword = fake()->password(8);
        $dto = new UserPasswordUpdateDTO(password: $newPassword);

        $mockRepository = Mockery::mock(UserRepositoryContract::class);
        $mockRepository->shouldReceive('update')
            ->once()
            ->with($user, Mockery::on(static function (array $data) use ($newPassword): bool {
                // Verify the password is hashed
                return isset($data['password']) && Hash::check($newPassword, $data['password']);
            }))
            ->andReturn(true);

        $action = new UserPasswordUpdateAction($mockRepository);

        $result = $action->handle($user, $dto);

        $this->assertTrue($result);
    }

    public function test_password_is_hashed_with_hash_make(): void
    {
        $user = User::factory()->create();
        $plainPassword = fake()->password(8);
        $dto = new UserPasswordUpdateDTO(password: $plainPassword);

        $this->action->handle($user, $dto);

        $user->refresh();

        // Verify the stored password is not the plain text
        $this->assertNotEquals($plainPassword, $user->password);

        // Verify it starts with the bcrypt identifier
        $this->assertStringStartsWith('$2y$', $user->password);

        // Verify it can be checked with Hash::check
        $this->assertTrue(Hash::check($plainPassword, $user->password));
    }
}
