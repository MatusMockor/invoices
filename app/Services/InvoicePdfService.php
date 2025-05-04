<?php

namespace App\Services;

use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoicePdfService
{
    protected $payBySquareService;

    public function __construct(PayBySquareService $payBySquareService)
    {
        $this->payBySquareService = $payBySquareService;
    }

    /**
     * Generate a PDF for an invoice
     *
     * @param  Invoice  $invoice  The invoice to generate a PDF for
     * @param  string|null  $iban  IBAN for payment QR code
     * @return \Barryvdh\DomPDF\PDF
     */
    public function generatePdf(Invoice $invoice, ?string $iban = null)
    {
        // Load the invoice with its relations
        $invoice->load(['company', 'items']);

        // Generate QR code if IBAN is provided
        $qrCode = null;
        if ($iban) {
            try {
                $qrCode = $this->payBySquareService->generateQrCode(
                    $iban,
                    $invoice->total_amount,
                    $invoice->invoice_number,
                    $invoice->currency ?? 'EUR'
                );
            } catch (\Exception $e) {
                \Log::error('QR code generation failed: '.$e->getMessage());
            }
        }

        // Generate PDF
        $pdf = PDF::loadView('invoices.pdf', [
            'invoice' => $invoice,
            'iban' => $iban,
            'qrCode' => $qrCode,
        ]);

        // Set paper size to A4
        $pdf->setPaper('a4');

        return $pdf;
    }

    /**
     * Stream PDF for preview
     *
     * @param  Invoice  $invoice  The invoice to generate a PDF for
     * @param  string|null  $iban  IBAN for payment QR code
     * @return \Illuminate\Http\Response
     */
    public function streamPdf(Invoice $invoice, ?string $iban = null)
    {
        $pdf = $this->generatePdf($invoice, $iban);

        return $pdf->stream("invoice-{$invoice->invoice_number}.pdf");
    }

    /**
     * Download PDF
     *
     * @param  Invoice  $invoice  The invoice to generate a PDF for
     * @param  string|null  $iban  IBAN for payment QR code
     * @return \Illuminate\Http\Response
     */
    public function downloadPdf(Invoice $invoice, ?string $iban = null)
    {
        $pdf = $this->generatePdf($invoice, $iban);

        return $pdf->download("invoice-{$invoice->invoice_number}.pdf");
    }
}
