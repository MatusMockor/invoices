<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\InvoiceTemplate;
use App\Enums\VatPayerStatus;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Services\Interfaces\InvoicePdfService as InvoicePdfServiceContract;
use App\Services\Interfaces\PayBySquare as PayBySquareContract;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Spatie\Browsershot\Browsershot;

final class InvoicePdfService implements InvoicePdfServiceContract
{
    /**
     * InvoicePdfService constructor
     */
    public function __construct(protected PayBySquareContract $payBySquare) {}

    /**
     * Generate a PDF for the given invoice
     *
     * Loads the user's preferred invoice template from settings and renders
     * the PDF with the appropriate template, company data, and QR code.
     *
     * @param  Invoice  $invoice  The invoice to generate PDF for (will eager load relations)
     * @return string The PDF content as a binary string
     */
    public function generatePdf(Invoice $invoice): string
    {
        $invoice->load(['items', 'supplierCompany', 'user.settings']);

        $template = $this->getInvoiceTemplate($invoice);
        $qrCode = $this->generatePayBySquareQrCode($invoice);
        $vatSummary = $this->calculateVatSummary($invoice);
        $isVatPayer = $this->isVatPayer($invoice);

        $html = $this->renderInvoiceHtml($invoice, $template, $qrCode, $vatSummary, $isVatPayer);

        return $this->generatePdfFromHtml($html);
    }

    /**
     * Generate and return a downloadable PDF response
     */
    public function downloadPdf(Invoice $invoice): Response
    {
        $pdf = $this->generatePdf($invoice);

        $filename = 'faktura-'.$invoice->invoice_number.'.pdf';

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    /**
     * Generate and return a streamable PDF response
     */
    public function streamPdf(Invoice $invoice): Response
    {
        $pdf = $this->generatePdf($invoice);

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="faktura-'.$invoice->invoice_number.'.pdf"',
        ]);
    }

    private function getInvoiceTemplate(Invoice $invoice): string
    {
        return $invoice->user->settings?->invoice_template->value ?? InvoiceTemplate::default()->value;
    }

    private function generatePayBySquareQrCode(Invoice $invoice): ?string
    {
        if (! $this->canGenerateQrCode($invoice)) {
            return null;
        }

        $variableSymbol = substr($invoice->invoice_number, 0, 10);

        return $this->payBySquare->generateQrCode(
            $invoice->supplierCompany->iban,
            $invoice->supplierCompany->swift,
            $invoice->total_amount,
            $variableSymbol,
            '',
            '',
            "Invoice {$invoice->invoice_number}",
            $invoice->supplierCompany->name
        );
    }

    private function canGenerateQrCode(Invoice $invoice): bool
    {
        return $invoice->supplierCompany
            && $invoice->supplierCompany->iban
            && $invoice->supplierCompany->swift;
    }

    /**
     * @param  array<int, array{rate: float, base: float, vat_amount: float}>  $vatSummary
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    private function renderInvoiceHtml(
        Invoice $invoice,
        string $template,
        ?string $qrCode,
        array $vatSummary,
        bool $isVatPayer
    ): string {
        return view('invoices.pdf-render', [
            'invoice' => $invoice,
            'qrCode' => $qrCode,
            'template' => $template,
            'vatSummary' => $vatSummary,
            'isVatPayer' => $isVatPayer,
        ])->render();
    }

    private function generatePdfFromHtml(string $html): string
    {
        $browsershot = Browsershot::html($html)
            ->setNodeModulePath(base_path('node_modules'))
            ->format('A4')
            ->margins(0, 0, 0, 0)
            ->showBackground()
            ->waitUntilNetworkIdle();

        $this->configureChromiumIfAvailable($browsershot);

        return $browsershot->pdf();
    }

    private function configureChromiumIfAvailable(Browsershot $browsershot): void
    {
        if (! file_exists('/usr/bin/chromium-browser')) {
            return;
        }

        $browsershot->setChromePath('/usr/bin/chromium-browser')
            ->addChromiumArguments([
                'no-sandbox',
                'disable-setuid-sandbox',
                'disable-dev-shm-usage',
                'disable-gpu',
            ]);
    }

    /**
     * Calculate VAT summary grouped by rate
     *
     * @return array<int, array{rate: float, base: float, vat_amount: float}>
     */
    private function calculateVatSummary(Invoice $invoice): array
    {
        // Only calculate for VAT payers
        if ($invoice->supplier_vat_payer_status === null
            || $invoice->supplier_vat_payer_status === VatPayerStatus::NOT_VAT_PAYER) {
            return [];
        }

        // Ensure items are loaded and not empty
        if (! $invoice->relationLoaded('items') || $invoice->items->isEmpty()) {
            return [];
        }

        $grouped = $invoice->items
            ->filter(static fn (InvoiceItem $item): bool => $item->tax_rate > 0)
            ->groupBy('tax_rate');

        return $grouped->map(function (Collection $items, string|int $rate) use ($invoice): array {
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
     * Determine if invoice supplier is a VAT payer based on snapshot or fallback
     */
    private function isVatPayer(Invoice $invoice): bool
    {
        $vatStatus = $invoice->supplier_vat_payer_status
            ?? $invoice->supplierCompany?->vat_payer_status;

        return $vatStatus !== null
            && $vatStatus !== VatPayerStatus::NOT_VAT_PAYER;
    }
}
