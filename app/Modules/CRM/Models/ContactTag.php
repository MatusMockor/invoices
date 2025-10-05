<?php

declare(strict_types=1);

namespace App\Modules\CRM\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ContactTag extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'color',
        'description',
    ];

    public function contacts(): BelongsToMany
    {
        return $this->belongsToMany(CrmContact::class, 'contact_tag_assignments', 'tag_id', 'contact_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
