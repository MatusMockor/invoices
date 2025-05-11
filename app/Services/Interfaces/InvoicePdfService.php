<?php

namespace App\Services\Interfaces;

use App\Models\Invoice;
use Illuminate\Http\Response;

interface InvoicePdfService
{
    /**
     * Generate a PDF for the given invoice
     *
     * @return mixed
     */
    public function generatePdf(Invoice $invoice);

    /**
     * Generate and return a downloadable PDF response
     */
    public function downloadPdf(Invoice $invoice): Response;

    /**
     * Generate and return a streamable PDF response
     */
    public function streamPdf(Invoice $invoice): Response;
}