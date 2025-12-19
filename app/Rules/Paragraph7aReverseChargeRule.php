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
class Paragraph7aReverseChargeRule implements ValidationRule
{
    private const EU_COUNTRIES = [
        'AT', 'BE', 'BG', 'HR', 'CY', 'CZ', 'DK', 'EE', 'FI', 'FR',
        'DE', 'GR', 'HU', 'IE', 'IT', 'LV', 'LT', 'LU', 'MT', 'NL',
        'PL', 'PT', 'RO', 'SI', 'ES', 'SE',
    ];

    public function __construct(
        private readonly ?UserCompany $supplierCompany,
        private readonly ?string $customerCountry
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($this->supplierCompany === null) {
            return;
        }

        $vatStatus = $this->supplierCompany->vat_payer_status;

        if ($vatStatus !== VatPayerStatus::REGISTERED_PARAGRAPH_7A) {
            return;
        }

        // §7a: Slovak customers - no VAT allowed
        if ($this->customerCountry === 'SK' && $value !== null && $value > 0) {
            $fail('Pre odberateľov zo Slovenska nesmie registrovaná osoba podľa §7a vystavovať faktúry s DPH.');
        }

        // §7a: EU customers - must be 0% (reverse charge)
        if (in_array($this->customerCountry, self::EU_COUNTRIES, true) && $value !== 0) {
            $fail('Pre odberateľov z EÚ musí byť sadzba DPH 0% (prenesenie daňovej povinnosti).');
        }
    }
}
