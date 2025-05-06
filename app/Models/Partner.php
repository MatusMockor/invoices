<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Partner extends Model
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
