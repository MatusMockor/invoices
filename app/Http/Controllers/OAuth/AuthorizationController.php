<?php

declare(strict_types=1);

namespace App\Http\Controllers\OAuth;

use App\Http\Controllers\Controller;
use App\OAuth\OAuthScopes;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Laravel\Passport\Bridge\User;
use Laravel\Passport\ClientRepository;
use Laravel\Passport\Http\Controllers\ApproveAuthorizationController;
use Laravel\Passport\Http\Controllers\DenyAuthorizationController;
use Laravel\Passport\Passport;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Entities\ScopeEntityInterface;
use League\OAuth2\Server\RequestTypes\AuthorizationRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final class AuthorizationController extends Controller
{
    public function __construct(
        private readonly AuthorizationServer $server,
        private readonly ClientRepository $clients,
    ) {}

    /**
     * Display OAuth consent screen.
     *
     * This method handles the OAuth authorization flow with custom Slovak consent screen.
     */
    public function showAuthorizationForm(
        ServerRequestInterface $psrRequest,
        Request $request,
        ResponseInterface $psrResponse,
    ): View|Response|RedirectResponse {
        Log::info('OAuth authorize called', [
            'url' => $request->fullUrl(),
            'authenticated' => Auth::check(),
            'user_id' => Auth::id(),
        ]);

        // Check if user is authenticated via web session
        if (! Auth::check()) {
            // Store OAuth URL for post-login redirect
            session()->put('url.intended', $request->fullUrl());
            Log::info('Redirecting to Blade login for OAuth', ['url' => $request->fullUrl()]);

            // Redirect to Blade login page (not React SPA)
            return redirect()->route('oauth.login');
        }

        // Validate the authorization request using Passport's server
        try {
            $authRequest = $this->server->validateAuthorizationRequest($psrRequest);
        } catch (\League\OAuth2\Server\Exception\OAuthServerException $e) {
            Log::error('OAuth validation failed', ['error' => $e->getMessage()]);

            return $this->renderError($e->getMessage(), $e->getErrorType());
        }

        // Set the user on the auth request
        $user = Auth::user();
        $authRequest->setUser(new User((string) $user->getAuthIdentifier()));

        // Get scopes and client info
        $scopes = $this->parseScopes($authRequest);
        $client = $this->clients->find($authRequest->getClient()->getIdentifier());

        // Check for auto-approval (client skips authorization or user already granted these scopes)
        $skipsAuth = $client->skipsAuthorization($user, $scopes);
        $hasGranted = $this->hasGrantedScopes($user, $client, $scopes);

        Log::info('OAuth auto-approve check', [
            'skipsAuthorization' => $skipsAuth,
            'hasGrantedScopes' => $hasGranted,
        ]);

        if ($skipsAuth || $hasGranted) {
            Log::info('OAuth auto-approving');

            return $this->approveRequest($authRequest, $psrResponse);
        }

        // Store auth request in session for approve/deny
        $authToken = Str::random();
        $request->session()->put('authToken', $authToken);
        $request->session()->put('authRequest', $authRequest);
        $request->session()->save();

        Log::debug('OAuth consent screen - session saved', [
            'has_authRequest' => $request->session()->has('authRequest'),
        ]);

        // Display custom consent screen with Slovak translations
        return view('oauth.authorize', [
            'client' => [
                'id' => $client->getKey(),
                'name' => $client->name,
            ],
            'scopes' => $this->formatScopesForDisplay($scopes),
            'request' => [
                'client_id' => $client->getKey(),
                'redirect_uri' => $authRequest->getRedirectUri(),
                'response_type' => 'code',
                'scope' => implode(' ', array_map(
                    static fn (ScopeEntityInterface $scope): string => $scope->getIdentifier(),
                    $authRequest->getScopes()
                )),
                'state' => $request->query('state'),
            ],
            'authToken' => $authToken,
        ]);
    }

    /**
     * Approve the authorization request.
     */
    public function approve(
        Request $request,
        ResponseInterface $psrResponse,
    ): Response|RedirectResponse {
        Log::info('OAuth approve called', [
            'auth_token' => $request->input('auth_token'),
            'session_authToken' => $request->session()->get('authToken'),
            'has_authRequest' => $request->session()->has('authRequest'),
        ]);

        try {
            // Delegate to Passport's approve controller
            $approveController = app(ApproveAuthorizationController::class);
            $response = $approveController->approve($request, $psrResponse);

            Log::info('OAuth approve success', [
                'status' => $response->getStatusCode(),
                'location' => $response->headers->get('Location'),
            ]);

            return $response;
        } catch (Throwable $e) {
            Log::error('OAuth approve failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Deny the authorization request.
     */
    public function deny(
        Request $request,
        ResponseInterface $psrResponse,
    ): Response|RedirectResponse {
        // Delegate to Passport's deny controller
        $denyController = app(DenyAuthorizationController::class);

        return $denyController->deny($request, $psrResponse);
    }

    /**
     * Transform the authorization request's scopes into Scope instances.
     *
     * @return \Laravel\Passport\Scope[]
     */
    private function parseScopes(AuthorizationRequestInterface $authRequest): array
    {
        return Passport::scopesFor(
            collect($authRequest->getScopes())->map(
                static fn (ScopeEntityInterface $scope): string => $scope->getIdentifier()
            )->unique()->all()
        );
    }

    /**
     * Check if user has already granted the client access to the scopes.
     *
     * @param  \Laravel\Passport\Scope[]  $scopes
     */
    private function hasGrantedScopes($user, $client, array $scopes): bool
    {
        $activeTokens = $client->tokens()->where([
            ['user_id', '=', $user->getAuthIdentifier()],
            ['revoked', '=', false],
            ['expires_at', '>', now()],
        ]);

        if (empty($scopes)) {
            return $activeTokens->exists();
        }

        return collect($scopes)->pluck('id')->diff(
            $activeTokens->get()->pluck('scopes')->flatten()
        )->isEmpty();
    }

    /**
     * Approve the authorization request and return response.
     */
    private function approveRequest(AuthorizationRequestInterface $authRequest, ResponseInterface $psrResponse): Response
    {
        $authRequest->setAuthorizationApproved(true);

        $response = $this->server->completeAuthorizationRequest($authRequest, $psrResponse);

        return new Response(
            (string) $response->getBody(),
            $response->getStatusCode(),
            $response->getHeaders()
        );
    }

    /**
     * Format scopes for display in consent screen.
     *
     * @param  \Laravel\Passport\Scope[]  $scopes
     * @return array<array{id: string, name: string, description: string}>
     */
    private function formatScopesForDisplay(array $scopes): array
    {
        return array_map(static function ($scope): array {
            $oauthScope = OAuthScopes::tryFrom($scope->id);

            if (! $oauthScope) {
                return [
                    'id' => $scope->id,
                    'name' => $scope->id,
                    'description' => $scope->description,
                ];
            }

            return [
                'id' => $oauthScope->value,
                'name' => $oauthScope->shortDescription(),
                'description' => $oauthScope->description(),
            ];
        }, $scopes);
    }

    /**
     * Render error view.
     */
    private function renderError(string $message, string $errorCode): View
    {
        return view('oauth.error', [
            'message' => $message,
            'errorCode' => $errorCode,
        ]);
    }
}
