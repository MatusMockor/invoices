<?php

namespace App\Models;

use App\Observers\InvoiceObserver;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * Invoice model representing an invoice in the system.
 *
 * @property int $id
 * @property int $user_id
 * @property string $invoice_number
 * @property Carbon $issue_date
 * @property Carbon $due_date
 * @property Carbon $delivery_date
 * @property int $business_entity_id
 * @property int $supplier_company_id
 * @property float $total_amount
 * @property string $currency
 * @property string|null $constant_symbol
 * @property string|null $note
 * @property string $status
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read User $user
 * @property-read BusinessEntity $businessEntity
 * @property-read Company $supplierCompany
 * @property-read Collection|InvoiceItem[] $items
 */
#[ObservedBy([InvoiceObserver::class])]
class Invoice extends Model
{
    use HasFactory;

    /**
     * Get the user that owns the invoice.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected $fillable = [
        'user_id',
        'invoice_number',
        'issue_date',
        'due_date',
        'delivery_date',
        'business_entity_id',
        'supplier_company_id',
        'total_amount',
        'currency',
        'constant_symbol',
        'note',
        'status',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'due_date' => 'date',
        'delivery_date' => 'date',
    ];

    public function businessEntity(): BelongsTo
    {
        return $this->belongsTo(BusinessEntity::class);
    }

    public function supplierCompany(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'supplier_company_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }
}
