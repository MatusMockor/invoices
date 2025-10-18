<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Handle user login request.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $data = $request->getData();

        // Find user by email
        $user = User::where('email', $data['email'])->first();

        // Check if user exists and password is correct
        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Create token without creating session
        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'message' => 'Logged in successfully',
            'user' => new UserResource($user),
            'token' => $token,
        ]);
    }

    /**
     * Handle user registration request.
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create($request->getData());

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'message' => 'User registered successfully',
            'user' => new UserResource($user),
            'token' => $token,
        ], 201);
    }

    /**
     * Handle user logout request.
     */
    public function logout(Request $request): JsonResponse
    {
        // Revoke the current token that was used to authenticate the request
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully',
        ]);
    }

    /**
     * Get the authenticated user.
     */
    public function user(Request $request): UserResource
    {
        return new UserResource($request->user());
    }

    /**
     * Clear old session cookies (helper for migration to token-based auth).
     */
    public function clearCookies(): JsonResponse
    {
        $response = response()->json([
            'message' => 'Cookies cleared',
        ]);

        // Expire all possible session cookies
        $cookies = ['laravel_session', 'XSRF-TOKEN'];

        // Also try to get any encrypted session cookie name
        foreach ($cookies as $cookie) {
            $response->cookie($cookie, '', -1, '/', null, false, false);
        }

        // Try to clear any possible encrypted cookie names
        if (isset($_COOKIE)) {
            foreach (array_keys($_COOKIE) as $cookieName) {
                $response->cookie($cookieName, '', -1, '/', null, false, false);
            }
        }

        return $response;
    }
}
