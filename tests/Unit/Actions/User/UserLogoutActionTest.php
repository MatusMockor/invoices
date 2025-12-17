<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\User;

use App\Actions\User\UserLogoutAction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Passport\AccessToken;
use Laravel\Passport\Passport;
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

    public function test_revokes_current_access_token(): void
    {
        $user = User::factory()->create();
        $tokenResult = $user->createToken('auth-token');

        // Use Passport::actingAs with real token for logout testing
        Passport::actingAs($user);

        // Create AccessToken with token ID for logout action
        $accessToken = new AccessToken([
            'oauth_access_token_id' => $tokenResult->token->id,
            'oauth_user_id' => $user->id,
            'oauth_scopes' => [],
        ]);
        $user->withAccessToken($accessToken);

        $this->assertEquals(1, $user->tokens()->where('revoked', false)->count());

        $this->action->handle($user);

        // Token should be revoked (not deleted in Passport)
        $this->assertEquals(0, $user->tokens()->where('revoked', false)->count());
    }

    public function test_wraps_logout_in_transaction(): void
    {
        DB::shouldReceive('transaction')
            ->once()
            ->andReturnUsing(static function (callable $callback): mixed {
                return $callback();
            });

        $user = User::factory()->create();
        $tokenResult = $user->createToken('auth-token');

        Passport::actingAs($user);
        $accessToken = new AccessToken([
            'oauth_access_token_id' => $tokenResult->token->id,
            'oauth_user_id' => $user->id,
            'oauth_scopes' => [],
        ]);
        $user->withAccessToken($accessToken);

        app(UserLogoutAction::class)->handle($user);
    }

    public function test_only_revokes_current_token_not_all_tokens(): void
    {
        $user = User::factory()->create();

        // Create multiple tokens
        $user->createToken('token-1');
        $tokenResult2 = $user->createToken('token-2');
        $user->createToken('token-3');

        // Set token2 as current
        Passport::actingAs($user);
        $accessToken = new AccessToken([
            'oauth_access_token_id' => $tokenResult2->token->id,
            'oauth_user_id' => $user->id,
            'oauth_scopes' => [],
        ]);
        $user->withAccessToken($accessToken);

        $this->assertEquals(3, $user->tokens()->where('revoked', false)->count());

        $this->action->handle($user);

        // Should have 2 non-revoked tokens left (token1 and token3)
        $this->assertEquals(2, $user->tokens()->where('revoked', false)->count());
    }
}
