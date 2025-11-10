<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\InvoiceStatus;
use App\Observers\InvoiceObserver;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
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
 * @property int|null $company_id
 * @property int|null $supplier_company_id
 * @property string|null $company_ico
 * @property string|null $company_dic
 * @property string|null $company_ic_dph
 * @property string|null $company_name
 * @property string|null $company_address
 * @property string|null $company_city
 * @property string|null $company_zip
 * @property string|null $company_country
 * @property float $total_amount
 * @property string $currency
 * @property string|null $variable_symbol
 * @property string|null $constant_symbol
 * @property string|null $specific_symbol
 * @property string|null $note
 * @property InvoiceStatus $status
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read User $user
 * @property-read Company|null $company
 * @property-read UserCompany|null $supplierCompany
 * @property-read Collection|InvoiceItem[] $items
 */
#[ObservedBy([InvoiceObserver::class])]
class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'invoice_number',
        'issue_date',
        'due_date',
        'delivery_date',
        'company_id',
        'supplier_company_id',
        'company_ico',
        'company_dic',
        'company_ic_dph',
        'company_name',
        'company_address',
        'company_city',
        'company_zip',
        'company_country',
        'total_amount',
        'currency',
        'variable_symbol',
        'constant_symbol',
        'specific_symbol',
        'note',
        'status',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'due_date' => 'date',
        'delivery_date' => 'date',
        'total_amount' => 'float',
        'status' => InvoiceStatus::class,
    ];

    /**
     * Get the user that owns the invoice.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function supplierCompany(): BelongsTo
    {
        return $this->belongsTo(UserCompany::class, 'supplier_company_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function notes(): MorphMany
    {
        return $this->morphMany(Note::class, 'noteable');
    }
}
