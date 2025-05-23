<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * Vehicle model representing a vehicle in the system.
 *
 * @property int $id
 * @property int $company_id
 * @property string $type
 * @property string $license_plate
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Company $company
 * @property-read Collection|Trip[] $trips
 */
class Vehicle extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'company_id',
        'type',
        'license_plate',
    ];

    /**
     * Get the company that owns the vehicle.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the trips for the vehicle.
     */
    public function trips(): HasMany
    {
        return $this->hasMany(Trip::class);
    }
}
