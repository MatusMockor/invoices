<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * UserSetting model representing user preferences and settings.
 *
 * @property int $id
 * @property int $user_id
 * @property \App\Enums\InvoiceTemplate $invoice_template
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read User $user
 */
final class UserSetting extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'invoice_template',
    ];

    /**
     * Get the user that owns the settings.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'invoice_template' => \App\Enums\InvoiceTemplate::class,
        ];
    }
}
