<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Invoice
 */
class InvoiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'company_id' => $this->supplier_company_id,
            'business_entity_id' => $this->business_entity_id,
            'invoice_number' => $this->invoice_number,
            'issue_date' => $this->issue_date->format('Y-m-d'),
            'due_date' => $this->due_date->format('Y-m-d'),
            'delivery_date' => $this->delivery_date->format('Y-m-d'),
            'variable_symbol' => $this->variable_symbol ?? null,
            'constant_symbol' => $this->constant_symbol,
            'specific_symbol' => $this->specific_symbol ?? null,
            'total_amount' => $this->total_amount,
            'total_amount_without_vat' => $this->calculateTotalWithoutVat(),
            'vat_amount' => $this->calculateVatAmount(),
            'currency' => $this->currency,
            'notes' => $this->note,
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
        ];
    }

    private function calculateTotalWithoutVat(): float
    {
        if (! $this->relationLoaded('items')) {
            return 0;
        }

        return $this->items->sum('total_price');
    }

    private function calculateVatAmount(): float
    {
        return 0;
    }
}
