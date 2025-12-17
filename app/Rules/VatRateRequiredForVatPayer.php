<?php

declare(strict_types=1);

namespace App\Rules;

use App\Models\UserCompany;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class VatRateRequiredForVatPayer implements ValidationRule
{
    public function __construct(
        private readonly ?UserCompany $supplierCompany
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($this->supplierCompany === null) {
            return;
        }

        $vatStatus = $this->supplierCompany->vat_payer_status;

        // Full VAT payers must have VAT rate
        if ($vatStatus?->isVatPayer() && ($value === null || ! in_array($value, [0, 5, 19, 23]))) {
            $fail('Platca DPH musí uviesť sadzbu DPH pre každú položku faktúry.');
        }
    }
}
