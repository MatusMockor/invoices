<?php

namespace App\Http\Controllers;

use App\Http\Requests\Invoices\CreateInvoiceRequest;
use App\Http\Requests\Invoices\UpdateInvoiceRequest;
use App\Models\Invoice;
use App\Repositories\Interfaces\BusinessEntityRepository;
use App\Repositories\Interfaces\InvoiceItemRepository;
use App\Repositories\Interfaces\InvoiceRepository;
use App\Services\Interfaces\BusinessEntityDataService;
use App\Services\Interfaces\InvoicePdfService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\View\View;
use Throwable;

class InvoiceController extends Controller
{
    public function __construct(
        protected BusinessEntityDataService $businessEntityDataService,
        protected InvoicePdfService $pdfService,
        protected InvoiceRepository $invoiceRepository,
        protected InvoiceItemRepository $invoiceItemRepository,
        protected BusinessEntityRepository $businessEntityRepository
    ) {
        $this->authorizeResource(Invoice::class);
    }

    public function index(): View
    {
        $invoices = $this->invoiceRepository->getAllForCompanyPaginated(
            auth()->user()->current_company_id
        );

        return view('invoices.index', ['invoices' => $invoices]);
    }

    public function create(): View
    {
        $companies = $this->businessEntityRepository->getAllOrderedByName();

        return view('invoices.create', ['companies' => $companies]);
    }

    public function store(CreateInvoiceRequest $request): RedirectResponse
    {
        $partner = $this->businessEntityDataService->findOrCreateBusinessEntity($request->ico);

        if (! $partner) {
            return back()->withErrors(['ico' => 'Company with the given ICO could not be found.'])->withInput();
        }

        $validated = $request->validated();
        $user = auth()->user();

        // Create invoice
        $invoice = $this->invoiceRepository->create([
            'invoice_number' => $validated['invoice_number'],
            'user_id' => $user->id,
            'issue_date' => $validated['issue_date'],
            'due_date' => $validated['due_date'],
            'delivery_date' => $validated['delivery_date'],
            'business_entity_id' => $partner->id, // This is the recipient company
            'supplier_company_id' => $user->current_company_id, // Set the active company as supplier
            'total_amount' => $validated['total_amount'],
            'currency' => $validated['currency'],
            'constant_symbol' => $validated['constant_symbol'] ?? null,
            'note' => $validated['note'],
            'status' => $validated['status'],
        ]);

        // Prepare items for upsert using array_map
        $this->prepareItemsForUpsertUsing($invoice, $validated['items']);

        return redirect()->route('invoices.index')
            ->with('success', 'Invoice was successfully created');

    }

    public function show(Invoice $invoice): View
    {
        $invoice->load(['businessEntity', 'items']);

        return view('invoices.show', ['invoice' => $invoice]);
    }

    public function edit(Invoice $invoice): View
    {
        $invoice->load(['businessEntity', 'items']);

        return view('invoices.edit', [
            'invoice' => $invoice,
        ]);
    }

    public function update(UpdateInvoiceRequest $request, Invoice $invoice): RedirectResponse
    {
        try {
            // Find or create company based on ICO
            $partner = $this->businessEntityDataService->findOrCreateBusinessEntity($request->ico);

            if (! $partner) {
                return back()->withErrors(['ico' => 'Company with the given ICO could not be found.'])->withInput();
            }

            $user = auth()->user();

            // Update invoice
            $this->invoiceRepository->update($invoice, array_merge(
                $request->validated(),
                [
                    'business_entity_id' => $partner->id, // This is the recipient company
                    'supplier_company_id' => $user->current_company_id, // Keep the active company as supplier
                ]
            ));

            // Prepare items for upsert using array_map
            $this->prepareItemsForUpsertUsing($invoice, $request->validated('items'));

            return redirect()->route('invoices.index')
                ->with('success', 'Invoice was successfully updated');

        } catch (Throwable $e) {
            return back()->withErrors(['message' => 'Error updating invoice: '.$e->getMessage()])->withInput();
        }
    }

    public function destroy(Invoice $invoice): RedirectResponse
    {
        $this->invoiceRepository->delete($invoice);

        return redirect()->route('invoices.index')
            ->with('success', 'Invoice was successfully deleted');
    }

    /**
     * Download the invoice as PDF
     */
    public function downloadPdf(Invoice $invoice): Response
    {
        $this->authorize('view', $invoice);

        return $this->pdfService->downloadPdf($invoice);
    }

    /**
     * View the invoice as PDF in browser
     */
    public function viewPdf(Invoice $invoice): Response
    {
        $this->authorize('view', $invoice);

        return $this->pdfService->streamPdf($invoice);
    }

    protected function prepareItemsForUpsertUsing(Invoice $invoice, $items1): void
    {
        $itemIds = Arr::pluck(
            Arr::where($items1, static function (array $item) {
                return isset($item['id']);
            }),
            'id'
        );

        $this->invoiceItemRepository->deleteItemsNotInIds($invoice->id, $itemIds);

        $items = array_map(static function ($item) use ($invoice) {
            $itemData = [
                'invoice_id' => $invoice->id,
                'description' => $item['description'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'total_price' => $item['quantity'] * $item['unit_price'],
            ];

            if (isset($item['id'])) {
                $itemData['id'] = $item['id'];
            }

            return $itemData;
        }, $items1);

        $this->invoiceItemRepository->upsert(
            $items,
            ['id'],
            ['description', 'quantity', 'unit_price', 'total_price']
        );
    }
}
