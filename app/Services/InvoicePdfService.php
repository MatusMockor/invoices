<?php

namespace App\Services;

use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class InvoicePdfService
{
    /**
     * Generate a PDF for the given invoice
     *
     * @param Invoice $invoice
     * @return mixed
     */
    public function generatePdf(Invoice $invoice)
    {
        $invoice->load(['partner', 'items', 'supplierCompany']);

        $pdf = PDF::loadView('invoices.pdf', [
            'invoice' => $invoice,
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
