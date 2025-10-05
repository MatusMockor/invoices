<?php

declare(strict_types=1);

namespace App\Modules\CRM\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ContactCustomFieldDefinition extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'type',
        'options',
        'is_required',
        'is_active',
        'sort_order',
        'description',
    ];

    protected $casts = [
        'options' => 'array',
        'is_required' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function values(): HasMany
    {
        return $this->hasMany(ContactCustomFieldValue::class, 'field_definition_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeRequired($query)
    {
        return $query->where('is_required', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }
}
