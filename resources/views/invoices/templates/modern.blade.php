<div class="bg-gradient-to-br from-slate-50 to-blue-50 text-slate-900 p-4 min-h-[297mm] flex flex-col" id="invoice-content">
    <!-- Modern Header with Gradient - Compact -->
    <div class="mb-3 relative overflow-hidden">
        <div class="relative bg-white p-3 rounded-xl border border-blue-200 shadow-lg">
            <div class="flex justify-between items-start mb-2.5">
                <div>
                    <h1 class="text-xl font-black text-blue-600 mb-1">
                        InvoiceHub
                    </h1>
                    <p class="text-xs text-slate-600">Profesionálne riešenie pre faktúry</p>
                </div>
                <div class="text-right">
                    <div class="inline-block bg-gradient-to-r from-blue-600 to-purple-600 text-white px-2.5 py-1.5 rounded-lg shadow-lg">
                        <p class="text-xs uppercase tracking-wider font-semibold opacity-90">Faktúra</p>
                        <p class="text-base font-bold">{{ $invoice->invoice_number }}</p>
                    </div>
                </div>
            </div>

            <!-- Client and Supplier Info in Modern Cards - Compact -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2.5 mt-2.5">
                <!-- Supplier -->
                <div class="relative">
                    <div class="absolute -inset-0.5 bg-gradient-to-r from-blue-500 to-purple-500 rounded-lg opacity-20"></div>
                    <div class="relative bg-white p-2.5 rounded-lg">
                        <div class="flex items-center gap-1.5 mb-1.5">
                            <div class="w-5 h-5 bg-gradient-to-r from-blue-500 to-purple-500 rounded flex items-center justify-center">
                                <span class="text-white text-[10px] font-bold">OD</span>
                            </div>
                            <h3 class="font-bold text-slate-700 uppercase text-[10px] tracking-wider">Dodávateľ</h3>
                        </div>
                        <p class="font-bold text-sm mb-0.5 text-slate-900">{{ $invoice->supplierCompany->name ?? 'N/A' }}</p>
                        <p class="text-xs text-slate-600">{{ $invoice->supplierCompany->street ?? '' }}</p>
                        <p class="text-xs text-slate-600 mb-1.5">{{ $invoice->supplierCompany->postal_code ?? '' }} {{ $invoice->supplierCompany->city ?? '' }}</p>
                        <div class="border-t border-slate-200 pt-1.5 mt-1.5 space-y-0.5">
                            <p class="text-[10px] text-slate-500"><span class="font-semibold">IČO:</span> {{ $invoice->supplierCompany->ico ?? 'N/A' }}</p>
                            <p class="text-[10px] text-slate-500"><span class="font-semibold">DIČ:</span> {{ $invoice->supplierCompany->dic ?? 'N/A' }}</p>
                            @if($invoice->supplier_registry_office ?? false)
                                <p class="text-[10px] text-slate-500">{{ $invoice->supplier_registry_office }}</p>
                            @endif
                            @if($invoice->supplier_registry_number ?? false)
                                <p class="text-[10px] text-slate-500">{{ $invoice->supplier_registry_number }}</p>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Client -->
                <div class="relative">
                    <div class="absolute -inset-0.5 bg-gradient-to-r from-slate-300 to-slate-400 rounded-lg opacity-20"></div>
                    <div class="relative bg-white p-2.5 rounded-lg">
                        <div class="flex items-center gap-1.5 mb-1.5">
                            <div class="w-5 h-5 bg-slate-600 rounded flex items-center justify-center">
                                <span class="text-white text-[10px] font-bold">PRE</span>
                            </div>
                            <h3 class="font-bold text-slate-700 uppercase text-[10px] tracking-wider">Odberateľ</h3>
                        </div>
                        <p class="font-bold text-sm mb-0.5 text-slate-900">{{ $invoice->company_name ?? 'N/A' }}</p>
                        <p class="text-xs text-slate-600">{{ $invoice->company_address ?? '' }}</p>
                        <p class="text-xs text-slate-600 mb-1.5">{{ $invoice->company_zip ?? '' }} {{ $invoice->company_city ?? '' }}</p>
                        <div class="border-t border-slate-200 pt-1.5 mt-1.5 space-y-0.5">
                            <p class="text-[10px] text-slate-500"><span class="font-semibold">IČO:</span> {{ $invoice->company_ico ?? 'N/A' }}</p>
                            <p class="text-[10px] text-slate-500"><span class="font-semibold">DIČ:</span> {{ $invoice->company_dic ?? 'N/A' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Section with Modern Design - Compact -->
    <div class="bg-white rounded-xl p-3 mb-3 shadow-lg border border-slate-200">
        <div class="flex items-start justify-between gap-3">
            <div class="flex-1">
                <h3 class="text-sm font-bold mb-2 text-slate-900">
                    Platobné údaje
                </h3>

                <!-- Dates in Pills -->
                <div class="flex gap-2 mb-2.5">
                    <div class="flex-1 bg-gradient-to-br from-blue-50 to-blue-100 p-2 rounded-lg border border-blue-200">
                        <p class="text-xs text-blue-600 font-semibold mb-0.5">Vystavené</p>
                        <p class="text-xs font-bold text-slate-900">{{ \Carbon\Carbon::parse($invoice->issue_date)->format('d.m.Y') }}</p>
                    </div>
                    <div class="flex-1 bg-gradient-to-br from-purple-50 to-purple-100 p-2 rounded-lg border border-purple-200">
                        <p class="text-xs text-purple-600 font-semibold mb-0.5">Splatnosť</p>
                        <p class="text-xs font-bold text-slate-900">{{ \Carbon\Carbon::parse($invoice->due_date)->format('d.m.Y') }}</p>
                    </div>
                </div>

                <!-- Bank Details -->
                <div class="bg-gradient-to-br from-slate-50 to-slate-100 p-2.5 rounded-lg border border-slate-200 space-y-1 text-xs">
                    <div class="flex justify-between items-center pb-1 border-b border-slate-300">
                        <span class="text-slate-600 font-semibold">Číslo účtu</span>
                        <span class="font-mono font-bold text-slate-900">{{ $invoice->supplierCompany->iban }}</span>
                    </div>
                    <div class="flex justify-between items-center pb-1 border-b border-slate-300">
                        <span class="text-slate-600 font-semibold">Variabilný symbol</span>
                        <span class="font-mono font-bold text-slate-900">{{ $invoice->variable_symbol }}</span>
                    </div>
                    @if($invoice->constant_symbol)
                        <div class="flex justify-between items-center pb-1 border-b border-slate-300">
                            <span class="text-slate-600 font-semibold">Konštantný symbol</span>
                            <span class="font-mono font-bold text-slate-900">{{ $invoice->constant_symbol }}</span>
                        </div>
                    @endif
                    @if($invoice->specific_symbol)
                        <div class="flex justify-between items-center pb-1 border-b border-slate-300">
                            <span class="text-slate-600 font-semibold">Špecifický symbol</span>
                            <span class="font-mono font-bold text-slate-900">{{ $invoice->specific_symbol }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between items-center pt-1">
                        <span class="font-bold text-slate-700">Suma k úhrade</span>
                        <span class="font-black text-base text-blue-600">
                            {{ number_format($invoice->total_amount, 2, ',', ' ') }} {{ $invoice->currency }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- QR Code with Modern Styling - Smaller -->
            @if($qrCode)
                <div class="flex flex-col items-center gap-1">
                    <div class="relative">
                        <div class="relative bg-white p-2 rounded-lg shadow-lg border border-slate-200">
                            <img src="{{ $qrCode }}" alt="Pay by Square QR Code" class="w-[80px] h-[80px]">
                        </div>
                    </div>
                    <p class="text-[10px] font-bold text-blue-600">
                        Pay by Square
                    </p>
                </div>
            @endif
        </div>
    </div>

    <!-- Modern Items Table -->
    <div class="bg-white rounded-xl p-3 mb-3 shadow-lg border border-slate-200">
        <h3 class="text-base font-bold mb-2.5 text-slate-900">Položky</h3>
        <div class="overflow-hidden rounded-lg border border-slate-200">
            <table class="w-full">
                <thead>
                    <tr class="bg-gradient-to-r from-blue-600 to-purple-600 text-white">
                        <th class="text-left py-2 px-2.5 font-semibold text-xs uppercase tracking-wide">Popis</th>
                        <th class="text-right py-2 px-2.5 w-20 font-semibold text-xs uppercase tracking-wide">Počet</th>
                        <th class="text-right py-2 px-2.5 w-28 font-semibold text-xs uppercase tracking-wide">Cena/ks</th>
                        <th class="text-right py-2 px-2.5 w-28 font-semibold text-xs uppercase tracking-wide">Celkom</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invoice->items as $index => $item)
                        <tr class="border-b border-slate-200 {{ $index % 2 === 0 ? 'bg-slate-50' : 'bg-white' }}">
                            <td class="py-2 px-2.5 text-slate-900 text-sm">{{ $item->description }}</td>
                            <td class="text-right py-2 px-2.5 text-slate-700 text-sm">{{ $item->quantity }}</td>
                            <td class="text-right py-2 px-2.5 text-slate-700 text-sm">{{ number_format($item->unit_price, 2, ',', ' ') }} {{ $invoice->currency }}</td>
                            <td class="text-right py-2 px-2.5 font-bold text-slate-900 text-sm">
                                {{ number_format($item->total_price, 2, ',', ' ') }} {{ $invoice->currency }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Totals with Modern Styling -->
    <div class="flex justify-end mb-3">
        <div class="w-80">
            <div class="bg-white rounded-xl p-3 shadow-lg border border-slate-200">
                <div class="flex justify-between py-2 mt-1.5 bg-gradient-to-r from-blue-600 to-purple-600 px-3 rounded-lg">
                    <span class="font-bold text-sm text-white">Celkom k úhrade:</span>
                    <span class="font-black text-lg text-white">{{ number_format($invoice->total_amount, 2, ',', ' ') }} {{ $invoice->currency }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer - pushed to bottom -->
    <div class="mt-auto">
        <div class="bg-white rounded-xl p-3 border border-slate-200">
            <p class="text-xs text-slate-600 text-center">
                Faktúru je potrebné uhradiť do dátumu splatnosti. V prípade otázok nás kontaktujte na email@invoicehub.sk
            </p>
        </div>

        <div class="mt-2 text-center text-xs text-slate-500">
            <p class="font-medium">Ďakujeme za vašu dôveru! ✨</p>
        </div>
    </div>
</div>