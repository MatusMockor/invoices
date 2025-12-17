<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\InvoiceTemplate;
use App\Enums\VatPayerStatus;
use App\Models\Invoice;
use App\Services\Interfaces\PayBySquare;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

final class PublicInvoiceController extends Controller
{
    public function __construct(
        private readonly PayBySquare $payBySquareService
    ) {}

    /**
     * Show public HTML preview of invoice.
     * This endpoint uses signed URLs for security.
     */
    public function preview(Request $request, Invoice $invoice): View
    {
        // Validate signed URL
        if (! $request->hasValidSignature()) {
            abort(403, 'Neplatný alebo expirovaný odkaz na faktúru.');
        }

        $invoice->load(['items', 'supplierCompany', 'user.settings']);

        // Get user's invoice template preference
        $template = $invoice->user->settings?->invoice_template?->value ?? InvoiceTemplate::default()->value;

        // Generate Pay by Square QR code
        $qrCode = $this->generateQrCode($invoice);

        // Calculate VAT summary
        $vatSummary = $this->calculateVatSummary($invoice);

        // Determine if VAT payer
        $isVatPayer = $this->isVatPayer($invoice);

        return view('invoices.pdf-render', [
            'invoice' => $invoice,
            'qrCode' => $qrCode,
            'template' => $template,
            'vatSummary' => $vatSummary,
            'isVatPayer' => $isVatPayer,
        ]);
    }

    /**
     * Generate QR code for invoice payment.
     */
    private function generateQrCode(Invoice $invoice): ?string
    {
        $userCompany = $invoice->supplierCompany;

        if (! $userCompany || ! $userCompany->iban || ! $userCompany->swift) {
            return null;
        }

        $variableSymbol = substr($invoice->invoice_number, 0, 10);

        return $this->payBySquareService->generateQrCode(
            iban: str_replace(' ', '', $userCompany->iban),
            swift: $userCompany->swift,
            amount: $invoice->total_amount,
            variableSymbol: $variableSymbol,
            constantSymbol: '',
            specificSymbol: '',
            note: 'Faktura '.$invoice->invoice_number,
            recipient: $userCompany->name
        );
    }

    /**
     * Calculate VAT summary grouped by rate.
     *
     * @return array<int, array{rate: float, base: float, vat_amount: float}>
     */
    private function calculateVatSummary(Invoice $invoice): array
    {
        if ($invoice->supplier_vat_payer_status === null
            || $invoice->supplier_vat_payer_status === VatPayerStatus::NOT_VAT_PAYER) {
            return [];
        }

        if (! $invoice->relationLoaded('items') || $invoice->items->isEmpty()) {
            return [];
        }

        $grouped = $invoice->items
            ->filter(static fn ($item) => $item->tax_rate !== null)
            ->groupBy('tax_rate');

        return $grouped->map(static function ($items, $rate) use ($invoice) {
            $base = $items->sum('subtotal');
            $vatAmount = $invoice->reverse_charge ? 0 : $items->sum('tax_amount');

            return [
                'rate' => (float) $rate,
                'base' => round($base, 2),
                'vat_amount' => round($vatAmount, 2),
            ];
        })->sortByDesc('rate')->values()->toArray();
    }

    /**
     * Determine if invoice supplier is a VAT payer.
     */
    private function isVatPayer(Invoice $invoice): bool
    {
        $vatStatus = $invoice->supplier_vat_payer_status
            ?? $invoice->supplierCompany?->vat_payer_status;

        return $vatStatus !== null
            && $vatStatus !== VatPayerStatus::NOT_VAT_PAYER;
    }
}
