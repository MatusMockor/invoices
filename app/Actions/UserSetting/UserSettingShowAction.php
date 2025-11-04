<?php

declare(strict_types=1);

namespace App\Actions\UserSetting;

use App\Enums\InvoiceTemplate;
use App\Models\User;
use App\Models\UserSetting;

final class UserSettingShowAction
{
    public function handle(User $user): UserSetting
    {
        // Eager load the current company relationship for use in the resource
        $user->load('currentCompany');

        return UserSetting::firstOrCreate(
            ['user_id' => $user->id],
            ['invoice_template' => InvoiceTemplate::default()->value]
        );
    }
}
