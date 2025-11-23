<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\User\UserLoginAction;
use App\Actions\User\UserLogoutAction;
use App\Actions\User\UserRegistrationAction;
use App\Actions\User\UserSimpleRegistrationAction;
use App\DTOs\User\LoginDTO;
use App\DTOs\User\SimpleUserRegistrationDTO;
use App\DTOs\User\UserRegistrationDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\RegisterWithCompanyRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class AuthController extends Controller
{
    /**
     * Handle user login request.
     */
    public function login(LoginRequest $request, UserLoginAction $action): JsonResponse
    {
        $dto = LoginDTO::fromFormRequest($request);
        $result = $action->handle($dto);

        return response()->json([
            'message' => 'Logged in successfully',
            'user' => new UserResource($result->user),
            'token' => $result->token,
        ]);
    }

    /**
     * Handle user registration request.
     */
    public function register(RegisterRequest $request, UserSimpleRegistrationAction $action): JsonResponse
    {
        $dto = SimpleUserRegistrationDTO::fromFormRequest($request);
        $user = $action->handle($dto);

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'message' => 'User registered successfully',
            'user' => new UserResource($user),
            'token' => $token,
        ], 201);
    }

    /**
     * Handle user registration with company request.
     */
    public function registerWithCompany(
        RegisterWithCompanyRequest $request,
        UserRegistrationAction $action
    ): JsonResponse {
        $dto = UserRegistrationDTO::fromFormRequest($request);

        $user = $action->handle($dto);

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'message' => 'User and company registered successfully',
            'user' => new UserResource($user),
            'token' => $token,
        ], 201);
    }

    /**
     * Handle user logout request.
     */
    public function logout(Request $request, UserLogoutAction $action): JsonResponse
    {
        $action->handle($request->user());

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
}
