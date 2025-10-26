<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\InvoiceTemplate;
use App\Models\Invoice;
use App\Services\Interfaces\InvoicePdfService as InvoicePdfServiceContract;
use App\Services\Interfaces\PayBySquare as PayBySquareContract;
use Illuminate\Http\Response;
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
        $invoice->load(['company', 'items', 'supplierCompany', 'user.settings']);

        // Get user's invoice template preference
        $template = $invoice->user->settings?->invoice_template?->value ?? InvoiceTemplate::default()->value;

        // Generate Pay by Square QR code if we have the necessary data
        $qrCode = null;
        if ($invoice->supplierCompany && $invoice->supplierCompany->iban && $invoice->supplierCompany->swift) {
            // Ensure variable symbol is max 10 characters
            $variableSymbol = str_replace(['INV-', '-'], '', $invoice->invoice_number);
            $variableSymbol = substr($variableSymbol, 0, 10);

            $qrCode = $this->payBySquare->generateQrCode(
                $invoice->supplierCompany->iban,
                $invoice->supplierCompany->swift,
                $invoice->total_amount,
                $variableSymbol, // Limited to 10 characters
                '', // Constant symbol
                '', // Specific symbol
                "Invoice {$invoice->invoice_number}", // Note
                $invoice->supplierCompany->name // Recipient
            );
        }

        // Render the HTML view based on template
        $html = view('invoices.pdf-render', [
            'invoice' => $invoice,
            'qrCode' => $qrCode,
            'template' => $template,
        ])->render();

        // Generate PDF using Browsershot
        $browsershot = Browsershot::html($html)
            ->setNodeModulePath(base_path('node_modules'))
            ->format('A4')
            ->margins(0, 0, 0, 0)
            ->showBackground()
            ->waitUntilNetworkIdle();

        // Use system Chromium if available (for Docker/Alpine)
        if (file_exists('/usr/bin/chromium-browser')) {
            $browsershot->setChromePath('/usr/bin/chromium-browser')
                ->addChromiumArguments([
                    'no-sandbox',
                    'disable-setuid-sandbox',
                    'disable-dev-shm-usage',
                    'disable-gpu',
                ]);
        }

        return $browsershot->pdf();
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
}
