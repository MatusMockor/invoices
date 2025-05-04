<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Services\CompanyDataService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    protected $companyDataService;

    public function __construct(CompanyDataService $companyDataService)
    {
        $this->companyDataService = $companyDataService;
    }

    public function index()
    {
        $invoices = Invoice::with('company')->latest()->paginate(10);

        return view('invoices.index', compact('invoices'));
    }

    public function create()
    {
        $companies = Company::orderBy('name')->get();

        return view('invoices.create', compact('companies'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'ico' => 'required|string|max:8',
            'invoice_number' => 'required|string|max:20|unique:invoices',
            'issue_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:issue_date',
            'total_amount' => 'required|numeric|min:0',
            'currency' => 'required|string|max:3',
            'note' => 'nullable|string',
            'status' => 'required|string|in:draft,sent,paid,cancelled',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string|max:255',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            // Find or create company based on ICO
            $company = $this->companyDataService->findOrCreateCompany($request->ico);

            if (! $company) {
                return back()->withErrors(['ico' => 'Spoločnosť s daným IČO sa nepodarilo nájsť.'])->withInput();
            }

            // Create invoice
            $invoice = Invoice::create([
                'invoice_number' => $validated['invoice_number'],
                'issue_date' => $validated['issue_date'],
                'due_date' => $validated['due_date'],
                'company_id' => $company->id,
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

            DB::commit();

            return redirect()->route('invoices.index')
                ->with('success', 'Faktúra bola úspešne vytvorená');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors(['message' => 'Nastala chyba pri vytváraní faktúry: '.$e->getMessage()])->withInput();
        }
    }

    public function show(Invoice $invoice)
    {
        $invoice->load(['company', 'items']);

        return view('invoices.show', compact('invoice'));
    }

    public function edit(Invoice $invoice)
    {
        $invoice->load(['company', 'items']);
        $companies = Company::orderBy('name')->get();

        return view('invoices.edit', compact('invoice', 'companies'));
    }

    public function update(Request $request, Invoice $invoice)
    {
        $validated = $request->validate([
            'ico' => 'required|string|max:8',
            'invoice_number' => 'required|string|max:20|unique:invoices,invoice_number,'.$invoice->id,
            'issue_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:issue_date',
            'total_amount' => 'required|numeric|min:0',
            'currency' => 'required|string|max:3',
            'note' => 'nullable|string',
            'status' => 'required|string|in:draft,sent,paid,cancelled',
            'items' => 'required|array|min:1',
            'items.*.id' => 'nullable|exists:invoice_items,id',
            'items.*.description' => 'required|string|max:255',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            // Find or create company based on ICO
            $company = $this->companyDataService->findOrCreateCompany($request->ico);

            if (! $company) {
                return back()->withErrors(['ico' => 'Spoločnosť s daným IČO sa nepodarilo nájsť.'])->withInput();
            }

            // Update invoice
            $invoice->update([
                'invoice_number' => $validated['invoice_number'],
                'issue_date' => $validated['issue_date'],
                'due_date' => $validated['due_date'],
                'company_id' => $company->id,
                'total_amount' => $validated['total_amount'],
                'currency' => $validated['currency'],
                'note' => $validated['note'],
                'status' => $validated['status'],
            ]);

            // Delete old items
            $invoice->items()->delete();

            // Create new invoice items
            foreach ($validated['items'] as $item) {
                InvoiceItem::create([
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

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors(['message' => 'Nastala chyba pri aktualizácii faktúry: '.$e->getMessage()])->withInput();
        }
    }

    public function destroy(Invoice $invoice)
    {
        $invoice->delete();

        return redirect()->route('invoices.index')
            ->with('success', 'Faktúra bola úspešne vymazaná');
    }
}
