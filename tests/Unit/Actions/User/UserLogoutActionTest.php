<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\User;

use App\Actions\User\UserLogoutAction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class UserLogoutActionTest extends TestCase
{
    use RefreshDatabase;

    private UserLogoutAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(UserLogoutAction::class);
    }

    public function test_deletes_current_access_token(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth-token');

        // Set the current access token
        $user->withAccessToken($token->accessToken);

        $this->assertEquals(1, $user->tokens()->count());

        $this->action->handle($user);

        $this->assertEquals(0, $user->tokens()->count());
    }

    public function test_wraps_logout_in_transaction(): void
    {
        DB::shouldReceive('transaction')
            ->once()
            ->andReturnUsing(function ($callback) {
                return $callback();
            });

        $user = User::factory()->create();
        $token = $user->createToken('auth-token');
        $user->withAccessToken($token->accessToken);

        app(UserLogoutAction::class)->handle($user);
    }

    public function test_only_deletes_current_token_not_all_tokens(): void
    {
        $user = User::factory()->create();

        // Create multiple tokens
        $token1 = $user->createToken('token-1');
        $token2 = $user->createToken('token-2');
        $token3 = $user->createToken('token-3');

        // Set token2 as current
        $user->withAccessToken($token2->accessToken);

        $this->assertEquals(3, $user->tokens()->count());

        $this->action->handle($user);

        // Should have 2 tokens left (token1 and token3)
        $this->assertEquals(2, $user->tokens()->count());
    }
}
