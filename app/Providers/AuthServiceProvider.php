<?php

declare(strict_types=1);

namespace App\Providers;

use App\OAuth\OAuthScopes;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Laravel\Passport\Passport;

final class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // Policies are registered in AppServiceProvider using Gate::policy()
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // Define OAuth scopes with Slovak descriptions for consent screen
        Passport::tokensCan(OAuthScopes::toArray());

        // Set default scopes - applied if client doesn't request specific scopes
        Passport::setDefaultScope(OAuthScopes::defaults());

        /*
        |----------------------------------------------------------------------
        | Token Expiration Settings (PRD Requirements)
        |----------------------------------------------------------------------
        |
        | Access token: 1 hour (3600 seconds)
        | Refresh token: 30 days
        | Personal access token: 6 months (for internal use)
        |
        */
        $accessTokenMinutes = (int) config('passport.access_token_expire_minutes', 60);
        $refreshTokenDays = (int) config('passport.refresh_token_expire_days', 30);

        Passport::tokensExpireIn(now()->addMinutes($accessTokenMinutes));
        Passport::refreshTokensExpireIn(now()->addDays($refreshTokenDays));
        Passport::personalAccessTokensExpireIn(now()->addMonths(6));

        /*
        |----------------------------------------------------------------------
        | Grant Types Configuration (OAuth 2.1 Compliance)
        |----------------------------------------------------------------------
        |
        | Enabled grant types:
        | - authorization_code: Main OAuth flow for ChatGPT integration
        | - refresh_token: Token renewal without re-authorization
        |
        | Disabled grant types (deprecated in OAuth 2.1):
        | - password: Disabled by default in Passport 12 (OAuth 2.1 compliant)
        | - implicit: Deprecated, replaced by authorization_code with PKCE
        |
        | Note: Password grant is disabled by default in Passport 12.
        | To enable it (not recommended), call: Passport::enablePasswordGrant()
        */
    }
}
