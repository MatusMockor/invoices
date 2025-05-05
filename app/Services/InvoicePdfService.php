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
     * @return \Barryvdh\DomPDF\PDF
     */
    public function generatePdf(Invoice $invoice): \Barryvdh\DomPDF\PDF
    {
        $invoice->load(['partner', 'items', 'supplierCompany']);
        
        return PDF::loadView('invoices.pdf', [
            'invoice' => $invoice,
        ]);
    }
    
    /**
     * Generate and return a downloadable PDF response
     *
     * @param Invoice $invoice
     * @return Response
     */
    public function downloadPdf(Invoice $invoice): Response
    {
        $pdf = $this->generatePdf($invoice);
        
        $filename = 'invoice-' . $invoice->invoice_number . '.pdf';
        
        return $pdf->download($filename);
    }
    
    /**
     * Generate and return a streamable PDF response
     *
     * @param Invoice $invoice
     * @return Response
     */
    public function streamPdf(Invoice $invoice): Response
    {
        $pdf = $this->generatePdf($invoice);
        
        return $pdf->stream();
    }
}
