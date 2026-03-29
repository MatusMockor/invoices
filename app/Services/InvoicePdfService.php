<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\InvoiceTemplate;
use App\Models\Invoice;
use App\Services\Interfaces\InvoicePdfService as InvoicePdfServiceContract;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\URL;
use Spatie\Browsershot\Browsershot;

final class InvoicePdfService implements InvoicePdfServiceContract
{
    /**
     * Generate a PDF for the given invoice
     *
     * Uses Browsershot to screenshot the React preview page for 1:1 match
     * between preview and PDF output.
     *
     * @param  Invoice  $invoice  The invoice to generate PDF for (will eager load relations)
     * @return string The PDF content as a binary string
     */
    public function generatePdf(Invoice $invoice): string
    {
        $invoice->load(['items', 'user.settings']);

        $template = $this->getInvoiceTemplate($invoice);

        // Generate URL for React preview with secret token
        $previewUrl = route('invoices.pdf-preview', [
            'invoice' => $invoice->id,
            'template' => $template,
            'token' => config('app.pdf_preview_token'),
        ]);

        return $this->generatePdfFromUrl($previewUrl);
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

    /**
     * Generate PDF from React preview URL using Browsershot
     *
     * Uses A4 viewport dimensions (794x1123px) for optimal single-page rendering.
     */
    private function generatePdfFromUrl(string $url): string
    {
        // For Docker: replace hostname with 127.0.0.1 and ensure port 80
        // Apache runs on port 80 inside the container
        $url = preg_replace('#https?://[^/]+#', 'http://127.0.0.1', $url);

        // A4 dimensions at 96 DPI: 794px x 1123px
        $browsershot = Browsershot::url($url)
            ->setNodeModulePath(base_path('node_modules'))
            ->windowSize(794, 1123)
            ->format('A4')
            ->margins(0, 0, 0, 0)
            ->showBackground()
            ->waitUntilNetworkIdle()
            ->setDelay(2000);

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
}
