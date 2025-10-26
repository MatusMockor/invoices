<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\UserSetting\UserSettingShowAction;
use App\Actions\UserSetting\UserSettingUpdateAction;
use App\DTOs\UserSetting\UserSettingUpdateDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateUserSettingRequest;
use App\Http\Resources\UserSettingResource;
use Illuminate\Http\Request;

final class UserSettingController extends Controller
{
    public function __construct(
        private readonly UserSettingShowAction $showAction,
        private readonly UserSettingUpdateAction $updateAction
    ) {}

    /**
     * Get the authenticated user's settings.
     */
    public function show(Request $request): UserSettingResource
    {
        $settings = $this->showAction->handle($request->user());

        return new UserSettingResource($settings);
    }

    /**
     * Update the authenticated user's settings.
     */
    public function update(UpdateUserSettingRequest $request): UserSettingResource
    {
        $dto = UserSettingUpdateDTO::fromRequest($request);
        $settings = $this->updateAction->handle($request->user(), $dto);

        return new UserSettingResource($settings);
    }
}
