@php
    use App\Enums\VatPayerStatus;

    $showIcDph = $invoice->supplier_vat_payer_status !== null
        && $invoice->supplier_vat_payer_status !== VatPayerStatus::NOT_VAT_PAYER;
@endphp

<p class="text-xs text-gray-600">IČO: {{ data_get($invoice->party_snapshot, 'supplier.ico') ?? 'N/A' }}</p>
<p class="text-xs text-gray-600">DIČ: {{ data_get($invoice->party_snapshot, 'supplier.dic') ?? 'N/A' }}</p>

@if($showIcDph && data_get($invoice->party_snapshot, 'supplier.ic_dph'))
    <p class="text-xs text-gray-600">IČ DPH: {{ data_get($invoice->party_snapshot, 'supplier.ic_dph') }}</p>
@endif

@if($invoice->supplier_registry_office || $invoice->supplier_registry_number)
    <p class="text-xs text-gray-600 mt-2">
        {{ $invoice->supplier_registry_office }}{{ $invoice->supplier_registry_office && $invoice->supplier_registry_number ? ', ' : '' }}{{ $invoice->supplier_registry_number ? 'registrácia č. ' . $invoice->supplier_registry_number : '' }}
    </p>
@endif
