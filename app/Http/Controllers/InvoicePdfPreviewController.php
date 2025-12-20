<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\DataTransferObjects\BankAccountData;
use App\DataTransferObjects\PayBySquareData;
use App\DataTransferObjects\PaymentSymbols;
use App\Enums\InvoiceTemplate;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Services\Interfaces\PayBySquare;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class InvoicePdfPreviewController extends Controller
{
    public function __construct(
        private readonly PayBySquare $payBySquareService
    ) {}

    /**
     * Render invoice preview for PDF screenshot.
     * Uses token authentication for Browsershot access.
     */
    public function show(Request $request, int $invoice): View
    {
        // Validate token using timing-safe comparison
        $expectedToken = config('app.pdf_preview_token');
        $providedToken = (string) $request->query('token', '');

        if (! $expectedToken || ! hash_equals($expectedToken, $providedToken)) {
            abort(403, 'Invalid token');
        }

        // Manually load invoice (route model binding not available without web middleware)
        $invoice = Invoice::with(['items', 'supplierCompany', 'user.settings'])->findOrFail($invoice);

        // Validate template against allowed values
        $requestedTemplate = $request->query('template');
        $defaultTemplate = $invoice->user->settings?->invoice_template->value ?? InvoiceTemplate::default()->value;
        $template = in_array($requestedTemplate, InvoiceTemplate::values(), true)
            ? $requestedTemplate
            : $defaultTemplate;

        $qrCode = $this->generateQrCode($invoice);

        // Prepare invoice data for React
        $invoiceData = $this->prepareInvoiceData($invoice, $qrCode);

        return view('invoices.pdf-preview', [
            'invoiceData' => $invoiceData,
            'template' => $template,
        ]);
    }

    private function prepareInvoiceData(Invoice $invoice, ?string $qrCode): array
    {
        return [
            'id' => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
            'issue_date' => $invoice->issue_date->toDateString(),
            'due_date' => $invoice->due_date->toDateString(),
            'delivery_date' => $invoice->delivery_date->toDateString(),
            'variable_symbol' => $invoice->variable_symbol,
            'constant_symbol' => $invoice->constant_symbol,
            'specific_symbol' => $invoice->specific_symbol,
            'subtotal' => $invoice->subtotal,
            'tax_rate' => $invoice->tax_rate,
            'tax_amount' => $invoice->tax_amount,
            'total_amount' => $invoice->total_amount,
            'currency' => $invoice->currency,
            'notes' => $invoice->notes,
            'reverse_charge_text' => $invoice->reverse_charge_text,
            'tax_exemption_text' => $invoice->tax_exemption_text,
            'supplier_is_vat_payer' => $invoice->supplier_vat_payer_status?->isVatPayer() ?? false,
            'supplier_registry_office' => $invoice->supplier_registry_office,
            'supplier_registry_number' => $invoice->supplier_registry_number,
            'supplier_company' => $this->prepareSupplierCompanyData($invoice),
            'business_entity' => $this->prepareBusinessEntityData($invoice),
            'items' => $this->prepareItemsData($invoice),
            'qr_code' => $qrCode,
        ];
    }

    private function prepareSupplierCompanyData(Invoice $invoice): ?array
    {
        if (! $invoice->supplierCompany) {
            return null;
        }

        return [
            'name' => $invoice->supplierCompany->name,
            'address' => $invoice->supplierCompany->street,
            'city' => $invoice->supplierCompany->city,
            'postal_code' => $invoice->supplierCompany->postal_code,
            'ico' => $invoice->supplierCompany->ico,
            'dic' => $invoice->supplierCompany->dic,
            'ic_dph' => $invoice->supplierCompany->ic_dph,
            'iban' => $invoice->supplierCompany->iban,
        ];
    }

    private function prepareBusinessEntityData(Invoice $invoice): array
    {
        return [
            'name' => $invoice->company_name,
            'address' => $invoice->company_address,
            'city' => $invoice->company_city,
            'postal_code' => $invoice->company_zip,
            'ico' => $invoice->company_ico,
            'dic' => $invoice->company_dic,
            'ic_dph' => $invoice->company_ic_dph,
        ];
    }

    private function prepareItemsData(Invoice $invoice): array
    {
        return $invoice->items->map(static fn (InvoiceItem $item): array => [
            'description' => $item->description,
            'quantity' => $item->quantity,
            'unit_price_without_tax' => $item->unit_price_without_tax,
            'tax_rate' => $item->tax_rate,
            'tax_amount' => $item->tax_amount,
            'subtotal' => $item->subtotal,
            'total_price' => $item->total_price,
        ])->all();
    }

    private function generateQrCode(Invoice $invoice): ?string
    {
        $userCompany = $invoice->supplierCompany;

        if (! $userCompany || ! $userCompany->iban || ! $userCompany->swift) {
            return null;
        }

        $data = new PayBySquareData(
            bankAccount: BankAccountData::fromUserCompany($userCompany->iban, $userCompany->swift),
            amount: $invoice->total_amount,
            symbols: PaymentSymbols::fromInvoice(
                $invoice->variable_symbol,
                $invoice->constant_symbol,
                $invoice->specific_symbol
            ),
            note: 'Faktura '.$invoice->invoice_number.' - '.$userCompany->name,
        );

        return $this->payBySquareService->generateQrCode($data);
    }
}
