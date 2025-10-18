<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreInvoiceRequest;
use App\Http\Requests\UpdateInvoiceRequest;
use App\Http\Resources\InvoiceCollection;
use App\Http\Resources\InvoiceResource;
use App\Models\Invoice;
use App\Repositories\Interfaces\InvoiceRepository;
use App\Services\Interfaces\InvoicePdfService;
use App\Services\Interfaces\InvoiceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class InvoiceController extends Controller
{
    public function __construct(
        private readonly InvoiceService $invoiceService,
        private readonly InvoicePdfService $pdfService,
        private readonly InvoiceRepository $invoiceRepository
    ) {
        $this->authorizeResource(Invoice::class);
    }

    /**
     * Get all invoices for the current company.
     */
    public function index(Request $request): InvoiceCollection
    {
        $invoices = $this->invoiceRepository->getAllForCompanyPaginated(
            auth()->user()->current_company_id,
            $request->input('per_page', 15)
        );

        return new InvoiceCollection($invoices);
    }

    /**
     * Get a single invoice by ID.
     */
    public function show(Invoice $invoice): InvoiceResource
    {
        $invoice->load(['businessEntity', 'supplierCompany', 'items']);

        return new InvoiceResource($invoice);
    }

    /**
     * Create a new invoice.
     */
    public function store(StoreInvoiceRequest $request): JsonResponse
    {
        $invoice = $this->invoiceService->createInvoice(
            $request->validated(),
            auth()->id(),
            auth()->user()->current_company_id
        );

        return new InvoiceResource($invoice)
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Update an existing invoice.
     */
    public function update(UpdateInvoiceRequest $request, Invoice $invoice): InvoiceResource
    {
        $updatedInvoice = $this->invoiceService->updateInvoice(
            $invoice,
            $request->validated(),
            auth()->user()->current_company_id
        );

        return new InvoiceResource($updatedInvoice);
    }

    /**
     * Delete an invoice.
     */
    public function destroy(Invoice $invoice): JsonResponse
    {
        $this->invoiceRepository->delete($invoice);

        return response()->json([
            'message' => 'Invoice deleted successfully',
        ]);
    }

    /**
     * Download invoice as PDF.
     */
    public function downloadPdf(Invoice $invoice): Response
    {
        $this->authorize('view', $invoice);

        return $this->pdfService->downloadPdf($invoice);
    }

    /**
     * View invoice as PDF in browser.
     */
    public function viewPdf(Invoice $invoice): Response
    {
        $this->authorize('view', $invoice);

        return $this->pdfService->streamPdf($invoice);
    }
}
