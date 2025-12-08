@php
    use App\Enums\VatPayerStatus;

    $showVatFields = ($isVatPayer ?? false) || (
        $invoice->supplier_vat_payer_status !== null
        && $invoice->supplier_vat_payer_status !== VatPayerStatus::NOT_VAT_PAYER
    );
    $vatSummary = $vatSummary ?? [];
    $darkTheme = $darkTheme ?? false;
@endphp

@if($showVatFields && !empty($vatSummary))
<div class="vat-summary mt-4">
    @if($darkTheme)
        <h4 class="font-semibold text-sm mb-2 text-cyan-400">Rekapitulácia DPH</h4>
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b" style="border-color: rgba(255, 255, 255, 0.1);">
                    <th class="text-left py-2 px-3 text-slate-400">Sadzba DPH</th>
                    <th class="text-right py-2 px-3 text-slate-400">Základ dane</th>
                    <th class="text-right py-2 px-3 text-slate-400">DPH</th>
                </tr>
            </thead>
            <tbody>
                @foreach($vatSummary as $item)
                <tr class="border-b" style="border-color: rgba(255, 255, 255, 0.05);">
                    <td class="py-2 px-3 text-white">{{ number_format($item['rate'], 0) }}%</td>
                    <td class="text-right py-2 px-3 text-white">{{ number_format($item['base'], 2, ',', ' ') }} {{ $invoice->currency ?? 'EUR' }}</td>
                    <td class="text-right py-2 px-3 text-white">{{ number_format($item['vat_amount'], 2, ',', ' ') }} {{ $invoice->currency ?? 'EUR' }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="font-semibold" style="background: rgba(6, 182, 212, 0.1);">
                    <td class="py-2 px-3 text-cyan-400">Celkom</td>
                    <td class="text-right py-2 px-3 text-cyan-400">{{ number_format($invoice->subtotal ?? 0, 2, ',', ' ') }} {{ $invoice->currency ?? 'EUR' }}</td>
                    <td class="text-right py-2 px-3 text-cyan-400">{{ number_format($invoice->tax_amount ?? 0, 2, ',', ' ') }} {{ $invoice->currency ?? 'EUR' }}</td>
                </tr>
            </tfoot>
        </table>
    @else
        <h4 class="font-semibold text-sm mb-2">Rekapitulácia DPH</h4>
        <table class="w-full text-sm border border-gray-300">
            <thead>
                <tr class="bg-gray-100">
                    <th class="text-left py-2 px-3 border-b">Sadzba DPH</th>
                    <th class="text-right py-2 px-3 border-b">Základ dane</th>
                    <th class="text-right py-2 px-3 border-b">DPH</th>
                </tr>
            </thead>
            <tbody>
                @foreach($vatSummary as $item)
                <tr class="border-b border-gray-200">
                    <td class="py-2 px-3">{{ number_format($item['rate'], 0) }}%</td>
                    <td class="text-right py-2 px-3">{{ number_format($item['base'], 2, ',', ' ') }} {{ $invoice->currency ?? 'EUR' }}</td>
                    <td class="text-right py-2 px-3">{{ number_format($item['vat_amount'], 2, ',', ' ') }} {{ $invoice->currency ?? 'EUR' }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="font-semibold bg-gray-50">
                    <td class="py-2 px-3">Celkom</td>
                    <td class="text-right py-2 px-3">{{ number_format($invoice->subtotal ?? 0, 2, ',', ' ') }} {{ $invoice->currency ?? 'EUR' }}</td>
                    <td class="text-right py-2 px-3">{{ number_format($invoice->tax_amount ?? 0, 2, ',', ' ') }} {{ $invoice->currency ?? 'EUR' }}</td>
                </tr>
            </tfoot>
        </table>
    @endif
</div>
@endif
