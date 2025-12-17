<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\URL;

/**
 * @mixin Invoice
 */
final class InvoiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'supplier_company_id' => $this->supplier_company_id,
            // Supplier registry data snapshot
            'supplier_registry_office' => $this->supplier_registry_office,
            'supplier_registry_number' => $this->supplier_registry_number,
            'business_entity_id' => $this->business_entity_id,
            'invoice_number' => $this->invoice_number,
            'issue_date' => $this->issue_date->format('Y-m-d'),
            'due_date' => $this->due_date->format('Y-m-d'),
            'delivery_date' => $this->delivery_date->format('Y-m-d'),
            'variable_symbol' => $this->variable_symbol ?? null,
            'constant_symbol' => $this->constant_symbol,
            'specific_symbol' => $this->specific_symbol ?? null,
            'subtotal' => $this->subtotal,
            'tax_amount' => $this->tax_amount,
            'tax_rate' => $this->tax_rate,
            'total_amount' => $this->total_amount,
            'discount_amount' => $this->discount_amount,
            'discount_percentage' => $this->discount_percentage,
            'reverse_charge' => $this->reverse_charge ?? false,
            'tax_exemption_reason' => $this->tax_exemption_reason,
            'special_text' => $this->special_text,
            'currency' => $this->currency,
            'notes' => $this->notes ?? $this->note,
            'status' => $this->status,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),

            // Company data stored directly on invoice (snapshot at creation time)
            'company_id' => $this->company_id,
            'company_ico' => $this->company_ico,
            'company_dic' => $this->company_dic,
            'company_ic_dph' => $this->company_ic_dph,
            'company_name' => $this->company_name,
            'company_address' => $this->company_address,
            'company_city' => $this->company_city,
            'company_zip' => $this->company_zip,
            'company_country' => $this->company_country,

            'business_entity' => new CompanyResource($this->whenLoaded('company')),
            'supplier_company' => new UserCompanyResource($this->whenLoaded('supplierCompany')),
            'items' => InvoiceItemResource::collection($this->whenLoaded('items')),
            'qr_code' => $this->qr_code ?? null,

            // Supplier VAT status (snapshot if available, fallback to supplier company)
            'supplier_vat_payer_status' => $this->getEffectiveVatStatus()?->value,
            'supplier_vat_period' => $this->supplier_vat_period,
            // Helper flags based on effective VAT status
            'supplier_is_vat_payer' => $this->supplierIsVatPayer(),
            'supplier_is_registered_paragraph_7a' => $this->supplierIsRegisteredParagraph7a(),
            'should_show_vat_fields' => $this->shouldShowVatFields(),

            // Computed text fields for display
            'reverse_charge_text' => $this->reverse_charge_text,
            'tax_exemption_text' => $this->tax_exemption_text,

            // Flattened supplier object for MCP preview (only when relation loaded)
            'supplier' => $this->when($this->relationLoaded('supplierCompany'), function (): array {
                return $this->formatSupplierForPreview();
            }),

            // Flattened customer object from invoice snapshot for MCP preview
            'customer' => $this->formatCustomerForPreview(),

            // VAT summary grouped by rate for MCP preview (only when items relation loaded)
            'vat_summary' => $this->when($this->relationLoaded('items'), function (): array {
                return $this->calculateVatSummary();
            }),

            // Public preview URL (signed, valid for 1 hour) for MCP/ChatGPT iframe embedding
            'preview_url' => $this->generatePreviewUrl(),
        ];
    }

    /**
     * Format supplier company data for MCP preview.
     *
     * @return array<string, mixed>
     */
    private function formatSupplierForPreview(): array
    {
        $supplier = $this->supplierCompany;

        if (! $supplier) {
            return [];
        }

        return [
            'name' => $supplier->name,
            'street' => $supplier->street,
            'city' => $supplier->city,
            'postal_code' => $supplier->postal_code,
            'country' => $supplier->country ?? 'Slovensko',
            'ico' => $supplier->ico,
            'dic' => $supplier->dic,
            'ic_dph' => $supplier->ic_dph,
            'iban' => $supplier->iban,
            'swift' => $supplier->swift,
            'is_vat_payer' => $this->supplierIsVatPayer(),
            'vat_payer_status' => $this->getEffectiveVatStatus()?->value,
        ];
    }

    /**
     * Format customer data for MCP preview (from invoice snapshot).
     *
     * @return array<string, mixed>
     */
    private function formatCustomerForPreview(): array
    {
        return [
            'name' => $this->company_name,
            'street' => $this->company_address,
            'city' => $this->company_city,
            'postal_code' => $this->company_zip,
            'country' => $this->company_country ?? 'Slovensko',
            'ico' => $this->company_ico,
            'dic' => $this->company_dic,
            'ic_dph' => $this->company_ic_dph,
        ];
    }

    /**
     * Generate signed URL for public invoice preview.
     * URL is valid for 1 hour.
     */
    private function generatePreviewUrl(): string
    {
        return URL::temporarySignedRoute(
            'invoices.preview',
            now()->addHour(),
            ['invoice' => $this->id]
        );
    }

    /**
     * Calculate VAT summary grouped by tax rate for MCP preview.
     *
     * @return array<int, array{rate: int, base: float, vat_amount: float}>
     */
    private function calculateVatSummary(): array
    {
        if (! $this->relationLoaded('items') || $this->items->isEmpty()) {
            return [];
        }

        // Group items by tax rate and sum
        $summary = [];

        /** @var InvoiceItem $item */
        foreach ($this->items as $item) {
            $rate = (int) $item->tax_rate;

            if (! isset($summary[$rate])) {
                $summary[$rate] = [
                    'rate' => $rate,
                    'base' => 0.0,
                    'vat_amount' => 0.0,
                ];
            }

            $summary[$rate]['base'] += (float) $item->subtotal;
            $summary[$rate]['vat_amount'] += (float) $item->tax_amount;
        }

        // Convert to indexed array and sort by rate ascending
        $result = array_values($summary);
        usort($result, static fn (array $a, array $b): int => $a['rate'] <=> $b['rate']);

        return $result;
    }
}
