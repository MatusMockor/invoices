<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Invoice;
use App\Services\PayBySquareService;
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

            'business_entity' => new BusinessEntityResource($this->whenLoaded('businessEntity')),
            'supplier_company' => new CompanyResource($this->whenLoaded('supplierCompany')),
            'items' => InvoiceItemResource::collection($this->whenLoaded('items')),
            'qr_code' => $this->when($this->relationLoaded('supplierCompany'), function () {
                return $this->generateQrCode();
            }),
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
        if (! $this->relationLoaded('items')) {
            return 0;
        }

        return $this->items->sum(function ($item) {
            return $item->total_price * ($item->vat_rate / 100);
        });
    }

    private function generateQrCode(): ?string
    {
        $company = $this->supplierCompany;

        if (! $company || ! $company->iban || ! $company->swift) {
            return null;
        }

        $payBySquareService = app(PayBySquareService::class);

        return $payBySquareService->generateQrCode(
            iban: str_replace(' ', '', $company->iban),
            swift: $company->swift,
            amount: $this->total_amount,
            variableSymbol: str_replace(['INV-', '-'], '', $this->invoice_number),
            constantSymbol: $this->constant_symbol ?? '',
            specificSymbol: $this->specific_symbol ?? '',
            note: 'Faktura '.$this->invoice_number,
            recipient: $company->name
        );
    }
}
