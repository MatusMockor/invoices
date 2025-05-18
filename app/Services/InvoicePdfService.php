<?php

namespace App\Services;

use App\Models\Invoice;
use App\Services\Interfaces\InvoicePdfService as InvoicePdfServiceContract;
use App\Services\Interfaces\PayBySquare as PayBySquareContract;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class InvoicePdfService implements InvoicePdfServiceContract
{
    /**
     * InvoicePdfService constructor
     */
    public function __construct(protected PayBySquareContract $payBySquare) {}

    /**
     * Generate a PDF for the given invoice
     *
     * @return mixed
     */
    public function generatePdf(Invoice $invoice)
    {
        $invoice->load(['businessEntity', 'items', 'supplierCompany']);

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

        $pdf = PDF::loadView('invoices.pdf', [
            'invoice' => $invoice,
            'qrCode' => $qrCode,
        ]);

        return $pdf;
    }

    /**
     * Generate and return a downloadable PDF response
     */
    public function downloadPdf(Invoice $invoice): Response
    {
        $pdf = $this->generatePdf($invoice);

        $filename = 'invoice-'.$invoice->invoice_number.'.pdf';

        return $pdf->download($filename);
    }

    /**
     * Generate and return a streamable PDF response
     */
    public function streamPdf(Invoice $invoice): Response
    {
        $pdf = $this->generatePdf($invoice);

        return $pdf->stream();
    }
}
