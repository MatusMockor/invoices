<div class="bg-gradient-to-br from-indigo-50 to-white text-black p-8 min-h-screen">
    <!-- Header with Gradient -->
    <div class="mb-8 pb-6 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-2xl p-6 shadow-xl">
        <div class="flex justify-between items-center">
            <h1 class="text-3xl font-bold">InvoiceHub</h1>
            <h2 class="text-2xl font-bold">FAKTÚRA {{ $invoice->invoice_number }}</h2>
        </div>
    </div>

    <!-- Company Info Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <!-- Supplier Info -->
        <div class="bg-white p-6 rounded-2xl shadow-lg border border-indigo-100">
            <h3 class="font-bold text-indigo-600 mb-4 text-sm uppercase tracking-wider">Dodávateľ</h3>
            <p class="font-bold text-xl mb-2">{{ $invoice->supplierCompany->name ?? 'N/A' }}</p>
            <p class="text-sm text-gray-600">{{ $invoice->supplierCompany->street ?? '' }}</p>
            <p class="text-sm text-gray-600">{{ $invoice->supplierCompany->postal_code ?? '' }} {{ $invoice->supplierCompany->city ?? '' }}</p>
            <div class="mt-4 pt-4 border-t border-indigo-100 space-y-1">
                <p class="text-sm text-gray-600">IČO: {{ $invoice->supplierCompany->ico ?? 'N/A' }}</p>
                <p class="text-sm text-gray-600">DIČ: {{ $invoice->supplierCompany->dic ?? 'N/A' }}</p>
                @if($invoice->supplierCompany->ic_dph ?? false)
                    <p class="text-sm text-gray-600">IČ DPH: {{ $invoice->supplierCompany->ic_dph }}</p>
                @endif
            </div>
        </div>

        <!-- Client Info -->
        <div class="bg-white p-6 rounded-2xl shadow-lg border border-gray-200">
            <h3 class="font-bold text-gray-700 mb-4 text-sm uppercase tracking-wider">Odberateľ</h3>
            <p class="font-bold text-xl mb-2">{{ $invoice->customer_name ?? 'N/A' }}</p>
            <p class="text-sm text-gray-600">{{ $invoice->customer_street ?? '' }}</p>
            <p class="text-sm text-gray-600">{{ $invoice->customer_postal_code ?? '' }} {{ $invoice->customer_city ?? '' }}</p>
            <div class="mt-4 pt-4 border-t border-gray-200 space-y-1">
                <p class="text-sm text-gray-600">IČO: {{ $invoice->customer_ico ?? 'N/A' }}</p>
                <p class="text-sm text-gray-600">DIČ: {{ $invoice->customer_dic ?? 'N/A' }}</p>
                @if($invoice->customer_ic_dph ?? false)
                    <p class="text-sm text-gray-600">IČ DPH: {{ $invoice->customer_ic_dph }}</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Payment Info with QR Code -->
    <div class="bg-gradient-to-br from-indigo-50 to-purple-50 rounded-2xl p-6 mb-8 shadow-lg">
        <h3 class="font-bold mb-4 text-lg text-indigo-600">Platobné údaje</h3>
        <div class="flex gap-6 items-start">
            <div class="flex-1 space-y-4">
                <!-- Invoice Dates -->
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-white p-4 rounded-xl">
                        <p class="text-xs text-gray-500 mb-1 uppercase">Dátum vystavenia</p>
                        <p class="font-bold text-lg">{{ \Carbon\Carbon::parse($invoice->issue_date)->format('d.m.Y') }}</p>
                    </div>
                    <div class="bg-white p-4 rounded-xl">
                        <p class="text-xs text-gray-500 mb-1 uppercase">Dátum splatnosti</p>
                        <p class="font-bold text-lg text-indigo-600">{{ \Carbon\Carbon::parse($invoice->due_date)->format('d.m.Y') }}</p>
                    </div>
                </div>

                <!-- Bank Details -->
                <div class="bg-white p-4 rounded-xl space-y-3">
                    @if($invoice->supplierCompany->iban ?? false)
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-500">Číslo účtu</span>
                            <span class="font-mono font-bold">{{ $invoice->supplierCompany->iban }}</span>
                        </div>
                    @endif
                    @if($invoice->variable_symbol ?? false)
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-500">Variabilný symbol</span>
                            <span class="font-mono font-bold">{{ $invoice->variable_symbol }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between items-center pt-2 border-t border-gray-200">
                        <span class="font-bold">Suma k úhrade</span>
                        <span class="font-bold text-2xl text-indigo-600">{{ number_format($invoice->total_amount, 2, ',', ' ') }} {{ $invoice->currency }}</span>
                    </div>
                </div>
            </div>

            <!-- QR Code -->
            @if($qrCode ?? false)
                <div class="bg-white p-6 rounded-2xl shadow-lg">
                    <img src="{{ $qrCode }}" alt="Pay by Square QR Code" class="w-[120px] h-[120px]">
                    <p class="text-center text-xs font-bold text-indigo-600 mt-2">Pay by Square</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Items Table -->
    <div class="bg-white rounded-2xl shadow-lg overflow-hidden mb-8">
        <table class="w-full">
            <thead class="bg-gradient-to-r from-indigo-600 to-purple-600 text-white">
                <tr>
                    <th class="text-left py-4 px-4">Popis</th>
                    <th class="text-right py-4 px-4 w-20">Počet</th>
                    <th class="text-right py-4 px-4 w-28">Cena/ks</th>
                    <th class="text-right py-4 px-4 w-28">Celkom</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $item)
                    <tr class="border-b border-gray-100">
                        <td class="py-4 px-4">{{ $item->description }}</td>
                        <td class="text-right py-4 px-4">{{ number_format($item->quantity, 0, ',', ' ') }}</td>
                        <td class="text-right py-4 px-4">{{ number_format($item->unit_price, 2, ',', ' ') }} {{ $invoice->currency }}</td>
                        <td class="text-right py-4 px-4 font-bold">
                            {{ number_format($item->total_price, 2, ',', ' ') }} {{ $invoice->currency }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Total -->
    <div class="flex justify-end mb-8">
        <div class="bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-2xl p-6 shadow-xl">
            <div class="flex justify-between items-center gap-8">
                <span class="font-bold text-xl">Celkom k úhrade:</span>
                <span class="font-bold text-3xl">{{ number_format($invoice->total_amount, 2, ',', ' ') }} {{ $invoice->currency }}</span>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="text-center text-gray-600">
        <p class="text-sm">Faktúru je potrebné uhradiť do dátumu splatnosti.</p>
        @if($invoice->notes ?? false)
            <p class="text-sm mt-2">Poznámka: {{ $invoice->notes }}</p>
        @endif
        <p class="text-xs mt-4 text-gray-500">Ďakujeme za vašu dôveru!</p>
    </div>
</div>
