<div class="text-white p-4 min-h-[297mm] flex flex-col" id="invoice-content" style="background: linear-gradient(to bottom right, #0f172a 0%, #1e293b 50%, #0f172a 100%);">
    @php
        use App\Enums\VatPayerStatus;
        // Use snapshot field from invoice, fallback to company status for backwards compatibility
        $vatStatus = $invoice->supplier_vat_payer_status ?? $invoice->supplierCompany->vat_payer_status ?? null;
        $isVatPayer = $vatStatus !== null && $vatStatus !== VatPayerStatus::NOT_VAT_PAYER;
        $showIcDph = $isVatPayer;
    @endphp

    <!-- Bold Header with Accent -->
    <div class="mb-3 pb-3 border-b-2 border-cyan-500">
        <div class="flex justify-between items-start">
            <div>
                <h1 class="text-2xl font-bold text-white mb-1">InvoiceHub</h1>
                <div class="h-1 w-20 rounded-full" style="background: linear-gradient(to right, #06b6d4, #3b82f6);"></div>
            </div>
            <div class="text-right">
                <p class="text-[10px] text-cyan-400 uppercase tracking-widest mb-1 font-semibold">FAKTÚRA</p>
                <p class="text-xl font-bold text-cyan-400">
                    {{ $invoice->invoice_number }}
                </p>
            </div>
        </div>
    </div>

    <!-- Client and Supplier Cards -->
    <div class="grid grid-cols-2 gap-3 mb-3">
        <!-- Supplier Card -->
        <div class="rounded-xl p-3 border" style="background: rgba(255, 255, 255, 0.05); border-color: rgba(255, 255, 255, 0.1); backdrop-filter: blur(10px);">
            <div class="flex items-center gap-2 mb-2">
                <div class="h-6 w-1 bg-cyan-500 rounded-full"></div>
                <p class="text-[10px] font-bold text-cyan-400 uppercase tracking-wider">Dodávateľ</p>
            </div>
            <p class="font-bold text-white mb-1 text-base">{{ $invoice->supplierCompany->name ?? 'N/A' }}</p>
            <p class="text-xs text-slate-300 mb-0.5">{{ $invoice->supplierCompany->street ?? '' }}</p>
            <p class="text-xs text-slate-300 mb-2">{{ $invoice->supplierCompany->postal_code ?? '' }} {{ $invoice->supplierCompany->city ?? '' }}</p>
            <div class="space-y-0.5 pt-2 border-t" style="border-color: rgba(255, 255, 255, 0.1);">
                <p class="text-[10px] text-slate-400">IČO: <span class="text-white font-semibold">{{ $invoice->supplierCompany->ico ?? 'N/A' }}</span></p>
                <p class="text-[10px] text-slate-400">DIČ: <span class="text-white font-semibold">{{ $invoice->supplierCompany->dic ?? 'N/A' }}</span></p>
                @if($showIcDph && ($invoice->supplierCompany->ic_dph ?? false))
                    <p class="text-[10px] text-slate-400">IČ DPH: <span class="text-white font-semibold">{{ $invoice->supplierCompany->ic_dph }}</span></p>
                @endif
                @if($invoice->supplier_registry_office || $invoice->supplier_registry_number)
                    <p class="text-[10px] text-slate-400 mt-2">
                        <span class="text-white font-semibold">{{ $invoice->supplier_registry_office }}{{ $invoice->supplier_registry_office && $invoice->supplier_registry_number ? ', ' : '' }}{{ $invoice->supplier_registry_number ? 'registrácia č. ' . $invoice->supplier_registry_number : '' }}</span>
                    </p>
                @endif
            </div>
        </div>

        <!-- Client Card -->
        <div class="rounded-xl p-3 border border-cyan-500/30" style="background: linear-gradient(to bottom right, rgba(6, 182, 212, 0.1), rgba(59, 130, 246, 0.1)); backdrop-filter: blur(10px);">
            <div class="flex items-center gap-2 mb-2">
                <div class="h-6 w-1 bg-cyan-500 rounded-full"></div>
                <p class="text-[10px] font-bold text-cyan-400 uppercase tracking-wider">Odberateľ</p>
            </div>
            <p class="font-bold text-white mb-1 text-base">{{ $invoice->company_name ?? 'N/A' }}</p>
            <p class="text-xs text-slate-300 mb-0.5">{{ $invoice->company_address ?? '' }}</p>
            <p class="text-xs text-slate-300 mb-2">{{ $invoice->company_zip ?? '' }} {{ $invoice->company_city ?? '' }}</p>
            <div class="space-y-0.5 pt-2 border-t border-cyan-500/30">
                <p class="text-[10px] text-slate-400">IČO: <span class="text-white font-semibold">{{ $invoice->company_ico ?? 'N/A' }}</span></p>
                <p class="text-[10px] text-slate-400">DIČ: <span class="text-white font-semibold">{{ $invoice->company_dic ?? 'N/A' }}</span></p>
                @if($invoice->company_ic_dph ?? false)
                    <p class="text-[10px] text-slate-400">IČ DPH: <span class="text-white font-semibold">{{ $invoice->company_ic_dph }}</span></p>
                @endif
            </div>
        </div>
    </div>

    <!-- Payment Details Section -->
    <div class="grid gap-3 mb-3" style="grid-template-columns: 1fr auto;">
        <div class="rounded-xl p-3 border" style="background: rgba(255, 255, 255, 0.05); border-color: rgba(255, 255, 255, 0.1); backdrop-filter: blur(10px);">
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <p class="text-[10px] text-cyan-400 uppercase tracking-wider mb-1 font-semibold">Vystavenie</p>
                    <p class="text-sm font-bold text-white">{{ \Carbon\Carbon::parse($invoice->issue_date)->format('d.m.Y') }}</p>
                </div>
                <div>
                    <p class="text-[10px] text-cyan-400 uppercase tracking-wider mb-1 font-semibold">Splatnosť</p>
                    <p class="text-sm font-bold text-white">{{ \Carbon\Carbon::parse($invoice->due_date)->format('d.m.Y') }}</p>
                </div>
                @if($invoice->supplierCompany->iban ?? false)
                    <div>
                        <p class="text-[10px] text-cyan-400 uppercase tracking-wider mb-1 font-semibold">Číslo účtu</p>
                        <p class="text-[10px] font-mono font-semibold text-white">{{ $invoice->supplierCompany->iban }}</p>
                    </div>
                @endif
                @if($invoice->variable_symbol ?? false)
                    <div>
                        <p class="text-[10px] text-cyan-400 uppercase tracking-wider mb-1 font-semibold">Var. symbol</p>
                        <p class="text-[10px] font-mono font-semibold text-white">{{ $invoice->variable_symbol }}</p>
                    </div>
                @endif
                @if($invoice->constant_symbol)
                    <div>
                        <p class="text-[10px] text-cyan-400 uppercase tracking-wider mb-1 font-semibold">Konšt. symbol</p>
                        <p class="text-[10px] font-mono font-semibold text-white">{{ $invoice->constant_symbol }}</p>
                    </div>
                @endif
                @if($invoice->specific_symbol)
                    <div>
                        <p class="text-[10px] text-cyan-400 uppercase tracking-wider mb-1 font-semibold">Špec. symbol</p>
                        <p class="text-[10px] font-mono font-semibold text-white">{{ $invoice->specific_symbol }}</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- QR Code with Glow -->
        @if($qrCode ?? false)
            <div class="flex flex-col items-center justify-center">
                <div class="bg-white p-2 rounded-xl" style="box-shadow: 0 0 30px rgba(6, 182, 212, 0.3);">
                    <img src="{{ $qrCode }}" alt="Pay by Square QR Code" class="w-[80px] h-[80px]">
                </div>
                <p class="text-[10px] text-cyan-400 mt-2 font-semibold">Pay by Square</p>
            </div>
        @endif
    </div>

    <!-- Legal Texts (dark theme) -->
    @include('invoices.partials.legal-texts', ['darkTheme' => true])

    <!-- Items Table -->
    <div class="mb-3 rounded-xl overflow-hidden border" style="background: rgba(255, 255, 255, 0.05); border-color: rgba(255, 255, 255, 0.1); backdrop-filter: blur(10px);">
        <table class="w-full">
            <thead style="background: linear-gradient(to right, rgba(6, 182, 212, 0.2), rgba(59, 130, 246, 0.2));">
                <tr>
                    <th class="text-left py-2 px-2.5 text-[10px] font-bold text-cyan-400 uppercase tracking-wider">Popis</th>
                    <th class="text-right py-2 px-2.5 w-20 text-[10px] font-bold text-cyan-400 uppercase tracking-wider">Počet</th>
                    @if($isVatPayer)
                        <th class="text-right py-2 px-2.5 w-24 text-[10px] font-bold text-cyan-400 uppercase tracking-wider">Cena/ks</th>
                        <th class="text-right py-2 px-2.5 w-16 text-[10px] font-bold text-cyan-400 uppercase tracking-wider">DPH</th>
                        <th class="text-right py-2 px-2.5 w-28 text-[10px] font-bold text-cyan-400 uppercase tracking-wider">Celkom</th>
                    @else
                        <th class="text-right py-2 px-2.5 w-28 text-[10px] font-bold text-cyan-400 uppercase tracking-wider">Cena/ks</th>
                        <th class="text-right py-2 px-2.5 w-28 text-[10px] font-bold text-cyan-400 uppercase tracking-wider">Celkom</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $index => $item)
                    <tr class="border-t" style="border-color: rgba(255, 255, 255, 0.1);">
                        <td class="py-2 px-2.5 text-sm text-white font-medium">{{ $item->description }}</td>
                        <td class="text-right py-2 px-2.5 text-sm text-slate-300">{{ number_format($item->quantity, 0, ',', ' ') }}</td>
                        @if($isVatPayer)
                            <td class="text-right py-2 px-2.5 text-sm text-slate-300">{{ number_format($item->unit_price_without_tax, 2, ',', ' ') }} {{ $invoice->currency }}</td>
                            <td class="text-right py-2 px-2.5 text-sm text-slate-300">{{ number_format($item->tax_rate ?? 0, 0) }}%</td>
                            <td class="text-right py-2 px-2.5 text-sm font-bold text-white">
                                {{ number_format($item->total_price, 2, ',', ' ') }} {{ $invoice->currency }}
                            </td>
                        @else
                            <td class="text-right py-2 px-2.5 text-sm text-slate-300">{{ number_format($item->unit_price_without_tax, 2, ',', ' ') }} {{ $invoice->currency }}</td>
                            <td class="text-right py-2 px-2.5 text-sm font-bold text-white">
                                {{ number_format($item->total_price, 2, ',', ' ') }} {{ $invoice->currency }}
                            </td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- VAT Summary (dark theme) -->
    @if($isVatPayer)
        <div class="mb-3 rounded-xl p-3 border" style="background: rgba(255, 255, 255, 0.05); border-color: rgba(255, 255, 255, 0.1);">
            @include('invoices.partials.vat-summary', ['darkTheme' => true, 'vatSummary' => $vatSummary ?? []])
        </div>
    @endif

    <!-- Totals Section -->
    <div class="flex justify-end mb-3">
        <div class="w-80 rounded-xl p-3 border border-cyan-500/30" style="background: linear-gradient(to bottom right, rgba(6, 182, 212, 0.1), rgba(59, 130, 246, 0.1)); backdrop-filter: blur(10px);">
            <div class="space-y-2">
                @if($isVatPayer)
                    <div class="flex justify-between py-1 border-b" style="border-color: rgba(255, 255, 255, 0.1);">
                        <span class="text-sm text-slate-400">Základ dane:</span>
                        <span class="text-sm font-semibold text-white">{{ number_format($invoice->subtotal ?? 0, 2, ',', ' ') }} {{ $invoice->currency }}</span>
                    </div>
                    <div class="flex justify-between py-1 border-b" style="border-color: rgba(255, 255, 255, 0.1);">
                        <span class="text-sm text-slate-400">DPH:</span>
                        <span class="text-sm font-semibold text-white">{{ number_format($invoice->tax_amount ?? 0, 2, ',', ' ') }} {{ $invoice->currency }}</span>
                    </div>
                @endif
                <div class="flex justify-between py-2 pt-2">
                    <span class="font-bold text-cyan-400 text-xs uppercase tracking-wider">Celkom k úhrade:</span>
                    <span class="font-bold text-xl text-cyan-400">
                        {{ number_format($invoice->total_amount, 2, ',', ' ') }} {{ $invoice->currency }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer - pushed to bottom -->
    <div class="mt-auto pt-3 border-t text-center" style="border-color: rgba(255, 255, 255, 0.1);">
        <p class="text-[10px] text-slate-400 mb-2">
            Faktúru je potrebné uhradiť do dátumu splatnosti. V prípade otázok nás kontaktujte na email@invoicehub.sk
        </p>
        <p class="text-[10px] text-cyan-400 font-semibold">Ďakujeme za vašu dôveru!</p>
    </div>
</div>
