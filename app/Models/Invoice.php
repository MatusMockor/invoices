<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\InvoiceStatus;
use App\Observers\InvoiceObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
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
 * @property string|null $supplier_registry_office Snapshot of supplier registry office
 * @property string|null $supplier_registry_number Snapshot of supplier registration number
 * @property string|null $company_ico
 * @property string|null $company_dic
 * @property string|null $company_ic_dph
 * @property string|null $company_name
 * @property string|null $company_address
 * @property string|null $company_city
 * @property string|null $company_zip
 * @property string|null $company_country
 * @property float $total_amount
 * @property float $subtotal
 * @property float $tax_amount
 * @property float $tax_rate
 * @property float|null $discount_amount
 * @property float|null $discount_percentage
 * @property bool $reverse_charge
 * @property string|null $tax_exemption_reason
 * @property string|null $special_text
 * @property string|null $notes
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
 * @property-read string|null $reverse_charge_text Computed text for reverse charge
 * @property-read string|null $tax_exemption_text Computed text for tax exemption
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
        'supplier_registry_office',
        'supplier_registry_number',
        'company_ico',
        'company_dic',
        'company_ic_dph',
        'company_name',
        'company_address',
        'company_city',
        'company_zip',
        'company_country',
        'total_amount',
        'subtotal',
        'tax_amount',
        'tax_rate',
        'discount_amount',
        'discount_percentage',
        'reverse_charge',
        'tax_exemption_reason',
        'special_text',
        'notes',
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
        'subtotal' => 'float',
        'tax_amount' => 'float',
        'tax_rate' => 'float',
        'discount_amount' => 'float',
        'discount_percentage' => 'float',
        'reverse_charge' => 'boolean',
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

    /**
     * Get the reverse charge text for VAT liability transfer.
     * Returns appropriate text when reverse_charge is true.
     */
    public function getReverseChargeTextAttribute(): ?string
    {
        if (! $this->reverse_charge) {
            return null;
        }

        return 'Prenesenie daňovej povinnosti podľa §69 ods. 12 zákona o DPH';
    }

    /**
     * Get the tax exemption text.
     * Returns the tax exemption reason formatted as a text.
     */
    public function getTaxExemptionTextAttribute(): ?string
    {
        if (! $this->tax_exemption_reason) {
            return null;
        }

        return $this->tax_exemption_reason;
    }
}
