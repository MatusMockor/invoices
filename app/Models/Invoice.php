<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\InvoiceStatus;
use App\Enums\VatPayerStatus;
use App\Enums\VatPeriod;
use App\Observers\InvoiceObserver;
use App\Support\InvoicePartySnapshot;
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
 * @property array<string, mixed> $party_snapshot
 * @property string|null $supplier_registry_office Snapshot of supplier registry office
 * @property string|null $supplier_registry_number Snapshot of supplier registration number
 * @property VatPayerStatus|null $supplier_vat_payer_status Snapshot of supplier VAT status
 * @property VatPeriod|null $supplier_vat_period Snapshot of supplier VAT period
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
 * @property string|null $qr_code Dynamic property for QR code (not persisted)
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
        'party_snapshot',
        'supplier_registry_office',
        'supplier_registry_number',
        'supplier_vat_payer_status',
        'supplier_vat_period',
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
     * @return array<string, mixed>
     */
    public function getPartySnapshotAttribute(mixed $value): array
    {
        if (is_array($value)) {
            return InvoicePartySnapshot::normalize($value);
        }

        if (is_string($value) && $value !== '') {
            $decoded = json_decode($value, true);

            return InvoicePartySnapshot::normalize(is_array($decoded) ? $decoded : null);
        }

        return InvoicePartySnapshot::normalize(null);
    }

    /**
     * @param  array<string, mixed>|null  $value
     */
    public function setPartySnapshotAttribute(?array $value): void
    {
        $this->attributes['party_snapshot'] = json_encode(InvoicePartySnapshot::normalize($value));
    }

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

    /**
     * Get supplier VAT status from immutable party snapshot.
     */
    public function getEffectiveVatStatus(): ?VatPayerStatus
    {
        $status = data_get($this->party_snapshot, 'supplier.vat_payer_status');

        if (! is_string($status) || $status === '') {
            return null;
        }

        return VatPayerStatus::from($status);
    }

    public function getSupplierVatPeriodSnapshot(): ?VatPeriod
    {
        $period = data_get($this->party_snapshot, 'supplier.vat_period');

        if (! is_string($period) || $period === '') {
            return null;
        }

        return VatPeriod::from($period);
    }

    /**
     * Check if the supplier was a full VAT payer at invoice creation time
     */
    public function supplierIsVatPayer(): bool
    {
        $status = $this->getEffectiveVatStatus();

        return $status === VatPayerStatus::VAT_PAYER
            || $status === VatPayerStatus::VAT_PAYER_PARAGRAPH_7;
    }

    /**
     * Check if the supplier was registered under §7a at invoice creation time
     */
    public function supplierIsRegisteredParagraph7a(): bool
    {
        return $this->getEffectiveVatStatus() === VatPayerStatus::REGISTERED_PARAGRAPH_7A;
    }

    /**
     * Check if the invoice should show VAT fields based on supplier status
     */
    public function shouldShowVatFields(): bool
    {
        $status = $this->getEffectiveVatStatus();

        return $status !== null && $status !== VatPayerStatus::NOT_VAT_PAYER;
    }

    /**
     * @return array<string, mixed>
     */
    public function getSupplierSnapshot(): array
    {
        /** @var array<string, mixed> $snapshot */
        $snapshot = data_get($this->party_snapshot, 'supplier', []);

        return $snapshot;
    }

    /**
     * @return array<string, mixed>
     */
    public function getCustomerSnapshot(): array
    {
        /** @var array<string, mixed> $snapshot */
        $snapshot = data_get($this->party_snapshot, 'customer', []);

        return $snapshot;
    }

    /**
     * @return array<string, mixed>
     */
    public function getSupplierBankSnapshot(): array
    {
        /** @var array<string, mixed> $snapshot */
        $snapshot = data_get($this->party_snapshot, 'supplier.bank', []);

        return $snapshot;
    }

    public function getSupplierRegistryOfficeAttribute(): ?string
    {
        return $this->getLegacySnapshotValue('supplier.registration_office');
    }

    public function setSupplierRegistryOfficeAttribute(?string $value): void
    {
        $this->setLegacySnapshotValue('supplier.registration_office', $value);
    }

    public function getSupplierRegistryNumberAttribute(): ?string
    {
        return $this->getLegacySnapshotValue('supplier.registration_number');
    }

    public function setSupplierRegistryNumberAttribute(?string $value): void
    {
        $this->setLegacySnapshotValue('supplier.registration_number', $value);
    }

    public function getSupplierVatPayerStatusAttribute(): ?VatPayerStatus
    {
        return $this->getEffectiveVatStatus();
    }

    public function setSupplierVatPayerStatusAttribute(VatPayerStatus|string|null $value): void
    {
        $this->setLegacySnapshotValue(
            'supplier.vat_payer_status',
            $value instanceof VatPayerStatus ? $value->value : $value
        );
    }

    public function getSupplierVatPeriodAttribute(): ?VatPeriod
    {
        return $this->getSupplierVatPeriodSnapshot();
    }

    public function setSupplierVatPeriodAttribute(VatPeriod|string|null $value): void
    {
        $this->setLegacySnapshotValue(
            'supplier.vat_period',
            $value instanceof VatPeriod ? $value->value : $value
        );
    }

    public function getCompanyIcoAttribute(): ?string
    {
        return $this->getLegacySnapshotValue('customer.ico');
    }

    public function setCompanyIcoAttribute(?string $value): void
    {
        $this->setLegacySnapshotValue('customer.ico', $value);
    }

    public function getCompanyDicAttribute(): ?string
    {
        return $this->getLegacySnapshotValue('customer.dic');
    }

    public function setCompanyDicAttribute(?string $value): void
    {
        $this->setLegacySnapshotValue('customer.dic', $value);
    }

    public function getCompanyIcDphAttribute(): ?string
    {
        return $this->getLegacySnapshotValue('customer.ic_dph');
    }

    public function setCompanyIcDphAttribute(?string $value): void
    {
        $this->setLegacySnapshotValue('customer.ic_dph', $value);
    }

    public function getCompanyNameAttribute(): ?string
    {
        return $this->getLegacySnapshotValue('customer.name');
    }

    public function setCompanyNameAttribute(?string $value): void
    {
        $this->setLegacySnapshotValue('customer.name', $value);
    }

    public function getCompanyAddressAttribute(): ?string
    {
        return $this->getLegacySnapshotValue('customer.street');
    }

    public function setCompanyAddressAttribute(?string $value): void
    {
        $this->setLegacySnapshotValue('customer.street', $value);
    }

    public function getCompanyCityAttribute(): ?string
    {
        return $this->getLegacySnapshotValue('customer.city');
    }

    public function setCompanyCityAttribute(?string $value): void
    {
        $this->setLegacySnapshotValue('customer.city', $value);
    }

    public function getCompanyZipAttribute(): ?string
    {
        return $this->getLegacySnapshotValue('customer.postal_code');
    }

    public function setCompanyZipAttribute(?string $value): void
    {
        $this->setLegacySnapshotValue('customer.postal_code', $value);
    }

    public function getCompanyCountryAttribute(): ?string
    {
        return $this->getLegacySnapshotValue('customer.country');
    }

    public function setCompanyCountryAttribute(?string $value): void
    {
        $this->setLegacySnapshotValue('customer.country', $value);
    }

    private function getLegacySnapshotValue(string $path): ?string
    {
        $value = data_get($this->party_snapshot, $path);

        return is_string($value) && $value !== '' ? $value : null;
    }

    private function setLegacySnapshotValue(string $path, mixed $value): void
    {
        $snapshot = $this->party_snapshot;
        data_set($snapshot, $path, $value);
        $this->party_snapshot = $snapshot;
    }
}
