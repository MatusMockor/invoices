<?php

namespace App\Models;

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
 * @property string $street Street address
 * @property string $city City
 * @property string $postal_code Postal code
 * @property string $country Country
 * @property string|null $ic_dph VAT identification number
 * @property string $company_type Legal form of the company
 * @property string $registration_number Registration number in business register
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Invoice[] $invoices
 */
class BusinessEntity extends Model
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
        'company_type',
        'registration_number',
    ];

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }
}
