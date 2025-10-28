<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\User\UserAccountDeleteAction;
use App\Actions\User\UserPasswordUpdateAction;
use App\DTOs\User\UserPasswordUpdateDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\ProfileDestroyRequest;
use App\Http\Requests\ProfileUpdateRequest;
use App\Http\Requests\UpdatePasswordRequest;
use App\Http\Resources\UserResource;
use App\Repositories\Interfaces\UserRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly UserPasswordUpdateAction $passwordUpdateAction,
        private readonly UserAccountDeleteAction $accountDeleteAction
    ) {}

    /**
     * Get the user's profile information.
     */
    public function show(Request $request): UserResource
    {
        return new UserResource($request->user());
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): UserResource
    {
        $user = $request->user();
        $this->userRepository->update($user, $request->validated());

        return new UserResource($user->fresh());
    }

    /**
     * Update the user's password.
     */
    public function updatePassword(UpdatePasswordRequest $request): JsonResponse
    {
        $dto = UserPasswordUpdateDTO::fromRequest($request);

        $this->passwordUpdateAction->handle($request->user(), $dto);

        return response()->json(['message' => 'Password updated successfully']);
    }

    /**
     * Delete the user's account.
     */
    public function destroy(ProfileDestroyRequest $request): JsonResponse
    {
        $user = $request->user();

        $this->accountDeleteAction->handle($user);

        return response()->json(['message' => 'Account deleted successfully']);
    }
}
