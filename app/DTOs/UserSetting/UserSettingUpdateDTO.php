<?php

declare(strict_types=1);

namespace App\DTOs\UserSetting;

use App\Http\Requests\UpdateUserSettingRequest;

final readonly class UserSettingUpdateDTO
{
    public function __construct(
        public string $invoiceTemplate
    ) {}

    public static function fromRequest(UpdateUserSettingRequest $request): self
    {
        return new self(
            invoiceTemplate: $request->validated('invoice_template')
        );
    }
}
