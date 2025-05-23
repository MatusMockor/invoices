<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Trip model representing a vehicle trip in the system.
 *
 * @property int $id
 * @property int $vehicle_id
 * @property Carbon $date
 * @property string $start_location
 * @property string $end_location
 * @property string $purpose
 * @property int $start_odometer
 * @property int $end_odometer
 * @property int $distance
 * @property string $driver_name
 * @property float|null $fuel_amount
 * @property float|null $fuel_cost
 * @property string|null $fuel_receipt_number
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Vehicle $vehicle
 */
class Trip extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'vehicle_id',
        'date',
        'start_location',
        'end_location',
        'purpose',
        'start_odometer',
        'end_odometer',
        'distance',
        'driver_name',
        'fuel_amount',
        'fuel_cost',
        'fuel_receipt_number',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'date' => 'date',
        'start_odometer' => 'integer',
        'end_odometer' => 'integer',
        'distance' => 'integer',
        'fuel_amount' => 'decimal:2',
        'fuel_cost' => 'decimal:2',
    ];

    /**
     * Get the vehicle that owns the trip.
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }
}
