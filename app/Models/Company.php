<?php

declare(strict_types=1);

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
 * @property string|null $ic_dph VAT identification number
 * @property string|null $street Street address
 * @property string|null $city City
 * @property string|null $postal_code Postal code
 * @property string|null $country Country
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
    ];

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }
}
