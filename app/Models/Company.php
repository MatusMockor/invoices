<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
 * @property string|null $iban Bank account number in IBAN format
 * @property string|null $swift Bank identifier code
 * @property string|null $phone Contact phone number
 * @property string|null $email Contact email address
 * @property string|null $website Company website
 * @property string $company_type Legal form of the company
 * @property string $registration_number Registration number in business register
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $user
 */
class Company extends Model
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
        'iban',
        'swift',
        'phone',
        'email',
        'website',
        'user_id',
        'company_type',
        'registration_number',
    ];

    /**
     * Get the user that owns the company.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the vehicles for the company.
     */
    public function vehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class);
    }
}
