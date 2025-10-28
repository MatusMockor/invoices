<?php

declare(strict_types=1);

namespace App\Actions\UserSetting;

use App\DTOs\UserSetting\UserSettingUpdateDTO;
use App\Models\User;
use App\Models\UserSetting;
use Illuminate\Support\Facades\DB;

final class UserSettingUpdateAction
{
    public function handle(User $user, UserSettingUpdateDTO $dto): UserSetting
    {
        return DB::transaction(function () use ($user, $dto) {
            return UserSetting::updateOrCreate(
                ['user_id' => $user->id],
                ['invoice_template' => $dto->invoiceTemplate]
            );
        });
    }
}
