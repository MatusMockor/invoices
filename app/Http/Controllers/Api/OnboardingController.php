<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\Company\CompanyCreationAction;
use App\DTOs\Company\CompanyCreationDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateCompanyRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class OnboardingController extends Controller
{
    /**
     * Complete onboarding by creating user's first company.
     */
    public function store(
        CreateCompanyRequest $request,
        CompanyCreationAction $action
    ): JsonResponse {
        $dto = CompanyCreationDTO::fromRequest($request->all());

        $user = $request->user();

        $action->handle($dto, $user);

        return response()->json([
            'message' => 'Onboarding completed successfully',
            'user' => new UserResource($user->fresh()),
        ], 201);
    }

    /**
     * Check if user needs onboarding.
     */
    public function check(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'needs_onboarding' => $user->current_company_id === null,
        ]);
    }
}
