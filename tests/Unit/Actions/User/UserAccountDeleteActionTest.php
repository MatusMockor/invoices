<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\User;

use App\Actions\User\UserAccountDeleteAction;
use App\Models\User;
use App\Repositories\Contracts\UserRepository as UserRepositoryContract;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Mockery;
use Tests\TestCase;

final class UserAccountDeleteActionTest extends TestCase
{
    use RefreshDatabase;

    private UserAccountDeleteAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(UserAccountDeleteAction::class);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_soft_deletes_user(): void
    {
        $user = User::factory()->create();
        $userId = $user->id;

        $result = $this->action->handle($user);

        $this->assertTrue($result);

        // User should be soft deleted
        $this->assertSoftDeleted(User::class, [
            'id' => $userId,
        ]);

        // User should still exist in database with deleted_at timestamp
        $deletedUser = User::withTrashed()->find($userId);
        $this->assertNotNull($deletedUser);
        $this->assertNotNull($deletedUser->deleted_at);
    }

    public function test_revokes_all_user_tokens(): void
    {
        $user = User::factory()->create();

        // Create multiple tokens
        $user->createToken('token1');
        $user->createToken('token2');
        $user->createToken('token3');

        $this->assertCount(3, $user->tokens()->where('revoked', false)->get());

        $this->action->handle($user);

        // All tokens should be revoked (not deleted, but revoked in Passport)
        $user->refresh();
        $this->assertCount(0, $user->tokens()->where('revoked', false)->get());
    }

    public function test_uses_transaction_wrapper(): void
    {
        DB::shouldReceive('transaction')
            ->once()
            ->andReturnUsing(static function ($callback) {
                return $callback();
            });

        $user = User::factory()->create();

        $result = app(UserAccountDeleteAction::class)->handle($user);

        $this->assertTrue($result);
    }

    public function test_returns_true_on_success(): void
    {
        $user = User::factory()->create();

        $result = $this->action->handle($user);

        $this->assertTrue($result);
    }

    public function test_doesnt_hard_delete_user(): void
    {
        $user = User::factory()->create();
        $userId = $user->id;

        $this->action->handle($user);

        // User should still exist in database (not hard deleted)
        $deletedUser = User::withTrashed()->find($userId);
        $this->assertNotNull($deletedUser);

        // But should not be found without withTrashed()
        $activeUser = User::find($userId);
        $this->assertNull($activeUser);
    }

    public function test_calls_repository_soft_delete_method(): void
    {
        $user = User::factory()->create();

        $mockRepository = Mockery::mock(UserRepositoryContract::class);
        $mockRepository->shouldReceive('softDelete')
            ->once()
            ->with($user)
            ->andReturn(true);

        $action = new UserAccountDeleteAction($mockRepository);

        $result = $action->handle($user);

        $this->assertTrue($result);
    }

    public function test_revokes_tokens_before_soft_delete(): void
    {
        $user = User::factory()->create();
        $user->createToken('test-token');

        $mockRepository = Mockery::mock(UserRepositoryContract::class);

        // Track the order of operations
        $tokensRevokedBeforeSoftDelete = false;

        $mockRepository->shouldReceive('softDelete')
            ->once()
            ->with($user)
            ->andReturnUsing(static function (User $user) use (&$tokensRevokedBeforeSoftDelete): bool {
                // At this point, tokens should already be revoked
                $tokensRevokedBeforeSoftDelete = $user->tokens()->where('revoked', false)->count() === 0;

                return true;
            });

        $action = new UserAccountDeleteAction($mockRepository);
        $action->handle($user);

        $this->assertTrue($tokensRevokedBeforeSoftDelete, 'Tokens should be revoked before soft delete');
    }

    public function test_transaction_rolls_back_if_soft_delete_fails(): void
    {
        $user = User::factory()->create();
        $user->createToken('test-token');

        $initialTokenCount = $user->tokens()->count();

        $mockRepository = Mockery::mock(UserRepositoryContract::class);
        $mockRepository->shouldReceive('softDelete')
            ->once()
            ->andThrow(new Exception('Soft delete failed'));

        $action = new UserAccountDeleteAction($mockRepository);

        try {
            $action->handle($user);
            $this->fail('Expected exception was not thrown');
        } catch (Exception $e) {
            // Transaction should have rolled back, so tokens should still exist
            $user->refresh();
            $this->assertEquals($initialTokenCount, $user->tokens()->count());
        }
    }

    public function test_accepts_user_model(): void
    {
        $user = User::factory()->create();

        $this->assertInstanceOf(User::class, $user);

        $result = $this->action->handle($user);

        $this->assertTrue($result);
    }
}
