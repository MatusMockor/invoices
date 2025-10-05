<?php

declare(strict_types=1);

namespace App\Modules\CRM\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContactCustomFieldValue extends Model
{
    use HasFactory;

    protected $fillable = [
        'contact_id',
        'field_definition_id',
        'value',
    ];

    public function contact(): BelongsTo
    {
        return $this->belongsTo(CrmContact::class, 'contact_id');
    }

    public function fieldDefinition(): BelongsTo
    {
        return $this->belongsTo(ContactCustomFieldDefinition::class, 'field_definition_id');
    }
}
