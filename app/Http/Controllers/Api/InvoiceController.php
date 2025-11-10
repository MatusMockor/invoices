<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\Invoice\GetLatestInvoiceNumberAction;
use App\Actions\Invoice\InvoiceCreateAction;
use App\Actions\Invoice\InvoiceDeleteAction;
use App\Actions\Invoice\InvoiceUpdateAction;
use App\Actions\Invoice\InvoiceUpdateStatusAction;
use App\DTOs\Invoice\InvoiceCreateDTO;
use App\DTOs\Invoice\InvoiceUpdateDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreInvoiceRequest;
use App\Http\Requests\UpdateInvoiceRequest;
use App\Http\Requests\UpdateInvoiceStatusRequest;
use App\Http\Resources\InvoiceCollection;
use App\Http\Resources\InvoiceResource;
use App\Http\Resources\LatestInvoiceNumberResource;
use App\Models\Invoice;
use App\Repositories\Contracts\InvoiceRepository;
use App\Services\Interfaces\InvoicePdfService;
use App\Services\Interfaces\PayBySquare;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Throwable;

final class InvoiceController extends Controller
{
    public function __construct(
        private readonly InvoiceCreateAction $createAction,
        private readonly InvoiceUpdateAction $updateAction,
        private readonly InvoiceDeleteAction $deleteAction,
        private readonly GetLatestInvoiceNumberAction $getLatestNumberAction,
        private readonly InvoiceUpdateStatusAction $updateStatusAction,
        private readonly InvoicePdfService $pdfService,
        private readonly InvoiceRepository $invoiceRepository,
        private readonly PayBySquare $payBySquareService
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
     * Get the latest invoice number for the current company.
     */
    public function latestNumber(): LatestInvoiceNumberResource
    {
        $latestNumber = $this->getLatestNumberAction->handle(
            auth()->user()->current_company_id
        );

        return new LatestInvoiceNumberResource($latestNumber);
    }

    /**
     * Get a single invoice by ID.
     */
    public function show(Invoice $invoice): InvoiceResource
    {
        $invoice->load(['company', 'supplierCompany', 'items']);
        $invoice->qr_code = $this->generateQrCode($invoice);

        return new InvoiceResource($invoice);
    }

    /**
     * Create a new invoice.
     */
    public function store(StoreInvoiceRequest $request): JsonResponse
    {
        $dto = InvoiceCreateDTO::fromRequest($request->validated());

        $invoice = $this->createAction->handle(
            $dto,
            auth()->id(),
            auth()->user()->current_company_id
        );

        $invoice->load(['supplierCompany']);
        $invoice->qr_code = $this->generateQrCode($invoice);

        return new InvoiceResource($invoice)
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Update an existing invoice.
     *
     * @throws Throwable
     */
    public function update(UpdateInvoiceRequest $request, Invoice $invoice): InvoiceResource
    {
        $dto = InvoiceUpdateDTO::fromRequest($request->validated());

        $updatedInvoice = $this->updateAction->handle(
            $invoice,
            $dto,
            auth()->user()->current_company_id
        );

        $updatedInvoice->load(['supplierCompany']);
        $updatedInvoice->qr_code = $this->generateQrCode($updatedInvoice);

        return new InvoiceResource($updatedInvoice);
    }

    /**
     * Update invoice status.
     */
    public function updateStatus(UpdateInvoiceStatusRequest $request, Invoice $invoice): InvoiceResource
    {
        $this->authorize('update', $invoice);

        $updatedInvoice = $this->updateStatusAction->handle(
            $invoice,
            $request->getStatus()
        );

        return new InvoiceResource($updatedInvoice);
    }

    /**
     * Delete an invoice.
     */
    public function destroy(Invoice $invoice): JsonResponse
    {
        $this->deleteAction->handle($invoice);

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

    /**
     * Generate QR code for invoice payment.
     */
    private function generateQrCode(Invoice $invoice): ?string
    {
        $userCompany = $invoice->supplierCompany;

        if (! $userCompany || ! $userCompany->iban || ! $userCompany->swift) {
            return null;
        }

        return $this->payBySquareService->generateQrCode(
            iban: str_replace(' ', '', $userCompany->iban),
            swift: $userCompany->swift,
            amount: $invoice->total_amount,
            variableSymbol: $invoice->variable_symbol ?? '',
            constantSymbol: $invoice->constant_symbol ?? '',
            specificSymbol: $invoice->specific_symbol ?? '',
            note: 'Faktura '.$invoice->invoice_number,
            recipient: $userCompany->name
        );
    }
}
