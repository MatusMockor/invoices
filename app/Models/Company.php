<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CompanyType;
use App\Enums\VatPayerStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * BusinessEntity model representing business partners for invoicing
 *
 * @property int $id
 * @property string $name Company name
 * @property string $ico Company identification number
 * @property string|null $dic Tax identification number
 * @property string|null $ic_dph VAT identification number
 * @property string|null $vat_payer_status VAT payer status
 * @property string|null $street Street address
 * @property string|null $city City
 * @property string|null $postal_code Postal code
 * @property string|null $country Country
 * @property string|null $iban Bank account in IBAN format
 * @property string|null $swift SWIFT/BIC code
 * @property string|null $bank_name Bank name
 * @property string|null $phone Contact phone
 * @property string|null $email Contact email
 * @property string|null $website Company website
 * @property string|null $company_type Legal form (s.r.o., a.s., živnosť, etc.)
 * @property string|null $registration_office Registration office
 * @property string|null $registration_number Registration number
 * @property string|null $type Company type
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Invoice[] $invoices
 */
class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'ico',
        'name',
        'street',
        'city',
        'postal_code',
        'country',
        'dic',
        'ic_dph',
        'vat_payer_status',
        'iban',
        'swift',
        'bank_name',
        'phone',
        'email',
        'website',
        'company_type',
        'registration_office',
        'registration_number',
        'type',
    ];

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    protected function casts(): array
    {
        return [
            'type' => CompanyType::class,
            'vat_payer_status' => VatPayerStatus::class,
        ];
    }
}
