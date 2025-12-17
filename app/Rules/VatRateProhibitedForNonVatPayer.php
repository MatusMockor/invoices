<?php

declare(strict_types=1);

namespace App\Rules;

use App\Enums\VatPayerStatus;
use App\Models\UserCompany;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class VatRateProhibitedForNonVatPayer implements ValidationRule
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

        // Non-VAT payers cannot have VAT rate
        if ($vatStatus === VatPayerStatus::NOT_VAT_PAYER && $value !== null && $value > 0) {
            $fail('Neplatca DPH nesmie vystavovať faktúry s DPH. Ak by ste omylom uviedli DPH, museli by ste ho zaplatiť štátu.');
        }
    }
}
