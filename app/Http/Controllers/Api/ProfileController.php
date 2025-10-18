<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProfileDestroyRequest;
use App\Http\Requests\ProfileUpdateRequest;
use App\Http\Resources\UserResource;
use App\Repositories\Interfaces\UserRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function __construct(
        private readonly UserRepository $userRepository
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
     * Delete the user's account.
     */
    public function destroy(ProfileDestroyRequest $request): JsonResponse
    {
        $user = $request->user();

        Auth::logout();

        $this->userRepository->delete($user);

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['message' => 'Account deleted successfully']);
    }
}
