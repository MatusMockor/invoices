<?php

namespace App\Http\Controllers;

use App\Http\Requests\Invoices\CreateInvoiceRequest;
use App\Http\Requests\Invoices\UpdateInvoiceRequest;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Partner;
use App\Services\InvoicePdfService;
use App\Services\PartnerDataService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Throwable;

class InvoiceController extends Controller
{
    public function __construct(
        protected PartnerDataService $companyDataService,
        protected InvoicePdfService $pdfService
    ) {}

    public function index(): View
    {
        $invoices = Invoice::with('partner')
            ->where('supplier_company_id', auth()->user()->current_company_id)
            ->latest()
            ->paginate(10);

        return view('invoices.index', compact('invoices'));
    }

    public function create(): View
    {
        $companies = Partner::query()->orderBy('name')->get();

        return view('invoices.create', compact('companies'));
    }

    public function store(CreateInvoiceRequest $request): RedirectResponse
    {
        $partner = $this->companyDataService->findOrCreateCompany($request->ico);

        if (! $partner) {
            return back()->withErrors(['ico' => 'Spoločnosť s daným IČO sa nepodarilo nájsť.'])->withInput();
        }

        $validated = $request->validated();
        $user = auth()->user();

        // Create invoice
        $invoice = Invoice::create([
            'invoice_number' => $validated['invoice_number'],
            'user_id' => $user->id,
            'issue_date' => $validated['issue_date'],
            'due_date' => $validated['due_date'],
            'partner_id' => $partner->id, // This is the recipient company
            'supplier_company_id' => $user->current_company_id, // Set the active company as supplier
            'total_amount' => $validated['total_amount'],
            'currency' => $validated['currency'],
            'note' => $validated['note'],
            'status' => $validated['status'],
        ]);

        // Create invoice items
        foreach ($validated['items'] as $item) {
            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'description' => $item['description'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'total_price' => $item['quantity'] * $item['unit_price'],
            ]);
        }

        return redirect()->route('invoices.index')
            ->with('success', 'Faktúra bola úspešne vytvorená');

    }

    public function show(Invoice $invoice): View
    {
        $invoice->load(['partner', 'items']);

        return view('invoices.show', compact('invoice'));
    }

    public function edit(Invoice $invoice): View
    {
        $invoice->load(['partner', 'items']);
        $companies = Partner::query()->orderBy('name')->get();

        return view('invoices.edit', compact('invoice', 'companies'));
    }

    public function update(UpdateInvoiceRequest $request, Invoice $invoice): RedirectResponse
    {
        try {
            DB::beginTransaction();

            // Find or create company based on ICO
            $company = $this->companyDataService->findOrCreateCompany($request->ico);

            if (! $company) {
                return back()->withErrors(['ico' => 'Spoločnosť s daným IČO sa nepodarilo nájsť.'])->withInput();
            }

            $validated = $request->validated();

            $user = auth()->user();

            // Update invoice
            $invoice->update([
                'invoice_number' => $validated['invoice_number'],
                'issue_date' => $validated['issue_date'],
                'due_date' => $validated['due_date'],
                'company_id' => $company->id, // This is the recipient company
                'supplier_id' => $user->current_company_id, // Keep the active company as supplier
                'total_amount' => $validated['total_amount'],
                'currency' => $validated['currency'],
                'note' => $validated['note'],
                'status' => $validated['status'],
            ]);

            // Delete old items
            $invoice->items()->delete();

            // Create new invoice items
            foreach ($validated['items'] as $item) {
                InvoiceItem::query()->create([
                    'invoice_id' => $invoice->id,
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total_price' => $item['quantity'] * $item['unit_price'],
                ]);
            }

            DB::commit();

            return redirect()->route('invoices.index')
                ->with('success', 'Faktúra bola úspešne aktualizovaná');

        } catch (Throwable $e) {
            DB::rollBack();

            return back()->withErrors(['message' => 'Nastala chyba pri aktualizácii faktúry: '.$e->getMessage()])->withInput();
        }
    }

    public function destroy(Invoice $invoice): RedirectResponse
    {
        $invoice->delete();

        return redirect()->route('invoices.index')
            ->with('success', 'Faktúra bola úspešne vymazaná');
    }

    /**
     * Download the invoice as PDF
     */
    public function downloadPdf(Invoice $invoice): Response
    {
        return $this->pdfService->downloadPdf($invoice);
    }

    /**
     * View the invoice as PDF in browser
     */
    public function viewPdf(Invoice $invoice): Response
    {
        return $this->pdfService->streamPdf($invoice);
    }
}
