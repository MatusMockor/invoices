<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CompanyType;
use App\Enums\VatPayerStatus;
use App\Enums\VatPeriod;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Company model representing the user's company for invoicing
 *
 * @property int $id
 * @property int|null $user_id Owner of the company
 * @property string $name Company name
 * @property string $street Street address
 * @property string $city City
 * @property string $postal_code Postal code
 * @property string $country Country
 * @property string $ico Company identification number
 * @property string|null $dic Tax identification number
 * @property string|null $ic_dph VAT identification number
 * @property VatPayerStatus|null $vat_payer_status VAT payer status
 * @property VatPeriod|null $vat_period VAT period (monthly/quarterly)
 * @property string|null $iban Bank account number in IBAN format
 * @property string|null $swift Bank identifier code
 * @property string|null $phone Contact phone number
 * @property string|null $email Contact email address
 * @property string|null $website Company website
 * @property CompanyType $type Company type enum
 * @property string $registration_number Registration number in business register
 * @property string|null $registration_office Registration office (e.g., Okresny sud Bratislava I)
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read int $clients_count Number of unique clients
 * @property-read int $vehicles_count Number of vehicles
 * @property-read \App\Models\User|null $user
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Invoice[] $invoices
 */
class UserCompany extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'street',
        'city',
        'postal_code',
        'country',
        'ico',
        'dic',
        'ic_dph',
        'vat_payer_status',
        'vat_period',
        'iban',
        'swift',
        'phone',
        'email',
        'website',
        'user_id',
        'type',
        'registration_number',
        'registration_office',
    ];

    /**
     * Get the user that owns the company.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all invoices for this company.
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'supplier_company_id');
    }

    /**
     * Get all VAT status history records for this company
     */
    public function vatStatusHistory(): HasMany
    {
        return $this->hasMany(VatStatusHistory::class)->orderByDesc('valid_from');
    }

    /**
     * Get the current VAT status history record
     */
    public function currentVatStatusHistory(): HasOne
    {
        return $this->hasOne(VatStatusHistory::class)->whereNull('valid_to');
    }

    /**
     * Get the number of unique clients (companies) this company has issued invoices to.
     */
    protected function clientsCount(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->invoices()->distinct('company_id')->count('company_id'),
        );
    }

    /**
     * Get the number of vehicles. Returns 0 for now as vehicles module is not implemented.
     */
    protected function vehiclesCount(): Attribute
    {
        return Attribute::make(
            get: fn () => 0,
        );
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => CompanyType::class,
            'vat_payer_status' => VatPayerStatus::class,
            'vat_period' => VatPeriod::class,
        ];
    }
}
