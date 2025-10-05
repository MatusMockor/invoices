<?php

declare(strict_types=1);

namespace App\Modules\CRM\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContactAddress extends Model
{
    use HasFactory;

    protected $fillable = [
        'contact_id',
        'type',
        'street',
        'city',
        'postal_code',
        'state',
        'country',
        'latitude',
        'longitude',
        'is_primary',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    public function contact(): BelongsTo
    {
        return $this->belongsTo(CrmContact::class, 'contact_id');
    }

    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([
            $this->street,
            $this->city,
            $this->postal_code,
            $this->state,
            $this->country,
        ]);

        return implode(', ', $parts);
    }

    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }
}
