<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Carbon;

/**
 * Contact model representing a contact person.
 *
 * @property int $id
 * @property int $user_id
 * @property int $company_id
 * @property string|null $first_name
 * @property string|null $last_name
 * @property string|null $email
 * @property string|null $phone
 * @property string|null $position
 * @property string|null $notes
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read string $full_name
 * @property-read UserCompany|null $company
 * @property-read User|null $user
 */
class Contact extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'user_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'position',
        'notes',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(UserCompany::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function notes(): MorphMany
    {
        return $this->morphMany(Note::class, 'noteable');
    }

    public function getFullNameAttribute(): string
    {
        return trim(($this->first_name ?? '').' '.($this->last_name ?? '')) ?: ($this->email ?? '');
    }
}
