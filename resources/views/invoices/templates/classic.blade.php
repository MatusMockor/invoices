<div class="bg-white text-black p-8 min-h-screen">
    <!-- Header with Company Logo -->
    <div class="mb-8 pb-6 border-b-2 border-purple-600">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-purple-600">InvoiceHub</h1>
            <h2 class="text-xl font-bold text-purple-600">FAKTÚRA {{ $invoice->invoice_number }}</h2>
        </div>

        <!-- Top row: Supplier + Client side-by-side -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Supplier Info -->
            <div class="flex-1 bg-purple-50/50 p-4 rounded-lg border border-purple-200">
                <h3 class="font-bold text-purple-600 mb-2 text-xs uppercase tracking-wide">Dodávateľ</h3>
                <p class="font-semibold text-base mb-1">{{ $invoice->supplierCompany->name ?? 'N/A' }}</p>
                <p class="text-xs text-gray-600">{{ $invoice->supplierCompany->street ?? '' }}</p>
                <p class="text-xs text-gray-600">{{ $invoice->supplierCompany->postal_code ?? '' }} {{ $invoice->supplierCompany->city ?? '' }}</p>
                <div class="mt-2 pt-2 border-t border-purple-200">
                    <p class="text-xs text-gray-600">IČO: {{ $invoice->supplierCompany->ico ?? 'N/A' }}</p>
                    <p class="text-xs text-gray-600">DIČ: {{ $invoice->supplierCompany->dic ?? 'N/A' }}</p>
                    @if($invoice->supplierCompany->ic_dph ?? false)
                        <p class="text-xs text-gray-600">IČ DPH: {{ $invoice->supplierCompany->ic_dph }}</p>
                    @endif
                </div>
            </div>

            <!-- Client Info -->
            <div class="flex-1 bg-gray-50 p-4 rounded-lg border border-gray-200">
                <h3 class="font-bold text-gray-700 mb-2 text-xs uppercase tracking-wide">Odberateľ</h3>
                <p class="font-semibold text-base mb-1">{{ $invoice->company->name ?? 'N/A' }}</p>
                <p class="text-xs text-gray-600">{{ $invoice->company->street ?? '' }}</p>
                <p class="text-xs text-gray-600">{{ $invoice->company->postal_code ?? '' }} {{ $invoice->company->city ?? '' }}</p>
                <div class="mt-2 pt-2 border-t border-gray-200">
                    <p class="text-xs text-gray-600">IČO: {{ $invoice->company->ico ?? 'N/A' }}</p>
                    <p class="text-xs text-gray-600">DIČ: {{ $invoice->company->dic ?? 'N/A' }}</p>
                    @if($invoice->company->ic_dph ?? false)
                        <p class="text-xs text-gray-600">IČ DPH: {{ $invoice->company->ic_dph }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Info with QR Code -->
    <div class="border-2 border-purple-200 rounded-lg p-4 mb-4 bg-gradient-to-br from-purple-50/50 to-white">
        <h3 class="font-bold mb-2 text-sm text-purple-600 border-b border-purple-200 pb-1">Platobné údaje</h3>
        <div class="flex gap-4 items-start">
            <div class="flex-1 space-y-2">
                <!-- Invoice Details -->
                <div class="grid grid-cols-2 gap-x-3 gap-y-2">
                    <div class="bg-white/60 p-2.5 rounded-lg border border-purple-100">
                        <p class="text-xs text-gray-500 mb-0.5 uppercase tracking-wide">Dátum vystavenia</p>
                        <p class="font-semibold text-sm text-gray-900">{{ \Carbon\Carbon::parse($invoice->issue_date)->format('d.m.Y') }}</p>
                    </div>
                    <div class="bg-white/60 p-2.5 rounded-lg border border-purple-100">
                        <p class="text-xs text-gray-500 mb-0.5 uppercase tracking-wide">Dátum splatnosti</p>
                        <p class="font-semibold text-sm text-purple-600">{{ \Carbon\Carbon::parse($invoice->due_date)->format('d.m.Y') }}</p>
                    </div>
                </div>

                <!-- Bank Details -->
                <div class="bg-white/80 p-3 rounded-lg border border-purple-200 space-y-2">
                    @if($invoice->supplierCompany->iban ?? false)
                        <div class="flex justify-between items-center border-b border-gray-100 pb-1.5">
                            <span class="text-xs text-gray-500 uppercase tracking-wide">Číslo účtu</span>
                            <span class="font-mono font-semibold text-sm text-gray-900">{{ $invoice->supplierCompany->iban }}</span>
                        </div>
                    @endif
                    @if($invoice->variable_symbol ?? false)
                        <div class="flex justify-between items-center border-b border-gray-100 pb-1.5">
                            <span class="text-xs text-gray-500 uppercase tracking-wide">Variabilný symbol</span>
                            <span class="font-mono font-semibold text-sm text-gray-900">{{ $invoice->variable_symbol }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between items-center pt-0.5">
                        <span class="text-sm font-semibold text-gray-700">Suma k úhrade</span>
                        <span class="font-bold text-lg text-purple-600">{{ number_format($invoice->total_amount, 2, ',', ' ') }} {{ $invoice->currency }}</span>
                    </div>
                </div>
            </div>

            <!-- QR Code -->
            @if($qrCode ?? false)
                <div class="flex flex-col items-center gap-2 bg-white p-4 border-2 border-purple-300 rounded-xl shadow-md">
                    <img src="{{ $qrCode }}" alt="Pay by Square QR Code" class="w-[120px] h-[120px]">
                    <div class="text-center">
                        <p class="text-xs font-bold text-purple-600">Pay by Square</p>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Items Table -->
    <div class="mb-8">
        <table class="w-full">
            <thead>
                <tr class="border-b-2 border-gray-300">
                    <th class="text-left py-3 px-2">Popis</th>
                    <th class="text-right py-3 px-2 w-20">Počet</th>
                    <th class="text-right py-3 px-2 w-28">Cena/ks</th>
                    <th class="text-right py-3 px-2 w-28">Celkom</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $item)
                    <tr class="border-b border-gray-200">
                        <td class="py-3 px-2">{{ $item->description }}</td>
                        <td class="text-right py-3 px-2">{{ number_format($item->quantity, 0, ',', ' ') }}</td>
                        <td class="text-right py-3 px-2">{{ number_format($item->unit_price, 2, ',', ' ') }} {{ $invoice->currency }}</td>
                        <td class="text-right py-3 px-2 font-semibold">
                            {{ number_format($item->total_price, 2, ',', ' ') }} {{ $invoice->currency }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Totals -->
    <div class="flex justify-end mb-8">
        <div class="w-80">
            <div class="flex justify-between py-3 bg-purple-50 px-4 rounded-lg">
                <span class="font-bold text-lg">Celkom k úhrade:</span>
                <span class="font-bold text-lg text-purple-600">{{ number_format($invoice->total_amount, 2, ',', ' ') }} {{ $invoice->currency }}</span>
            </div>
        </div>
    </div>

    <!-- Footer Note -->
    <div class="border-t border-gray-200 pt-4">
        <p class="text-sm text-gray-600">
            Faktúru je potrebné uhradiť do dátumu splatnosti.
            @if($invoice->notes ?? false)
                <span>Poznámka: {{ $invoice->notes }}</span>
            @endif
        </p>
    </div>

    <!-- Footer -->
    <div class="mt-8 text-center text-xs text-gray-500 border-t border-gray-200 pt-4">
        <p>Ďakujeme za vašu dôveru!</p>
    </div>
</div>
