<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\DataTransferObjects\BankAccountData;
use App\DataTransferObjects\PayBySquareData;
use App\DataTransferObjects\PaymentSymbols;
use App\Enums\InvoiceTemplate;
use App\Http\Resources\InvoiceResource;
use App\Models\Invoice;
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
        $invoice = Invoice::with(['items', 'user.settings'])->findOrFail($invoice);

        // Validate template against allowed values
        $requestedTemplate = $request->query('template');
        $defaultTemplate = $invoice->user->settings?->invoice_template->value ?? InvoiceTemplate::default()->value;
        $template = in_array($requestedTemplate, InvoiceTemplate::values(), true)
            ? $requestedTemplate
            : $defaultTemplate;

        $invoice->qr_code = $this->generateQrCode($invoice);

        return view('invoices.pdf-preview', [
            'invoiceData' => (new InvoiceResource($invoice))->toArray($request),
            'template' => $template,
        ]);
    }

    private function generateQrCode(Invoice $invoice): ?string
    {
        $supplierBank = $invoice->getSupplierBankSnapshot();
        $supplierSnapshot = $invoice->getSupplierSnapshot();

        if (! ($supplierBank['iban'] ?? null) || ! ($supplierBank['swift'] ?? null)) {
            return null;
        }

        $data = new PayBySquareData(
            bankAccount: BankAccountData::fromUserCompany($supplierBank['iban'], $supplierBank['swift']),
            amount: $invoice->total_amount,
            symbols: PaymentSymbols::fromInvoice(
                $invoice->variable_symbol,
                $invoice->constant_symbol,
                $invoice->specific_symbol
            ),
            note: 'Faktura '.$invoice->invoice_number.' - '.($supplierSnapshot['name'] ?? 'N/A'),
        );

        return $this->payBySquareService->generateQrCode($data);
    }
}
