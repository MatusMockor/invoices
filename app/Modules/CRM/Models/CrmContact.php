<?php

declare(strict_types=1);

namespace App\Modules\CRM\Models;

use App\Models\Company;
use App\Models\Note;
use App\Models\User;
use App\Modules\CRM\Enums\ContactStatus;
use App\Modules\CRM\Factories\CrmContactFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ObservedBy([CrmContactObserver::class])]
class CrmContact extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'crm_contacts';

    protected $fillable = [
        'company_id',
        'user_id',
        'first_name',
        'last_name',
        'primary_email',
        'primary_phone',
        'job_title',
        'metadata',
        'is_active',
        'last_contacted_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'is_active' => 'boolean',
        'last_contacted_at' => 'datetime',
    ];

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): CrmContactFactory
    {
        return CrmContactFactory::new();
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function emails(): HasMany
    {
        return $this->hasMany(ContactEmail::class, 'contact_id');
    }

    public function phones(): HasMany
    {
        return $this->hasMany(ContactPhone::class, 'contact_id');
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(ContactAddress::class, 'contact_id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(ContactTag::class, 'contact_tag_assignments', 'contact_id', 'tag_id');
    }

    public function customFieldValues(): HasMany
    {
        return $this->hasMany(ContactCustomFieldValue::class, 'contact_id');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(ContactActivity::class, 'contact_id');
    }

    public function notes(): MorphMany
    {
        return $this->morphMany(Note::class, 'noteable');
    }

    public function getFullNameAttribute(): string
    {
        return trim(($this->first_name ?? '').' '.($this->last_name ?? '')) ?: 'Unnamed Contact';
    }

    public function getPrimaryAddressAttribute(): ?ContactAddress
    {
        if ($this->relationLoaded('addresses')) {
            return $this->addresses->where('is_primary', true)->first();
        }

        return $this->addresses()->where('is_primary', true)->first();
    }

    public function getPrimaryEmailAttribute(): ?ContactEmail
    {
        if ($this->relationLoaded('emails')) {
            return $this->emails->where('type', 'primary')->first();
        }

        return $this->emails()->where('type', 'primary')->first();
    }

    public function getPrimaryPhoneAttribute(): ?ContactPhone
    {
        if ($this->relationLoaded('phones')) {
            return $this->phones->where('type', 'primary')->first();
        }

        return $this->phones()->where('type', 'primary')->first();
    }

    // Simple accessors for backward compatibility with existing views
    public function getPrimaryEmailStringAttribute(): ?string
    {
        return $this->primary_email?->email;
    }

    public function getPrimaryPhoneStringAttribute(): ?string
    {
        return $this->primary_phone?->phone;
    }

    public function getStatusAttribute(): ContactStatus
    {
        return $this->is_active ? ContactStatus::ACTIVE : ContactStatus::INACTIVE;
    }

    public function setStatusAttribute(ContactStatus $status): void
    {
        $this->is_active = $status === ContactStatus::ACTIVE;
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeInactive(Builder $query): Builder
    {
        return $query->where('is_active', false);
    }

    public function scopeByCompany(Builder $query, int $companyId): Builder
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeByUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeWithTag(Builder $query, string $tagName): Builder
    {
        return $query->whereHas('tags', function (Builder $q) use ($tagName) {
            $q->where('name', $tagName);
        });
    }

    public function scopeRecentlyContacted(Builder $query, int $days = 30): Builder
    {
        return $query->where('last_contacted_at', '>=', now()->subDays($days));
    }

    public function scopeSearch(Builder $query, string $search): Builder
    {
        return $query->where(function (Builder $q) use ($search) {
            $q->where('first_name', 'like', "%{$search}%")
                ->orWhere('last_name', 'like', "%{$search}%")
                ->orWhere('primary_email', 'like', "%{$search}%")
                ->orWhere('primary_phone', 'like', "%{$search}%")
                ->orWhere('job_title', 'like', "%{$search}%")
                ->orWhereHas('emails', function (Builder $emailQuery) use ($search) {
                    $emailQuery->where('email', 'like', "%{$search}%");
                })
                ->orWhereHas('phones', function (Builder $phoneQuery) use ($search) {
                    $phoneQuery->where('phone', 'like', "%{$search}%");
                });
        });
    }
}
