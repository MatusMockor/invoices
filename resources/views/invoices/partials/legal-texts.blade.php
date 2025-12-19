@php
    use App\Enums\VatPayerStatus;

    $isVatPayerStatus = $invoice->supplier_vat_payer_status === VatPayerStatus::VAT_PAYER
        || $invoice->supplier_vat_payer_status === VatPayerStatus::VAT_PAYER_PARAGRAPH_7;
    $isRegisteredForVat = $invoice->supplier_vat_payer_status === VatPayerStatus::REGISTERED_PARAGRAPH_7A;
    $darkTheme = $darkTheme ?? false;
@endphp

{{-- Reverse Charge Text --}}
@if($invoice->reverse_charge && ($isVatPayerStatus || $isRegisteredForVat))
    @if($darkTheme)
        <div class="mb-3 p-3 rounded-xl border border-yellow-500/30" style="background: rgba(234, 179, 8, 0.1);">
            <p class="text-sm font-semibold text-yellow-400">
                {{ $invoice->reverse_charge_text ?? 'Prenesenie daňovej povinnosti podľa § 69 ods. 12 zákona o DPH' }}
            </p>
        </div>
    @else
        <div class="legal-text reverse-charge mb-3 p-3 bg-yellow-50 border border-yellow-200 rounded">
            <p class="text-sm font-semibold text-yellow-800">
                {{ $invoice->reverse_charge_text ?? 'Prenesenie daňovej povinnosti podľa § 69 ods. 12 zákona o DPH' }}
            </p>
        </div>
    @endif
@endif

{{-- Tax Exemption Text --}}
@if($invoice->tax_exemption_text ?? false)
    @if($darkTheme)
        <div class="mb-3 p-3 rounded-xl border border-blue-500/30" style="background: rgba(59, 130, 246, 0.1);">
            <p class="text-sm font-semibold text-blue-400">{{ $invoice->tax_exemption_text }}</p>
        </div>
    @else
        <div class="legal-text tax-exemption mb-3 p-3 bg-blue-50 border border-blue-200 rounded">
            <p class="text-sm font-semibold text-blue-800">{{ $invoice->tax_exemption_text }}</p>
        </div>
    @endif
@endif

{{-- Special Text --}}
@if($invoice->special_text)
    @if($darkTheme)
        <div class="mb-3 p-3 rounded-xl border" style="background: rgba(255, 255, 255, 0.05); border-color: rgba(255, 255, 255, 0.1);">
            <p class="text-sm text-slate-300">{{ $invoice->special_text }}</p>
        </div>
    @else
        <div class="legal-text special-text mb-3 p-3 bg-gray-50 border border-gray-200 rounded">
            <p class="text-sm text-gray-700">{{ $invoice->special_text }}</p>
        </div>
    @endif
@endif
