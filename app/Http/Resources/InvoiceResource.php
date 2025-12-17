<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Invoice
 *
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 */
class InvoiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return array_merge(
            $this->getInvoiceMetadata(),
            $this->getSupplierData(),
            $this->getClientData(),
            $this->getRelationshipsData(),
            $this->getVatStatusData(),
            $this->getComputedTextFields(),
        );
    }

    /**
     * Get core invoice metadata (dates, amounts, status).
     *
     * @return array<string, mixed>
     */
    private function getInvoiceMetadata(): array
    {
        return [
            'id' => $this->id,
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
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }

    /**
     * Get supplier (seller) related data.
     *
     * @return array<string, mixed>
     */
    private function getSupplierData(): array
    {
        return [
            'supplier_company_id' => $this->supplier_company_id,
            'supplier_registry_office' => $this->supplier_registry_office,
            'supplier_registry_number' => $this->supplier_registry_number,
        ];
    }

    /**
     * Get client (buyer) company data snapshot.
     *
     * @return array<string, mixed>
     */
    private function getClientData(): array
    {
        return [
            'business_entity_id' => $this->company_id,
            'company_id' => $this->company_id,
            'company_ico' => $this->company_ico,
            'company_dic' => $this->company_dic,
            'company_ic_dph' => $this->company_ic_dph,
            'company_name' => $this->company_name,
            'company_address' => $this->company_address,
            'company_city' => $this->company_city,
            'company_zip' => $this->company_zip,
            'company_country' => $this->company_country,
        ];
    }

    /**
     * Get loaded relationships data.
     *
     * @return array<string, mixed>
     */
    private function getRelationshipsData(): array
    {
        return [
            'business_entity' => new CompanyResource($this->whenLoaded('company')),
            'supplier_company' => new UserCompanyResource($this->whenLoaded('supplierCompany')),
            'items' => InvoiceItemResource::collection($this->whenLoaded('items')),
            'qr_code' => $this->qr_code ?? null,
        ];
    }

    /**
     * Get VAT status related data.
     *
     * @return array<string, mixed>
     */
    private function getVatStatusData(): array
    {
        return [
            'supplier_vat_payer_status' => $this->getEffectiveVatStatus()?->value,
            'supplier_vat_period' => $this->supplier_vat_period,
            'supplier_is_vat_payer' => $this->supplierIsVatPayer(),
            'supplier_is_registered_paragraph_7a' => $this->supplierIsRegisteredParagraph7a(),
            'should_show_vat_fields' => $this->shouldShowVatFields(),
        ];
    }

    /**
     * Get computed text fields for display.
     *
     * @return array<string, mixed>
     */
    private function getComputedTextFields(): array
    {
        return [
            'reverse_charge_text' => $this->reverse_charge_text,
            'tax_exemption_text' => $this->tax_exemption_text,
        ];
    }
}
